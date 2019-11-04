<?php

namespace App\Waypoint\Console\Commands;

use App;
use App\Waypoint\Command;
use App\Waypoint\Exceptions\GeneralException;
use Webpatser\Uuid\Uuid;

/**
 * Class GenerateJavaScriptConfigCommand
 * @package App\Waypoint\Console\Commands
 */
class DownloadClientCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:download_client 
                        {--client_id= : client_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download full client info as JSON object';

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle()
    {
        parent::handle();

        /** @var \App\Waypoint\Collection $ClientFullObj */
        if ( ! $ClientFullObj = $this->ClientFullRepository
            ->with('userDetails.accessLists')
            ->with('accessListsFull.accessListPropertiesFull')
            ->with('accessListsFull.accessListUsersFull')
            ->with('reportTemplatesFull.reportTemplateAccountGroups')
            ->find($this->option('client_id'))
        )
        {
            throw new GeneralException();
        }

        $client_download_file_name = storage_path('exports') . '/' . Uuid::generate()->__get('string') . '.json';
        $fh = fopen($client_download_file_name, 'w') or die("can't open file");
        fwrite($fh, json_encode($ClientFullObj->toArray(), JSON_PRETTY_PRINT));
        fclose($fh);
        $this->alert("See $client_download_file_name");

        return true;
    }
}
