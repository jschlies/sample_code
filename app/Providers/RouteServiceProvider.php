<?php

namespace App\Waypoint\Providers;

use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Config;
use Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to the controller routes in your routes file.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Waypoint\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        Route::pattern('access_list_id', '[0-9]+');
        Route::pattern('access_list_property_id', '[0-9]+');
        Route::pattern('access_list_user_id', '[0-9]+');
        Route::pattern('advanced_variance_approval_id', '[0-9]+');
        Route::pattern('advanced_variance_id', '[0-9]+');
        Route::pattern('advanced_variance_line_item_id', '[0-9]+');
        Route::pattern('api_key_id', '[0-9]+');
        Route::pattern('api_log_id', '[0-9]+');
        Route::pattern('attachable_id', '[0-9]+');
        Route::pattern('attachment_id', '[0-9]+');
        Route::pattern('audit_id', '[0-9]+');
        Route::pattern('audit_relation_id', '[0-9]+');
        Route::pattern('client_category_id', '[0-9]+');
        Route::pattern('client_category_id', '[0-9]+');
        Route::pattern('client_id', '[0-9]+');
        Route::pattern('comment_id', '[0-9]+');
        Route::pattern('comment_mention_id', '[0-9]+');
        Route::pattern('commentable_id', '[0-9]+');
        Route::pattern('download_history_id', '[0-9]+');
        Route::pattern('ecm_project_id', '[0-9]+');
        Route::pattern('ecm_project_id', '[0-9]+');
        Route::pattern('entity_tag_entity_id', '[0-9]+');
        Route::pattern('entity_tag_id', '[0-9]+');
        Route::pattern('failed_job_id', '[0-9]+');
        Route::pattern('favorite_id', '[0-9]+');
        Route::pattern('id', '[0-9]+');
        Route::pattern('lease_id', '[0-9]+');
        Route::pattern('lease_tenant_id', '[0-9]+');
        Route::pattern('native_account_id', '[0-9]+');
        Route::pattern('native_coa_id', '[0-9]+');
        Route::pattern('notification_log_id', '[0-9]+');
        Route::pattern('opportunity_id', '[0-9]+');
        Route::pattern('password_reset_id', '[0-9]+');
        Route::pattern('permission_id', '[0-9]+');
        Route::pattern('permission_role_id', '[0-9]+');
        Route::pattern('property_group_id', '[0-9]+');
        Route::pattern('property_group_property_id', '[0-9]+');
        Route::pattern('property_group_user_id', '[0-9]+');
        Route::pattern('property_id', '[0-9]+');
        Route::pattern('property_mapping_group_id', '[0-9]+');
        Route::pattern('property_native_coa_id', '[0-9]+');
        Route::pattern('related_user_id', '[0-9]+');
        Route::pattern('related_user_type_id', '[0-9]+');
        Route::pattern('report_template', '[0-9]+');
        Route::pattern('report_template_account_group_id', '[0-9]+');
        Route::pattern('report_template_mapping_id', '[0-9]+');
        Route::pattern('role_id', '[0-9]+');
        Route::pattern('role_user_id', '[0-9]+');
        Route::pattern('suite_id', '[0-9]+');
        Route::pattern('suite_lease_id', '[0-9]+');
        Route::pattern('suite_tenant_id', '[0-9]+');
        Route::pattern('tagging_tag_group_id', '[0-9]+');
        Route::pattern('tagging_tagged_id', '[0-9]+');
        Route::pattern('tenant_id', '[0-9]+');
        Route::pattern('tenant_tenant_attribute_id', '[0-9]+');
        Route::pattern('user_id', '[0-9]+');

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @param \Illuminate\Routing\Router $router
     * @return void
     */
    public function map(Router $router)
    {
        /** @noinspection PhpUnusedParameterInspection */
        $router->group(
            ['namespace' => $this->namespace],
            function ($router)
            {
                require Config::get('waypoint.api_routes');
            }
        );
    }
}
