<?php

namespace App\Waypoint\Console\Commands;

use App;
use App\Waypoint\Command;
use App\Waypoint\Exceptions\GeneralException;
use Carbon\Carbon;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Report\Html\Facade as SebastianBergmann_CodeCoverage_Report_Html_Facade;

/**
 * Class ListClientsCommand
 * @package App\Console\Commands
 *
 * See https://laravel.com/docs/5.1/artisan
 */
class MergeCoverageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:merge:coverage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Merge coverage reports';

    /**
     * ListClientsCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();
        /**
         * NOTE you cannot populate $this->ClientRepositoryObj in parent::__construct. Messes up code generator
         */
    }

    /**
     * Execute the console command.
     *
     * @throws \Exception
     */
    public function handle()
    {
        /**
         * delete all contents of public/coverage/
         */

        echo 'Checking for files (and deleting) in ' . config('waypoint.unittest_coverage_destination', base_path() . '/public/coverage/') . PHP_EOL;
        if ( ! file_exists(config('waypoint.unittest_coverage_destination', base_path() . '/public/coverage/')))
        {
            mkdir(config('waypoint.unittest_coverage_destination', base_path() . '/public/coverage/'));
        }
        foreach (glob(config('waypoint.unittest_coverage_destination', base_path() . '/public/coverage/') . '{,.}[!.,!..]*', GLOB_MARK | GLOB_BRACE) as $file)
        {
            /**
             * iterate files
             */
            if (is_file($file))
            {
                unlink($file);
            }
            elseif (is_dir($file))
            {
                $this->delete_directory_recursive($file);
            }
        }

        /**
         * dmerge coverage files in public/coverage/
         */
        $CodeCoverageObj = new CodeCoverage;

        $CodeCoverageObj->start('Waypoing Building Coverage Report: ' . Carbon::now()->format('Y-m-d H:i:s'));

        $dir = realpath(dirname(__FILE__)) . '/../../../storage/coverage/*';
        foreach (glob($dir) as $file)
        {
            if ( ! preg_match("/\.txt$/", $file))
            {
                echo 'Skipping ' . basename($file) . PHP_EOL;
                continue;
            }
            if ( ! is_dir($file))
            {
                echo 'Processing ' . basename($file) . PHP_EOL;
                include($file);

                /** @var CodeCoverage $coverage */
                if ( ! $coverage)
                {
                    throw new GeneralException('WTF ' . __FILE__ . ':' . __LINE__);
                }

                $this->apply_ledger_filters($coverage);
                $CodeCoverageObj->filter()->addFilesToWhitelist($coverage->filter()->getWhitelist());
                $CodeCoverageObj->merge($coverage);
            }
        }

        $CodeCoverageObj->stop();
        $this->apply_ledger_filters($CodeCoverageObj);

        echo 'Generating report to ' . config('waypoint.unittest_coverage_destination', base_path() . '/public/coverage') . PHP_EOL;

        if ( ! config('waypoint.unittest_coverage_destination', base_path() . '/public/coverage'))
        {
            mkdir(config('waypoint.unittest_coverage_destination', base_path() . '/public/coverage'));
        }
        /**
         * generate consolidated report
         */
        $WriterObj = new SebastianBergmann_CodeCoverage_Report_Html_Facade(30, 70);
        $WriterObj->process($CodeCoverageObj, config('waypoint.unittest_coverage_destination', base_path() . '/public/coverage'));
    }

    /**
     * @param $dirname
     * @return bool
     */
    function delete_directory_recursive($dirname)
    {
        if (is_dir($dirname))
        {
            $dir_handle = opendir($dirname);
        }
        if ( ! $dir_handle)
        {
            return false;
        }
        while ($file = readdir($dir_handle))
        {
            if ($file != "." && $file != "..")
            {
                if ( ! is_dir($dirname . "/" . $file))
                {
                    unlink($dirname . "/" . $file);
                }
                else
                {
                    $this->delete_directory_recursive($dirname . '/' . $file);
                }
            }
        }
        closedir($dir_handle);
        rmdir($dirname);
        return true;
    }

    /**
     * @param $CodeCoverageObj
     */
    function apply_ledger_filters(&$CodeCoverageObj)
    {
        $CodeCoverageObj->filter()->addDirectoryToWhitelist('/vagrant/app/');
        $CodeCoverageObj->filter()->removeDirectoryFromWhitelist('/vagrant/app/Http/Controllers/Api/Ledger/');
        $CodeCoverageObj->filter()->removeDirectoryFromWhitelist('/vagrant/app/Repositories/Ledger/');
        $CodeCoverageObj->filter()->removeDirectoryFromWhitelist('/vagrant/app/Models/Ledger/');
        $CodeCoverageObj->filter()->removeDirectoryFromWhitelist('/vagrant/app/Models/Ledger/');
    }
}