<?php

namespace App\Waypoint\Providers;

use App\Waypoint\Model;
use DB;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        /**
         * See https://laravel-news.com/laravel-5-4-key-too-long-error
         */
        Schema::defaultStringLength(128);

        /** @noinspection PhpUnusedParameterInspection */
        \Validator::extend(
            'array_or_json_string',
            function ($attribute, $value, $parameters, $validator)
            {
                if (is_array($value) || $value instanceof Arrayable)
                {
                    return true;
                }

                if (is_object($value) && ($value instanceof \stdClass))
                {
                    return true;
                }

                json_decode($value);
                return json_last_error() === JSON_ERROR_NONE;
            }
        );

        /** @noinspection PhpUnusedParameterInspection */
        \Validator::extend(
            'related_user_types_check',
            function ($attribute, $value, $parameters, Validator $validator)
            {
                if (count($parameters) < 0)
                {
                    throw new \InvalidArgumentException("Validation rule game_fixture requires 0 parameters.");
                }

                $input = $validator->getData();

                $related_user_type_arr = DB::select(
                    DB::raw(
                        'select *
                            FROM related_user_types
                            WHERE id=' . $input['related_user_type_id']
                    )
                );
                $related_object_type   = $related_user_type_arr[0]->related_object_type;
                /** @var Model $ObjectInQuestion */
                $ObjectInQuestion  = new $related_object_type();
                $table_in_question = $ObjectInQuestion->getTable();

                /**
                 * check that object (id = $input[related_object_id] and  $input[related_user_type]->related_object_type
                 */
                $related_user_type_arr = DB::select(
                    DB::raw(
                        'select *
                            FROM ' . $table_in_question . '
                            WHERE id=' . $input[$attribute]
                    )
                );

                return count($related_user_type_arr) >= 1;
            }
        );
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment() == 'local')
        {
        }

        $this->app->bind(
            \Auth0\Login\Contract\Auth0UserRepository::class,
            \App\Waypoint\Repositories\UserRepository::class
        );
    }
}
