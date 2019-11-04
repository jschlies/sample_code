<?php

namespace App\Waypoint\Console\Commands;

use App;
use App\Waypoint\Command;
use App\Waypoint\Repositories\SystemInformationRepository;
use Webpatser\Uuid\Uuid;

/**
 * Class ListAccessListsCommand
 * @package App\Console\Commands
 *
 * See https://laravel.com/docs/5.1/artisan
 */
class GenerateSystemInfoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:list:system_information';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List system configuration information';

    /**
     * GenerateSystemInfoCommand constructor.
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
        parent::handle();

        /** @var SystemInformationRepository $SystemInformationRepositoryObj */
        $SystemInformationRepositoryObj = App::make(SystemInformationRepository::class);
        $return_me                      = $SystemInformationRepositoryObj->generate_system_information();

        $client_download_file_name = storage_path('exports') . '/' . Uuid::generate()->__get('string') . '.json';
        $fh = fopen($client_download_file_name, 'w') or die("can't open file");
        fwrite($fh, json_encode($return_me, JSON_PRETTY_PRINT));
        fclose($fh);
        $this->alert("See $client_download_file_name" . PHP_EOL);
        return true;
    }
}