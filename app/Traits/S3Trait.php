<?php

namespace App\Waypoint;

use App\Waypoint\Events\ReadFromS3Event;
use App\Waypoint\Events\SendToS3Event;
use Illuminate\Support\Facades\Storage;

/**
 * Class ModelSaveAndValidateTrait
 * @package App\Waypoint\Models
 *
 * NOTE NOTE NOTE
 * This trait exists because the User, permission and role models
 * extend App\Waypoint\Models\Entrust\User which extends App\Waypoint\Models\Entrust which extends blah blah.
 * the point is that if we want to add base functionality to all models, we need to use this trait
 *
 */
trait S3Trait
{
    /**
     * @param $s3_object_key
     * @param $string
     * @param null $disc
     */
    public function send_to_s3($s3_object_key, $string, $disc = null)
    {
        Storage::disk($disc)
               ->write($s3_object_key, $string);

        event(
            new SendToS3Event(
                arrayToObject(
                    [
                        'var1' => 1,
                        'var2' => 1,
                    ]
                ),
                [
                                'option1' => 1,
                                'option2' => 1,
                ]
            )
        );
    }

    /**
     * @param $s3_object_key
     * @param $json_string
     * @param null $disc
     * @return string
     */
    public function get_from_s3($s3_object_path, $disc)
    {
        if (Storage::disk($disc)->exists($s3_object_path))
        {
            event(
                new ReadFromS3Event(
                    arrayToObject(
                        [
                            'var1' => 1,
                            'var2' => 1,
                        ]
                    ),
                    [
                                'option1' => 1,
                                'option2' => 1,
                    ]
                )
            );
            return Storage::disk($disc)->get($s3_object_path);
        }
        event(
            new ReadFromS3Event(
                arrayToObject(
                    [
                        'var1' => 1,
                        'var2' => 1,
                    ]
                ),
                [
                                'option1' => 1,
                                'option2' => 1,
                ]
            )
        );
        return null;
    }
}