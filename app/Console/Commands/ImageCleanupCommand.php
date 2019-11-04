<?php

namespace App\Waypoint\Console\Commands;

use App;
use App\Waypoint\Models\Client;
use function array_unique;
use AWS;
use App\Waypoint\Command;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\User;
use DB;
use function in_array;
use function is_array;
use function preg_match;
use PhpParser\Builder\Property;

/**
 * Class ListClientsCommand
 * @package App\Console\Commands
 *
 * See https://laravel.com/docs/5.1/artisan
 * @codeCoverageIgnore
 */
class ImageCleanupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:queue:image_cleanup
                        {--dry_run=1 : Values are 0 and 1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'remove all unused images from s3';

    /**
     * CreateAllSQSQueuesCommand constructor.
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

        if ( ! config('queue.driver', false) == 'sqs')
        {
            return;
        }

        $this->image_cleanup($this->option('dry_run'));

        return true;
    }

    /**
     * @throws GeneralException
     * @throws \InvalidArgumentException
     */
    public function image_cleanup($dry_run = true)
    {
        $list_of_used_images = [];
        /** @var Client $ClientObj */
        foreach (App::make(App\Waypoint\Repositories\ClientRepository::class)->all() as $ClientObj)
        {
            foreach ($ClientObj->getImageJSON(true) as $image_arr)
            {
                if ( ! is_array($image_arr))
                {
                    continue;
                }
                foreach ($image_arr as $image)
                {
                    $list_of_used_images[] = $image;
                }
            }
            /** @var Property $PropertyObj */
            foreach ($ClientObj->properties as $PropertyObj)
            {
                foreach ($PropertyObj->getImageJSON(true) as $image_arr)
                {
                    if ( ! is_array($image_arr))
                    {
                        continue;
                    }
                    foreach ($image_arr as $image)
                    {
                        $list_of_used_images[] = $image;
                    }
                }

            }
            foreach ($ClientObj->users as $UserObj)
            {
                /** @var User $image */
                foreach ($UserObj->getImageJSON(true) as $image_arr)
                {
                    if ( ! is_array($image_arr))
                    {
                        continue;
                    }
                    foreach ($image_arr as $image)
                    {
                        $list_of_used_images[] = $image;
                    }
                }
            }
        }

        $list_of_used_images      = array_unique(
            array_map(
                function ($n)
                {
                    if (preg_match("/\/([A-z0-9\-]*\....)$/", $n['url'], $gleaned))
                    {
                        return $gleaned[1];
                    }
                    return null;
                },
                $list_of_used_images
            )
        );
        $list_of_used_attachments = $results =
            DB::select(
                DB::raw(
                    "
                                    SELECT
                                        filepath
                                    FROM
                                        attachments
                                "
                )
            );
        $list_of_used_attachments = array_unique(
            array_map(
                function ($n)
                {
                    return $n->filepath;
                },
                $list_of_used_attachments
            )
        );

        $i   = 0;
        $key = null;
        do
        {
            /** @var \Aws\S3\S3Client $S3ImageClientObj */
            $S3ImageClientObj    = AWS::createClient(config('waypoint.image_data_store_disc', 's3_images'));
            $ListImageObjectsArr = $S3ImageClientObj->listObjects(
                [
                    'Bucket'     => config('waypoint.aws_hermes_images_bucket', null),
                    'StartAfter' => $key,
                ]
            );
            foreach ($ListImageObjectsArr->toArray()['Contents'] as $aws_image)
            {
                if (
                    ! in_array($aws_image['Key'], $list_of_used_images) &&
                    ! in_array($aws_image['Key'], $list_of_used_attachments)

                )
                {
                    if ( ! $dry_run)
                    {
                        /** @var AWS\Result $DeleteImageObjectObj */
                        $DeleteImageObjectObj = $S3ImageClientObj->deleteObject(
                            [
                                'Bucket' => config('waypoint.aws_hermes_images_bucket', null),
                                'Key'    => $aws_image['Key'],
                            ]
                        );
                        if ($DeleteImageObjectObj->get('@metadata')['statusCode'] !== 204)
                        {
                            throw new GeneralException('AWS delete failure');
                        }
                    }
                    $this->alert(($dry_run ? 'Dry Run ' : '') . $i++ . ' deleted ' . $aws_image['Key']);
                }
                else
                {
                    $this->alert(($dry_run ? 'Dry Run ' : '') . $i++ . ' skipped ' . $aws_image['Key']);
                }

                $key = $aws_image['Key'];
            }
        } while (count($ListImageObjectsArr->toArray()['Contents']) > 0);

    }
}