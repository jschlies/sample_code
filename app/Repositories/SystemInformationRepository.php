<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\Heartbeat;
use App\Waypoint\Repository as BaseRepository;
use function preg_match;

/**
 * Class UtilityRepository
 * @package App\Waypoint\Repositories
 */
class SystemInformationRepository extends BaseRepository
{
    /**
     * Configure the Model
     **/
    public function model()
    {
        return Heartbeat::class;
    }

    protected $forbidden_properties = [
        'MANAGEMENT_CLIENT_ID',
        'GOOGLE_ANALYTICS_TRACKING_ID',
        'AWS_SQS_CLIENT_ID',
        'USERNAME',
        'PASSWORD',
        'AUTH',
        'SECRET',
        'KEY',
        'TOKEN',
    ];

    /**
     * @return array
     */
    public function generate_system_information()
    {
        $return_me = [];
        $dir       = new \DirectoryIterator(config_path());
        foreach ($dir as $fileinfo)
        {
            if ( ! $fileinfo->isDot() && $fileinfo->isFile())
            {
                preg_match("/^(.*)\.php/i", $fileinfo->getFilename(), $gleaned_arr);
                if (count($gleaned_arr) > 1)
                {
                    $return_me['laravel_config'][$gleaned_arr[1]] = (array) config($gleaned_arr[1]);
                }
            }
        }
        $return_me['php']['php_loaded_extensions'] = get_loaded_extensions();
        $return_me['php']['phpinfo']               = array_merge($return_me, $this->phpinfo2array());

        if (env('APP_ENV') == 'local')
        {
            $return_me['git'] = self::git_version();
        }
        return $this->clean_array($return_me);
    }

    /**
     * @return array
     */
    private function phpinfo2array()
    {
        $entitiesToUtf8 = function ($input)
        {
            return preg_replace_callback(
                "/(&#[0-9]+;)/",
                function ($m)
                {
                    return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES");
                },
                $input
            );
        };
        $plainText      =
            function ($input) use ($entitiesToUtf8)
            {
                return trim(html_entity_decode($entitiesToUtf8(strip_tags($input))));
            };
        $titlePlainText =
            function ($input) use ($plainText)
            {
                return '# ' . $plainText($input);
            };

        ob_start();
        phpinfo(-1);

        $phpinfo = ['phpinfo' => []];

        // Strip everything after the <h1>Configuration</h1> tag (other h1's)
        if ( ! preg_match('#(.*<h1[^>]*>\s*Configuration.*)<h1#s', ob_get_clean(), $matches))
        {
            return [];
        }

        $input   = $matches[1];
        $matches = [];

        if (preg_match_all(
            '#(?:<h2.*?>(?:<a.*?>)?(.*?)(?:<\/a>)?<\/h2>)|' . '(?:<tr.*?><t[hd].*?>(.*?)\s*</t[hd]>(?:<t[hd].*?>(.*?)\s*</t[hd]>(?:<t[hd].*?>(.*?)\s*</t[hd]>)?)?</tr>)#s', $input,
            $matches,
            PREG_SET_ORDER
        ))
        {
            foreach ($matches as $match)
            {
                $fn = strpos($match[0], '<th') === false ? $plainText : $titlePlainText;
                if (strlen($match[1]))
                {
                    $phpinfo[$match[1]] = [];
                }
                elseif (isset($match[3]))
                {
                    $keys1                                = array_keys($phpinfo);
                    $phpinfo[end($keys1)][$fn($match[2])] = isset($match[4]) ? [$fn($match[3]), $fn($match[4])] : $fn($match[3]);
                }
                else
                {
                    $keys1                  = array_keys($phpinfo);
                    $phpinfo[end($keys1)][] = $fn($match[2]);
                }
            }
        }

        return $phpinfo;
    }

    /**
     * @return array
     */
    private static function git_version()
    {
        try
        {
            if ( ! self::has_git())
            {
                return [];
            }
            exec('git describe --always', $version_mini_hash);
            exec('git rev-list HEAD | wc -l', $version_number);
            exec('git log -1', $line);
            if (isset($version_number[0]) && isset($version_mini_hash[0]))
            {
                $version['short'] = "v1." . trim($version_number[0]) . "." . $version_mini_hash[0];
                $version['full']  = "v1." . trim($version_number[0]) . ".$version_mini_hash[0] (" . str_replace('commit ', '', $line[0]) . ")";
            }
            else
            {
                $version['short'] = "version_number and/or version_mini_hash unavailable";
                $version['full']  = "version_number and/or version_mini_hash unavailable";
            }
            return $version;
        }
        catch (\Exception $e)
        {
            return [];
        }
    }

    /**
     * @return bool|string
     *
     * See http://zurb.com/forrst/posts/Check_if_Git_is_installed_from_PHP-0E2
     */
    public static function has_git()
    {
        exec('which git 2>&1', $output);
        if (preg_match("/no git/", $output[0]))
        {
            return false;
        }
        $git = file_exists($line = trim(current($output))) ? $line : 'git';

        unset($output);

        exec($git . ' --version', $output);

        preg_match('#^(git version)#', current($output), $matches);

        return ! empty($matches[0]) ? $git : false;
    }

    /**
     * @param $array_to_clean
     * @return mixed
     */
    public function clean_array($array_to_clean)
    {
        $pattern_arr = implode('|', $this->forbidden_properties);
        foreach ($array_to_clean as $i => $value)
        {
            /** Case insensitive */
            if (preg_match('/' . $pattern_arr . '/i', $i))
            {
                unset($array_to_clean[$i]);
                continue;
            }
            if (is_array($value))
            {
                $array_to_clean[$i] = $this->clean_array($array_to_clean[$i]);
            }

        }
        return $array_to_clean;
    }
}
