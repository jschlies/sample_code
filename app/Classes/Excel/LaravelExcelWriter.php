<?php

namespace App\Waypoint;

use App;
use App\Waypoint\Http\ApiGuardAuth;
use App\Waypoint\Repositories\DownloadHistoryRepository;
use Auth;
use Carbon\Carbon;
use Maatwebsite\Excel\Exceptions\LaravelExcelException;
use Maatwebsite\Excel\Writers\LaravelExcelWriter as LaravelExcelWriterBase;

/**
 *
 * LaravelExcel Excel writer
 *
 * @category   Laravel Excel
 * @version    1.0.0
 * @package    maatwebsite/excel
 * @copyright  Copyright (c) 2013 - 2014 Maatwebsite (http://www.maatwebsite.nl)
 * @author     Maatwebsite <info@maatwebsite.nl>
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
 */
class LaravelExcelWriter extends LaravelExcelWriterBase
{
    /**
     * Download a file
     * @param array $headers
     * @throws LaravelExcelException
     */
    protected function _download(Array $headers = [])
    {
        // Set the headers
        $this->_setHeaders(
            $headers,
            [
                'Content-Type'        => $this->contentType,
                'Content-Disposition' => 'attachment; filename="' . $this->filename . '.' . $this->ext . '"',
                'Expires'             => 'Mon, 26 Jul 1997 05:00:00 GMT', // Date in the past
                'Last-Modified'       => Carbon::now()->format('D, d M Y H:i:s'),
                'Cache-Control'       => 'cache, must-revalidate',
                'Pragma'              => 'public',
            ]
        );

        // Check if writer isset
        if ( ! $this->writer)
        {
            throw new LaravelExcelException('[ERROR] No writer was set.');
        }

        /**
         * the right way to do this is an event() but for now.......
         *
         * you might think we could just do:
         * $output_as_string                = $this->string('csv');
         * so we could store at csv but LaravelExcelWriter does not seem to work that way
         * Doing this higher in the stack via event would be better
         */
        $output_as_string = $this->string($this->ext);

        /** @var DownloadHistoryRepository $DownloadHistoryRepositoryObj */
        $DownloadHistoryRepositoryObj = App::make(DownloadHistoryRepository::class);

        $UserObj = ApiGuardAuth::getUser();
        $DownloadHistoryRepositoryObj->create(
            [
                'download_time' => Carbon::now()->format('Y-m-d H:i:s'),
                'download_md5'  => md5($output_as_string),
                'download_type' => $this->ext,
                'user_id'       => Auth::getUser() ? Auth::getUser()->id : ($UserObj ? $UserObj->id : 1),
                'data'          => $output_as_string,
            ]
        );

        // Download
        $this->writer->save('php://output');

        // End the script to prevent corrupted xlsx files
        exit;
    }
}
