<?php

use App\Waypoint\Exceptions\GeneralException;
use Illuminate\Database\Migrations\Migration;
use App\Waypoint\Models\Client;

class ImageCleanupOrigional extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /**
         * clients
         */
        $client_result_arr = DB::select(
            DB::raw(
                '
                    SELECT clients.id, clients.name, clients.image_json
                    FROM clients
                    WHERE clients.name != :DUMMY_CLIENT_NAME
                '
            ),
            [
                'DUMMY_CLIENT_NAME' => Client::DUMMY_CLIENT_NAME,
            ]
        );

        foreach ($client_result_arr as $client_result)
        {
            $image_json_arr = json_decode($client_result->image_json, true);

            $new_client_image_json_obj = new stdClass();
            if ( ! is_array($image_json_arr))
            {
                $image_json_arr = [];
            }
            foreach ($image_json_arr as $key => $image_json)
            {
                if (isset($image_json['origional']))
                {
                    $image_json['original'] = $image_json['origional'];
                    unset($image_json['origional']);
                }
                $new_client_image_json_obj->$key = $image_json;
            }

            $image_json = json_encode($new_client_image_json_obj);

            DB::update(
                DB::raw(
                    "
                    UPDATE clients 
                    SET image_json = :IMAGE_JSON
                    WHERE clients.id = :CLIENT_ID
                "
                ),
                [
                    'CLIENT_ID'  => $client_result->id,
                    'IMAGE_JSON' => $image_json,
                ]
            );
        }

        /**
         * properties
         */
        $property_result_arr = DB::select(
            DB::raw(
                '
                    SELECT properties.id, properties.name, properties.image_json
                    FROM properties
                '
            )
        );

        foreach ($property_result_arr as $property_result)
        {
            $image_json_arr = json_decode($property_result->image_json, true);

            $new_property_image_json_obj = new stdClass();
            if ( ! is_array($image_json_arr))
            {
                $image_json_arr = [];
            }
            foreach ($image_json_arr as $key => $image_json)
            {
                if (isset($image_json['origional']))
                {
                    $image_json['original'] = $image_json['origional'];
                    unset($image_json['origional']);
                }
                $new_property_image_json_obj->$key = $image_json;
            }

            $image_json = json_encode($new_property_image_json_obj);

            DB::update(
                DB::raw(
                    "
                    UPDATE properties 
                    SET image_json = :IMAGE_JSON
                    WHERE properties.id = :PROPERTY_ID
                "
                ),
                [
                    'PROPERTY_ID' => $property_result->id,
                    'IMAGE_JSON'  => $image_json,
                ]
            );
        }

        /**
         * users
         */
        $user_result_arr = DB::select(
            DB::raw(
                '
                    SELECT users.id, users.image_json
                    FROM users
                '
            )
        );

        foreach ($user_result_arr as $user_result)
        {
            $image_json_arr = json_decode($user_result->image_json, true);

            $new_user_image_json_obj = new stdClass();
            if ( ! is_array($image_json_arr))
            {
                $image_json_arr = [];
            }
            foreach ($image_json_arr as $key => $image_json)
            {
                if (isset($image_json['origional']))
                {
                    $image_json['original'] = $image_json['origional'];
                    unset($image_json['origional']);
                }
                $new_user_image_json_obj->$key = $image_json;
            }

            $image_json = json_encode($new_user_image_json_obj);

            DB::update(
                DB::raw(
                    "
                    UPDATE users 
                    SET image_json = :IMAGE_JSON
                    WHERE users.id = :USER_ID
                "
                ),
                [
                    'USER_ID'    => $user_result->id,
                    'IMAGE_JSON' => $image_json,
                ]
            );
        }

    }

    /**
     * @throws \App\Waypoint\Exceptions\GeneralException
     */
    public function down()
    {
        throw new GeneralException('Migration down() not supported');
    }
}
