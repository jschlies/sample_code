<?php

namespace App\Waypoint\Console\Commands;

use App\Waypoint\Exceptions\GeneralException;
use Illuminate\Foundation\Console\RouteListCommand;
use Illuminate\Routing\Route;
use Illuminate\Routing\RouteCompiler;
use const PHP_EOL;

/**
 * Class RouteReportCommand
 * @package App\Console\Commands
 *
 * See https://laravel.com/docs/5.1/artisan
 * @codeCoverageIgnore
 */
class RouteReportCommand extends RouteListCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'waypoint:route_report';

    /**
     * The name and signature of the console command.
     *
     * @var string
     * @todo test these parameters. the intent was to inherit RouteListCommand and use it's filters/options. needs work
     */
    protected $signature = 'waypoint:route_report ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Route Report and save to storage/exports';

    /**
     * Execute the console command.
     *
     * See http://stackoverflow.com/questions/12981848/php-preg-replace-backslash
     */
    public function handle()
    {
        $this->route_report();
    }

    /**
     * @throws GeneralException
     */
    public function route_report($suppress_sysout = false)
    {
        $de_dup = [];
        $myFile = config('waypoint.route_report_target_path') . 'route_report.' . date("YmdHis") . '.csv';
        $fp     = fopen($myFile, 'w');
        fputcsv($fp, ['METHOD', 'LOWEST ROLE', 'URI', 'NAME', 'ACTION', 'REGEX', 'DUP ROUTE WARNING']);
        /** @var \Illuminate\Routing\Route $route */
        foreach ($this->routes as $route)
        {
            $regex      = (new RouteCompiler($route))->compile()->getRegex();
            $uri        = '/' . $route->uri();
            $action     = $route->getActionName();
            $route_info = $this->getRouteInformation($route);
            $method     = $route_info['method'];

            /**
             * check
             */
            if (preg_match("/^(.*)\@(.*)$/", $route_info['action'], $gleaned))
            {
                $controller_name   = $gleaned[1];
                $controller_method = $gleaned[2];

                $ControllorObj = \App::make($controller_name);
                if ( ! method_exists($ControllorObj, $controller_method))
                {
                    throw new GeneralException('unknown controller/method ' . $route_info['action'] . ' ' . $route_info['uri'], 500);
                }
            }
            elseif ($action !== "Closure")
            {
                throw new GeneralException('unknown controller/method ' . $route_info['action'] . ' ' . $route_info['uri'], 500);
            }
            if ($method == 'GET|HEAD')
            {
                $method = 'GET';
            }
            if ($method == 'PUT|PATCH')
            {
                $method = 'PUT';
            }

            fputcsv(
                $fp,
                [
                    $method,
                    self::getLowestRoleFromString($route_info['middleware']),
                    $uri,
                    $route->getName(),
                    $action,
                    $regex,
                    isset($de_dup[$method][$regex]) ? 'Dup route detected' : 'Unique route',
                ]
            );
            $de_dup[$method][$regex] = true;
        }

        if ( ! $suppress_sysout)
        {
            echo '--------------------------------------------------------------------------------------------------------' . PHP_EOL;
            echo '----- See ' . $myFile . '  ------' . PHP_EOL;
            echo '--------------------------------------------------------------------------------------------------------' . PHP_EOL;
        }
        fclose($fp);
    }

    /**
     * Get the route information for a given route.
     *
     * @param  \Illuminate\Routing\Route $route
     * @return array
     */
    protected function getRouteInformation(Route $route)
    {
        return [
            'host'       => $route->domain(),
            'method'     => implode('|', $route->methods()),
            'uri'        => $route->uri(),
            'name'       => $route->getName(),
            'action'     => $route->getActionName(),
            'middleware' => $this->getMiddleware($route),
        ];
    }

    /**
     * @param string $role_as_coma_delimited_string
     * @return string
     */
    public static function getLowestRoleFromString(string $role_as_coma_delimited_string)
    {
        if (stri_contains($role_as_coma_delimited_string, 'ClientUser'))
        {
            return 'ClientUser';
        }
        if (stri_contains($role_as_coma_delimited_string, 'ClientUser'))
        {
            return 'ClientAdmin';
        }
        if (stri_contains($role_as_coma_delimited_string, 'WaypointAssociate'))
        {
            return 'WaypointAssociate';
        }
        if (stri_contains($role_as_coma_delimited_string, 'WaypointSystemAdministrator'))
        {
            return 'WaypointSystemAdministrator';
        }
        if (stri_contains($role_as_coma_delimited_string, 'Root'))
        {
            return 'Root';
        }
        return '';
    }
}