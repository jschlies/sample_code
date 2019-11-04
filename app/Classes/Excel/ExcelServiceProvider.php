<?php

namespace App\Waypoint;

use Illuminate\Support\Facades\Response;
use \Maatwebsite\Excel\ExcelServiceProvider as ExcelServiceProviderBase;

/**
 *
 * LaravelExcel Excel ServiceProvider
 *
 * @category   Laravel Excel
 * @package    maatwebsite/excel
 * @copyright  Copyright (c) 2013 - 2014 Maatwebsite (http://www.maatwebsite.nl)
 * @author     Maatwebsite <info@maatwebsite.nl>
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
 */
class ExcelServiceProvider extends ExcelServiceProviderBase
{
    /**
     * Bind writers
     * @return void
     */
    protected function bindWriters()
    {
        // Bind the excel writer
        $this->app->singleton('excel.writer', function ($app)
        {
            return new LaravelExcelWriter(
                $app->make(Response::class),
                $app['files'],
                $app['excel.identifier']
            );
        });
    }
}
