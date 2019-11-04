<?php

namespace App\Waypoint\Console\Commands;

use App\Waypoint\Models\ApiKey;
use App\Waypoint\Models\User;
use App\Waypoint\Command;
use App;
use App\Waypoint\Exceptions\GeneralException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class GenerateApiKeyCommand
 * @package App\Console\Commands
 *
 * See https://laravel.com/docs/5.1/artisan
 */
class GenerateApiKeyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:generate_api_key 
                        {--client_id= : Client ID } 
                        {--email= : User email } ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate generic users';

    public function __construct()
    {
        parent::__construct();
        /**
         * NOTE you cannot populate $this->ClientRepositoryObj in parent::__construct. Messses with code generator
         */
    }

    /**
     * Execute the console command.
     *
     * @todo push this logic into a repository
     */
    public function handle()
    {
        parent::handle();

        $client_id = $this->option('client_id');

        if ( ! $ClientObj = $this->ClientRepositoryObj->findWithoutFail($client_id))
        {
            throw new ModelNotFoundException('No such client');
        }

        /** @var User $UserObj */
        if ( ! $UserObj = $this->UserRepositoryObj->findWhere(
            [
                'client_id' => $client_id,
                'email'     => $this->option('email'),
            ]
        )->first()
        )
        {
            throw new GeneralException('No such user');
        }
        if ($UserObj->apiKey)
        {
            $UserObj->apiKey->delete();
        }

        $ApiKeyObj = ApiKey::make($UserObj->id);

        $this->alert('Creating ApiKey ' . $ApiKeyObj->key . ' ' . $UserObj->email . ' in client ' . $ClientObj->name);

        return true;
    }
}