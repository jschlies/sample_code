<?php

namespace App\Waypoint;

use Felixkiss\UniqueWithValidator\ServiceProvider as UniqueWithValidatorServiceProviderBase;

/**
 * Class UniqueWithValidatorServiceProvider
 * @package App\Waypoint
 */
class UniqueWithValidatorServiceProvider extends UniqueWithValidatorServiceProviderBase
{
    /**
     * Register the service provider.
     *
     * @return void
     * @todo remove this method when this
     *       has been pulled https://github.com/felixkiss/uniquewith-validator/pull/67/files
     */
    //public function register()
    //{
    //    // Whenever the validator factory is accessed in the container, we set
    //    // the custom resolver on it (this works in Laravel >= 5.2 as well).
    //    $this->app->resolving(
    //        'validator', function ($factory, $app)
    //    {
    //        $factory->resolver(
    //            function ($translator, $data, $rules, $messages, $customAttributes = [])
    //            {
    //                return new ValidatorExtension(
    //                    $translator,
    //                    $data,
    //                    $rules,
    //                    $messages,
    //                    $customAttributes
    //                );
    //            }
    //        );
    //    }
    //    );
    //}
}