<?php

namespace App\Waypoint\Providers;

use App\Waypoint\Exceptions\AuthServiceException;
use App\Waypoint\Http\ApiGuardAuth;
use App\Waypoint\Models\AccessList;
use App\Waypoint\Models\AccessListProperty;
use App\Waypoint\Models\AccessListUser;
use App\Waypoint\Models\AdvancedVariance;
use App\Waypoint\Models\AdvancedVarianceApproval;
use App\Waypoint\Models\AdvancedVarianceExplanationType;
use App\Waypoint\Models\AdvancedVarianceLineItem;
use App\Waypoint\Models\AdvancedVarianceThreshold;
use App\Waypoint\Models\AssetType;
use App\Waypoint\Models\AuthenticatingEntity;
use App\Waypoint\Models\CalculatedField;
use App\Waypoint\Models\CalculatedFieldEquation;
use App\Waypoint\Models\CalculatedFieldEquationProperty;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\ClientCategory;
use App\Waypoint\Models\Comment;
use App\Waypoint\Models\CustomReport;
use App\Waypoint\Models\CustomReportType;
use App\Waypoint\Models\EcmProject;
use App\Waypoint\Models\EntityTagEntity;
use App\Waypoint\Models\Favorite;
use App\Waypoint\Models\Lease;
use App\Waypoint\Models\NativeAccount;
use App\Waypoint\Models\NativeAccountType;
use App\Waypoint\Models\NativeAccountTypeTrailer;
use App\Waypoint\Models\NativeCoa;
use App\Waypoint\Models\Opportunity;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\PropertyGroup;
use App\Waypoint\Models\PropertyGroupProperty;
use App\Waypoint\Models\PropertyNativeCoa;
use App\Waypoint\Models\RelatedUser;
use App\Waypoint\Models\RelatedUserType;
use App\Waypoint\Models\ReportTemplate;
use App\Waypoint\Models\ReportTemplateAccountGroup;
use App\Waypoint\Models\ReportTemplateMapping;
use App\Waypoint\Models\Role;
use App\Waypoint\Models\Suite;
use App\Waypoint\Models\Tenant;
use App\Waypoint\Models\TenantAttribute;
use App\Waypoint\Models\TenantTenantAttribute;
use App\Waypoint\Models\TenantIndustry;
use App\Waypoint\Models\User;
use Cookie;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any application authentication / authorization services.
     *
     * @param \Illuminate\Contracts\Auth\Access\Gate $gate
     * @return void
     */
    public function boot(GateContract $gate)
    {
        $this->registerPolicies();

        /**
         * @todo move this logic into Policy classes
         * see https://laravel.com/docs/5.1/authorization
         */
        $gate->before(
        /** @noinspection PhpInconsistentReturnPointsInspection */
            function (User $UserObj, $policy)
            {
                if ( ! $policy)
                {
                    throw new AuthServiceException('Access policy failure' . __FILE__ . ':' . __LINE__);
                }
                if ($UserObj->roleIsAtLeast(Role::WAYPOINT_ROOT_ROLE))
                {
                    return true;
                }
                /**
                 * @todo See HER-850
                 */
                if (ApiGuardAuth::getUser())
                {
                    return true;
                }
                return null;
            }
        );

        $gate->define(
            'user_active_status_access_policy',
            function (User $UserObj)
            {
                if (Cookie::get('CLIENT_ID_COOKIE'))
                {
                    return Cookie::get('CLIENT_ID_COOKIE') == $UserObj->client_id &&
                           $UserObj->active_status == User::ACTIVE_STATUS_ACTIVE;
                }
                return $UserObj->active_status == User::ACTIVE_STATUS_ACTIVE;
            }
        );

        $gate->define(
            'clients_access_policy',
            function (User $UserObj, $client_id)
            {
                if ($UserObj->roleIsAtLeast(Role::CLIENT_GENERIC_USER_ROLE))
                {
                    return $UserObj->client_id == $client_id;
                }
                return false;
            }
        );

        $gate->define(
            'properties_access_policy',
            function (User $UserObj, $property_id)
            {
                if ($UserObj->roleIsAtLeast(Role::CLIENT_ADMINISTRATIVE_USER_ROLE))
                {
                    if ( ! $PropertyObj = Property::find($property_id))
                    {
                        return false;
                    }
                    return $PropertyObj->client_id == $UserObj->client_id;
                }
                elseif ($UserObj->roleIsAtLeast(Role::CLIENT_GENERIC_USER_ROLE))
                {
                    if ( ! $PropertyObj = Property::find($property_id))
                    {
                        return false;
                    }
                    return $UserObj->canAccessProperty($property_id);
                }
                return false;
            }
        );

        $gate->define(
            'access_lists_access_policy',
            function (User $UserObj, $access_list_id)
            {
                if ($UserObj->roleIsAtLeast(Role::CLIENT_ADMINISTRATIVE_USER_ROLE))
                {
                    if ( ! $AccessListObj = AccessList::find($access_list_id))
                    {
                        return false;
                    }
                    return $AccessListObj->client_id == $UserObj->client_id;
                }
                elseif ($UserObj->roleIsAtLeast(Role::CLIENT_GENERIC_USER_ROLE))
                {
                    return in_array(
                        $access_list_id,
                        $UserObj->accessListUser->pluck('access_list_id')->toArray()
                    );
                }
                return false;
            }
        );

        $gate->define(
            'users_access_policy',
            function (User $UserObj, $user_id)
            {
                if ( ! $OtherUserObj = User::find($user_id))
                {
                    return false;
                }
                if ( ! $UserObj->roleIsAtLeast(Role::WAYPOINT_ASSOCIATE_ROLE))
                {
                    if ($OtherUserObj->is_hidden)
                    {
                        /**
                         * even users always have acces to themselves
                         */
                        return $UserObj->id == $user_id;
                    }
                }
                return $UserObj->client_id == $OtherUserObj->client_id;
            }
        );

        $gate->define(
            'calculated_fields_access_policy',
            function (User $UserObj, $calculated_field_id)
            {
                if ( ! $CalculatedFieldObj = CalculatedField::find($calculated_field_id))
                {
                    return false;
                }
                if ($UserObj->roleIsAtLeast(Role::CLIENT_GENERIC_USER_ROLE))
                {
                    return $CalculatedFieldObj->reportTemplate->client_id == $UserObj->client_id;
                }
                return false;
            }
        );

        $gate->define(
            'calculated_fields_equation_access_policy',
            function (User $UserObj, $calculated_field_equation_id)
            {
                if ( ! $CalculatedFieldEquationObj = CalculatedFieldEquation::find($calculated_field_equation_id))
                {
                    return false;
                }
                if ($UserObj->roleIsAtLeast(Role::CLIENT_GENERIC_USER_ROLE))
                {
                    return $CalculatedFieldEquationObj->calculatedField->reportTemplate->client_id == $UserObj->client_id;
                }
                return false;
            }
        );

        $gate->define(
            'calculated_fields_equation_property_access_policy',
            function (User $UserObj, $calculated_field_equation_property_id)
            {
                if ( ! $CalculatedFieldEquationPropertyObj = CalculatedFieldEquationProperty::find($calculated_field_equation_property_id))
                {
                    return false;
                }
                if ($UserObj->roleIsAtLeast(Role::CLIENT_GENERIC_USER_ROLE))
                {
                    return $CalculatedFieldEquationPropertyObj->calculatedFieldEquation->calculatedField->reportTemplate->client_id == $UserObj->client_id;
                }
                return false;
            }
        );

        $gate->define(
            'property_groups_access_policy',
            function (User $UserObj, $property_group_id)
            {
                if ($UserObj->roleIsAtLeast(Role::CLIENT_ADMINISTRATIVE_USER_ROLE))
                {
                    if ( ! $OtherPropertyGroupObj = PropertyGroup::find($property_group_id))
                    {
                        return false;
                    }
                    return $OtherPropertyGroupObj->user->client_id == $UserObj->client_id || $OtherPropertyGroupObj->user->client_id == $UserObj->client_id;
                }
                elseif ($UserObj->roleIsAtLeast(Role::CLIENT_GENERIC_USER_ROLE))
                {
                    return $UserObj->propertyGroupIsAccessible($property_group_id);
                }
                return false;
            }
        );

        $gate->define(
            'property_group_properties_access_policy',
            function (User $UserObj, $property_group_property_id)
            {
                if ( ! $PropertyGroupPropertyObj = PropertyGroupProperty::find($property_group_property_id))
                {
                    return false;
                }
                if ($UserObj->roleIsAtLeast(Role::CLIENT_ADMINISTRATIVE_USER_ROLE))
                {
                    return $PropertyGroupPropertyObj->propertyGroup->client_id == $UserObj->client_id;
                }
                elseif ($UserObj->roleIsAtLeast(Role::CLIENT_GENERIC_USER_ROLE))
                {
                    return $UserObj->propertyGroupIsAccessible($PropertyGroupPropertyObj->property_group_id);
                }
                return false;
            }
        );

        $gate->define(
            'favorites_access_policy',
            function (User $UserObj, $favorite_id)
            {
                /**
                 * for favorites, Delete is different. see controller layer
                 * See HER-656
                 */
                if ( ! $FavoriteObj = Favorite::find($favorite_id))
                {
                    return true;
                }

                if ($UserObj->roleIsAtLeast(Role::CLIENT_ADMINISTRATIVE_USER_ROLE))
                {
                    if ($FavoriteObj->client_id)
                    {
                        return $UserObj->client_id == $UserObj->client_id;
                    }
                    elseif ($FavoriteObj->user_id)
                    {
                        if ( ! $UserObj->roleIsAtLeast(Role::WAYPOINT_ASSOCIATE_ROLE))
                        {
                            if ($FavoriteObj->user->is_hidden)
                            {
                                /**
                                 * even users always have acces to themselves
                                 */
                                return $UserObj->id == $FavoriteObj->user_id;
                            }
                        }
                        return $FavoriteObj->user->client_id == $UserObj->client_id;
                    }
                    else
                    {
                        throw new AuthServiceException('Access policy failure' . __FILE__ . ':' . __LINE__);
                    }
                }
                elseif ($UserObj->roleIsAtLeast(Role::CLIENT_GENERIC_USER_ROLE))
                {
                    if ( ! $UserObj->roleIsAtLeast(Role::WAYPOINT_ASSOCIATE_ROLE))
                    {
                        if ($FavoriteObj->user->is_hidden)
                        {
                            /**
                             * even users always have acces to themselves
                             */
                            return $UserObj->id == $FavoriteObj->user_id;
                        }
                    }
                    return $FavoriteObj->user_id == $UserObj->id;
                }
                else
                {
                    throw new AuthServiceException('Access policy failure' . __FILE__ . ':' . __LINE__);
                }
            }
        );

        $gate->define(
            'entity_tag_entities_access_policy',
            function (User $UserObj, $entity_tag_entity_id)
            {
                if ( ! $EntityTagEntityObj = EntityTagEntity::find($entity_tag_entity_id))
                {
                    return false;
                }
                if ($UserObj->roleIsAtLeast(Role::CLIENT_ADMINISTRATIVE_USER_ROLE)
                )
                {
                    if ($EntityTagEntityObj->client_id == $UserObj->client_id)
                    {
                        return true;
                    }

                    if ( ! $UserObj = User::find($EntityTagEntityObj->user_id))
                    {
                        return false;
                    }
                    if ( ! $UserObj->roleIsAtLeast(Role::WAYPOINT_ASSOCIATE_ROLE))
                    {
                        if ($UserObj->is_hidden)
                        {
                            /**
                             * even users always have acces to themselves
                             */
                            return $UserObj->id == $entity_tag_entity_id;
                        }
                    }
                    if ($UserObj->client_id == $UserObj->client_id)
                    {
                        return true;
                    }
                }
                elseif ($UserObj->roleIsAtLeast(Role::CLIENT_GENERIC_USER_ROLE))
                {
                    if ( ! $UserObj->roleIsAtLeast(Role::WAYPOINT_ASSOCIATE_ROLE))
                    {
                        if ($EntityTagEntityObj->user->is_hidden)
                        {
                            /**
                             * even users always have acces to themselves
                             */
                            return $UserObj->id == $entity_tag_entity_id;
                        }
                    }
                    return $EntityTagEntityObj->user_id == $UserObj->id;
                }
                else
                {
                    throw new AuthServiceException('Access policy failure' . __FILE__ . ':' . __LINE__);
                }
                return false;
            }
        );
        $gate->define(
            'native_coa_access_policy',
            function (User $UserObj, $native_coa_id)
            {
                if ( ! $NativeCoaObj = NativeCoa::find($native_coa_id))
                {
                    return false;
                }
                if ($UserObj->roleIsAtLeast(Role::CLIENT_GENERIC_USER_ROLE)
                )
                {
                    return $NativeCoaObj->client_id == $UserObj->client_id;
                }
                else
                {
                    throw new AuthServiceException('Access policy failure' . __FILE__ . ':' . __LINE__);
                }
            }
        );

        $gate->define(
            'ecm_projects_access_policy',
            function (User $UserObj, $ecm_project_id)
            {
                if ( ! $EcmProjectObj = EcmProject::find($ecm_project_id))
                {
                    return false;
                }
                if ($UserObj->roleIsAtLeast(Role::CLIENT_GENERIC_USER_ROLE))
                {
                    return $EcmProjectObj->property->client_id == $UserObj->client_id;
                }
                else
                {
                    throw new AuthServiceException('Access policy failure' . __FILE__ . ':' . __LINE__);
                }
            }
        );

        $gate->define(
            'access_list_properties_access_policy',
            function (User $UserObj, $access_list_property_id)
            {
                if ( ! $AccessListPropertyObj = AccessListProperty::find($access_list_property_id))
                {
                    return false;
                }
                if ($UserObj->roleIsAtLeast(Role::CLIENT_ADMINISTRATIVE_USER_ROLE))
                {
                    return $AccessListPropertyObj->property->client_id == $UserObj->client_id;
                }
                elseif ($UserObj->roleIsAtLeast(Role::CLIENT_GENERIC_USER_ROLE))
                {
                    return false;
                }
                else
                {
                    throw new AuthServiceException('Access policy failure' . __FILE__ . ':' . __LINE__);
                }
            }
        );

        $gate->define(
            'access_list_users_access_policy',
            function (User $UserObj, $access_list_user_id)
            {
                if ( ! $AccessListUserObj = AccessListUser::find($access_list_user_id))
                {
                    return false;
                }
                if ( ! $UserObj->roleIsAtLeast(Role::WAYPOINT_ASSOCIATE_ROLE))
                {
                    if ($AccessListUserObj->user->is_hidden)
                    {
                        return false;
                    }
                }
                if ($UserObj->roleIsAtLeast(Role::CLIENT_ADMINISTRATIVE_USER_ROLE)
                )
                {
                    return $AccessListUserObj->user->client_id == $UserObj->client_id;
                }
                elseif ($UserObj->roleIsAtLeast(Role::CLIENT_GENERIC_USER_ROLE))
                {
                    return false;
                }
                else
                {
                    throw new AuthServiceException('Access policy failure' . __FILE__ . ':' . __LINE__);
                }
            }
        );

        $gate->define(
            'controller_licenses_access_policy',
            function (User $UserObj, $needed_config)
            {
                /** @var Client $ClientObj */
                $ClientObj = $UserObj->client;

                $ClientConfigObj = $ClientObj->getConfigJSON();
                if (isset($ClientConfigObj->$needed_config) && $ClientConfigObj->$needed_config == true)
                {
                    return true;
                }
                return false;
            }
        );

        $gate->define(
            'opportunities_access_policy',
            function (User $UserObj, $opportunity_id)
            {
                if ( ! $UserObj->client->canUseOpportunities())
                {
                    return false;
                }
                if ( ! $OpportunityObj = Opportunity::find($opportunity_id))
                {
                    return false;
                }
                if ($UserObj->roleIsAtLeast(Role::CLIENT_ADMINISTRATIVE_USER_ROLE)
                )
                {
                    return $OpportunityObj->property->client_id == $UserObj->client_id;
                }
                elseif ($UserObj->roleIsAtLeast(Role::CLIENT_GENERIC_USER_ROLE))
                {
                    return $UserObj->canAccessProperty($OpportunityObj->property_id);
                }
                else
                {
                    throw new AuthServiceException('Access policy failure' . __FILE__ . ':' . __LINE__);
                }
            }
        );
        $gate->define(
            'client_category_policy',
            function (User $UserObj, $client_category_id)
            {
                if ( ! $ClientCategoryObj = ClientCategory::find($client_category_id))
                {
                    return false;
                }
                if ($UserObj->roleIsAtLeast(Role::CLIENT_ADMINISTRATIVE_USER_ROLE)
                )
                {
                    return $ClientCategoryObj->client_id == $UserObj->client_id;
                }
                elseif ($UserObj->roleIsAtLeast(Role::CLIENT_GENERIC_USER_ROLE))
                {
                    return $ClientCategoryObj->client_id == $UserObj->client_id;
                }
                else
                {
                    throw new AuthServiceException('Access policy failure' . __FILE__ . ':' . __LINE__);
                }
            }
        );

        $gate->define(
            'comments_access_policy',
            function (User $UserObj, $comment_id)
            {
                if ( ! $CommentObj = Comment::find($comment_id))
                {
                    return false;
                }
                $CommenterUserObj = User::find($CommentObj->commented_id);
                if ($UserObj->roleIsAtLeast(Role::CLIENT_ADMINISTRATIVE_USER_ROLE))
                {
                    return $UserObj->client_id == $CommenterUserObj->client_id;
                }
                elseif ($UserObj->roleIsAtLeast(Role::CLIENT_GENERIC_USER_ROLE))
                {
                    /**
                     * in other words, allow if comment was created by $User
                     */
                    return $UserObj->id == $CommenterUserObj->id;
                }
                else
                {
                    throw new AuthServiceException('Access policy failure' . __FILE__ . ':' . __LINE__);
                }
            }
        );

        $gate->define(
            'related_user_type_access_policy',
            function (User $UserObj, $related_user_type_id)
            {
                if ( ! $RelatedUserTypeObj = RelatedUserType::find($related_user_type_id))
                {
                    return false;
                }
                $client_id = $RelatedUserTypeObj->client_id;
                if ($UserObj->roleIsAtLeast(Role::CLIENT_GENERIC_USER_ROLE))
                {
                    return $UserObj->client_id == $client_id;
                }
                else
                {
                    throw new AuthServiceException('Access policy failure' . __FILE__ . ':' . __LINE__);
                }
            }
        );

        $gate->define(
            'advanced_variances_access_policy',
            function (User $UserObj, $advanced_variance_id)
            {
                if ( ! $AdvancedVarianceObj = AdvancedVariance::find($advanced_variance_id))
                {
                    return false;
                }
                if ($UserObj->roleIsAtLeast(Role::CLIENT_ADMINISTRATIVE_USER_ROLE))
                {
                    return $UserObj->client_id == $AdvancedVarianceObj->property->client_id;
                }
                elseif ($UserObj->roleIsAtLeast(Role::CLIENT_GENERIC_USER_ROLE))
                {
                    return $UserObj->canAccessProperty($AdvancedVarianceObj->property_id);
                }
                else
                {
                    throw new AuthServiceException('Access policy failure' . __FILE__ . ':' . __LINE__);
                }
            }
        );

        $gate->define(
            'native_account_type_policy',
            function (User $UserObj, $native_account_type_id)
            {
                /** @var NativeAccountType $NativeAccountTypeObj */
                if ( ! $NativeAccountTypeObj = NativeAccountType::find($native_account_type_id))
                {
                    return false;
                }
                if ($UserObj->roleIsAtLeast(Role::CLIENT_GENERIC_USER_ROLE))
                {
                    return $UserObj->client_id == $NativeAccountTypeObj->client_id;
                }
                else
                {
                    throw new AuthServiceException('Access policy failure' . __FILE__ . ':' . __LINE__);
                }
            }
        );

        $gate->define(
            'native_account_type_trailer_policy',
            function (User $UserObj, $native_account_type_trailer_id)
            {
                /** @var NativeAccountType $NativeAccountTypeObj */
                if ( ! $NativeAccountTypeTrailerObj = NativeAccountTypeTrailer::find($native_account_type_trailer_id))
                {
                    return false;
                }
                if (
                    $UserObj->hasRole(Role::CLIENT_ADMINISTRATIVE_USER_ROLE) ||
                    $UserObj->hasRole(Role::WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE) ||
                    $UserObj->hasRole(Role::WAYPOINT_ASSOCIATE_ROLE) ||
                    $UserObj->hasRole(Role::CLIENT_GENERIC_USER_ROLE)
                )
                {
                    return $UserObj->client_id == $NativeAccountTypeTrailerObj->nativeAccountType->client_id;
                }
                else
                {
                    throw new AuthServiceException('Access policy failure' . __FILE__ . ':' . __LINE__);
                }
            }
        );

        $gate->define(
            'native_account_access_policy',
            function (User $UserObj, $native_account_id)
            {
                /** @var NativeAccountType $NativeAccountObj */
                if ( ! $NativeAccountObj = NativeAccount::find($native_account_id))
                {
                    return false;
                }
                if ($UserObj->roleIsAtLeast(Role::CLIENT_GENERIC_USER_ROLE))
                {
                    return $UserObj->client_id == $NativeAccountObj->nativeCoa->client_id;
                }
                else
                {
                    throw new AuthServiceException('Access policy failure' . __FILE__ . ':' . __LINE__);
                }
            }
        );

        $gate->define(
            'report_template_access_policy',
            function (User $UserObj, $report_template_id)
            {
                /** @var NativeAccountType $NativeAccountTypeObj */
                if ( ! $ReportTemplateObj = ReportTemplate::find($report_template_id))
                {
                    return false;
                }
                if ($UserObj->roleIsAtLeast(Role::CLIENT_GENERIC_USER_ROLE))
                {
                    return $report_template_id == 1 || $UserObj->client_id == $ReportTemplateObj->client_id;
                }
                else
                {
                    throw new AuthServiceException('Access policy failure' . __FILE__ . ':' . __LINE__);
                }
            }
        );

        $gate->define(
            'report_template_account_group_access_policy',
            function (User $UserObj, $report_template_account_group_id)
            {
                /** @var NativeAccountType $NativeAccountTypeObj */
                if ( ! $ReportTemplateAccountGroupObj = ReportTemplateAccountGroup::find($report_template_account_group_id))
                {
                    return false;
                }
                if ($UserObj->roleIsAtLeast(Role::CLIENT_GENERIC_USER_ROLE))
                {
                    return $ReportTemplateAccountGroupObj->report_template_id == 1 || $UserObj->client_id == $ReportTemplateAccountGroupObj->reportTemplate->client_id;
                }
                else
                {
                    throw new AuthServiceException('Access policy failure' . __FILE__ . ':' . __LINE__);
                }
            }
        );

        $gate->define(
            'report_template_mapping_access_policy',
            function (User $UserObj, $report_template_mapping_id)
            {
                /** @var NativeAccountType $NativeAccountTypeObj */
                if ( ! $ReportTemplateMappingObj = ReportTemplateMapping::find($report_template_mapping_id))
                {
                    return false;
                }
                if ($UserObj->roleIsAtLeast(Role::CLIENT_GENERIC_USER_ROLE))
                {
                    return $ReportTemplateMappingObj->reportTemplateAccountGroup->report_template_id == 1 || $UserObj->client_id == $ReportTemplateMappingObj->reportTemplateAccountGroup->reportTemplate->client_id;
                }
                else
                {
                    throw new AuthServiceException('Access policy failure' . __FILE__ . ':' . __LINE__);
                }
            }
        );

        $gate->define(
            'custom_report_type_access_policy',
            function (User $UserObj, $custom_report_type_id)
            {
                if ( ! $CustomReportTypeObj = CustomReportType::find($custom_report_type_id))
                {
                    return false;
                }
                if ($UserObj->roleIsAtLeast(Role::CLIENT_ADMINISTRATIVE_USER_ROLE))
                {
                    return $UserObj->client_id == $CustomReportTypeObj->client_id;
                }
                else
                {
                    throw new AuthServiceException('Access policy failure' . __FILE__ . ':' . __LINE__);
                }
            }
        );

        $gate->define(
            'custom_reports_access_policy',
            function (User $UserObj, $custom_report_id)
            {
                if ( ! $CustomReportObj = CustomReport::find($custom_report_id))
                {
                    return false;
                }
                elseif ($UserObj->roleIsAtLeast(Role::CLIENT_GENERIC_USER_ROLE))
                {
                    if ( ! is_null($CustomReportObj->property_id))
                    {
                        return $UserObj->canAccessProperty($CustomReportObj->property_id);
                    }
                    if ( ! is_null($CustomReportObj->property_group_id))
                    {
                        return $UserObj->propertyGroupIsAccessible(
                            $CustomReportObj->property_group_id
                        );
                    }
                }
                else
                {
                    throw new AuthServiceException('Access policy failure' . __FILE__ . ':' . __LINE__);
                }
            }
        );

        $gate->define(
            'advanced_variance_line_items_access_policy',
            function (User $UserObj, $advanced_variance_line_item_id)
            {
                /** var AdvancedVarianceLineItem $AdvancedVarianceLineItemObj */
                if ( ! $AdvancedVarianceLineItemObj = AdvancedVarianceLineItem::find($advanced_variance_line_item_id))
                {
                    return false;
                }
                $client_id = $AdvancedVarianceLineItemObj->advancedVariance->property->client_id;
                if ($UserObj->roleIsAtLeast(Role::CLIENT_ADMINISTRATIVE_USER_ROLE))
                {
                    return $UserObj->client_id == $client_id;
                }
                elseif ($UserObj->roleIsAtLeast(Role::CLIENT_GENERIC_USER_ROLE))
                {
                    return $UserObj->canAccessProperty($AdvancedVarianceLineItemObj->advancedVariance->property_id);
                }
                else
                {
                    throw new AuthServiceException('Access policy failure' . __FILE__ . ':' . __LINE__);
                }
            }
        );

        $gate->define(
            'advanced_variance_approval_access_policy',
            function (User $UserObj, $advanced_variance_approval_id)
            {
                /** var AdvancedVarianceLineItem $AdvancedVarianceLineItemObj */
                if ( ! $AdvancedVarianceApprovalObj = AdvancedVarianceApproval::find($advanced_variance_approval_id))
                {
                    return false;
                }
                $client_id = $AdvancedVarianceApprovalObj->advancedVariance->property->client_id;
                if ($UserObj->roleIsAtLeast(Role::CLIENT_ADMINISTRATIVE_USER_ROLE))
                {
                    return $UserObj->client_id == $client_id;
                }
                elseif ($UserObj->roleIsAtLeast(Role::CLIENT_GENERIC_USER_ROLE))
                {
                    return $UserObj->canAccessProperty($AdvancedVarianceApprovalObj->advancedVariance->property_id);
                }
                else
                {
                    throw new AuthServiceException('Access policy failure' . __FILE__ . ':' . __LINE__);
                }
            }
        );

        $gate->define(
            'advanced_variance_explanation_type_access_policy',
            function (User $UserObj, $advanced_variance_explanation_type_id)
            {
                /** var AdvancedVarianceExplanationType $AdvancedVarianceExplanationTypeObj */
                if ( ! $AdvancedVarianceExplanationTypeObj = AdvancedVarianceExplanationType::find($advanced_variance_explanation_type_id))
                {
                    return false;
                }
                $client_id = $AdvancedVarianceExplanationTypeObj->client_id;
                if ($UserObj->roleIsAtLeast(Role::CLIENT_GENERIC_USER_ROLE))
                {
                    return $UserObj->client_id == $client_id;
                }
                else
                {
                    throw new AuthServiceException('Access policy failure' . __FILE__ . ':' . __LINE__);
                }
            }
        );

        $gate->define(
            'advanced_variance_threshold_access_policy',
            function (User $UserObj, $advanced_variance_threshold_id)
            {
                /** var AdvancedVarianceExplanationType $AdvancedVarianceExplanationTypeObj */
                if ( ! $AdvancedVarianceExplanationTypeObj = AdvancedVarianceThreshold::find($advanced_variance_threshold_id))
                {
                    return false;
                }
                $client_id = $AdvancedVarianceExplanationTypeObj->client_id;
                if (
                    $UserObj->hasRole(Role::CLIENT_ADMINISTRATIVE_USER_ROLE) ||
                    $UserObj->hasRole(Role::WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE) ||
                    $UserObj->hasRole(Role::WAYPOINT_ASSOCIATE_ROLE) ||
                    $UserObj->hasRole(Role::CLIENT_GENERIC_USER_ROLE)
                )
                {
                    return $UserObj->client_id == $client_id;
                }
                else
                {
                    throw new AuthServiceException('Access policy failure' . __FILE__ . ':' . __LINE__);
                }
            }
        );

        /**
         * @todo make this follow the pattern in assigned_user_access_policy
         */
        $gate->define(
            'related_user_access_policy',
            function (User $UserObj, $related_user_id)
            {
                if ( ! $RelatedUserObj = RelatedUser::find($related_user_id))
                {
                    return false;
                }
                if ( ! $UserObj->roleIsAtLeast(Role::WAYPOINT_ASSOCIATE_ROLE))
                {
                    if ($RelatedUserObj->user->is_hidden)
                    {
                        return false;
                    }
                }
                if ($UserObj->roleIsAtLeast(Role::CLIENT_GENERIC_USER_ROLE))
                {
                    return $UserObj->client_id == RelatedUser::find($related_user_id)->user->client_id;
                }
                else
                {
                    throw new AuthServiceException('Access policy failure' . __FILE__ . ':' . __LINE__);
                }
            }
        );

        $gate->define(
            'assigned_user_access_policy',
            function (User $UserObj, $assigned_user_id, $related_params_arr)
            {
                if ( ! $AssignedUserObj = User::find($assigned_user_id))
                {
                    return false;
                }
                if ( ! $UserObj->roleIsAtLeast(Role::WAYPOINT_ASSOCIATE_ROLE))
                {
                    if ($AssignedUserObj->is_hidden)
                    {
                        return false;
                    }
                }
                if (isset($related_params_arr['opportunity_id']))
                {
                    if ( ! $OpportunityObj = Opportunity::find($related_params_arr['opportunity_id']))
                    {
                        return false;
                    }

                    if ( ! $AssignedUserObj->canAccessProperty($OpportunityObj->property_id))
                    {
                        return false;
                    }
                }
                if (isset($related_params_arr['property_id']))
                {
                    if ( ! $AssignedUserObj->canAccessProperty($related_params_arr['property_id']))
                    {
                        return false;
                    }
                }

                /**
                 * now think positive
                 */
                if ($UserObj->roleIsAtLeast(Role::CLIENT_GENERIC_USER_ROLE))
                {
                    return $UserObj->client_id == $AssignedUserObj->client_id;
                }
                else
                {
                    throw new AuthServiceException('Access policy failure' . __FILE__ . ':' . __LINE__);
                }
            }
        );

        $gate->define(
            'created_by_user_access_policy',
            function (User $UserObj, $created_by_user_id, $related_params_arr)
            {
                if ( ! $CreatedByUserObj = User::find($created_by_user_id))
                {
                    return false;
                }
                if ( ! $UserObj->roleIsAtLeast(Role::WAYPOINT_ASSOCIATE_ROLE))
                {
                    if ($CreatedByUserObj->is_hidden)
                    {
                        return false;
                    }
                }
                if (isset($related_params_arr['opportunity_id']))
                {
                    if ( ! $OpportunityObj = Opportunity::find($related_params_arr['opportunity_id']))
                    {
                        return false;
                    }

                    if ( ! $CreatedByUserObj->canAccessProperty($OpportunityObj->property_id))
                    {
                        return false;
                    }
                }
                if (isset($related_params_arr['property_id']))
                {
                    if ( ! $CreatedByUserObj->canAccessProperty($related_params_arr['property_id']))
                    {
                        return false;
                    }
                }

                /**
                 * now think positive
                 */
                if ($UserObj->roleIsAtLeast(Role::CLIENT_GENERIC_USER_ROLE))
                {
                    return $UserObj->client_id == $CreatedByUserObj->client_id;
                }
                else
                {
                    throw new AuthServiceException('Access policy failure' . __FILE__ . ':' . __LINE__);
                }
            }
        );

        $gate->define(
            'property_native_coa_access_policy',
            function (User $UserObj, $property_native_coa_id, $related_params_arr)
            {
                if ( ! $PropertyNativeCoaObj = PropertyNativeCoa::find($property_native_coa_id))
                {
                    return false;
                }
                if ($UserObj->roleIsAtLeast(Role::CLIENT_ADMINISTRATIVE_USER_ROLE))
                {
                    return $PropertyNativeCoaObj->property->client_id == $UserObj->client_id;
                }
                elseif ($UserObj->roleIsAtLeast(Role::CLIENT_GENERIC_USER_ROLE))
                {
                    return $UserObj->canAccessProperty($PropertyNativeCoaObj->property_id);
                }
                else
                {
                    throw new AuthServiceException('Access policy failure' . __FILE__ . ':' . __LINE__);
                }
            }
        );

        $gate->define(
            'leases_access_policy',
            function (User $UserObj, $lease_id, $related_params_arr = [])
            {
                if ( ! $LeaseObj = Lease::find($lease_id))
                {
                    return false;
                }
                if ($UserObj->roleIsAtLeast(Role::CLIENT_ADMINISTRATIVE_USER_ROLE))
                {
                    return $LeaseObj->property->client_id == $UserObj->client_id;
                }
                elseif ($UserObj->roleIsAtLeast(Role::CLIENT_GENERIC_USER_ROLE))
                {
                    return $UserObj->canAccessProperty($LeaseObj->property_id);
                }
                else
                {
                    throw new AuthServiceException('Access policy failure' . __FILE__ . ':' . __LINE__);
                }
            }
        );

        $gate->define(
            'suites_access_policy',
            function (User $UserObj, $suite_id, $related_params_arr = [])
            {
                if ( ! $SuiteObj = Suite::find($suite_id))
                {
                    return false;
                }
                if ($UserObj->roleIsAtLeast(Role::CLIENT_ADMINISTRATIVE_USER_ROLE))
                {
                    return $SuiteObj->property->client_id == $UserObj->client_id;
                }
                elseif ($UserObj->roleIsAtLeast(Role::CLIENT_GENERIC_USER_ROLE))
                {
                    return $UserObj->canAccessProperty($SuiteObj->property_id);
                }
                else
                {
                    throw new AuthServiceException('Access policy failure' . __FILE__ . ':' . __LINE__);
                }
            }
        );

        $gate->define(
            'asset_types_access_policy',
            function (User $UserObj, $asset_type_id, $related_params_arr = [])
            {
                if ( ! $AssetTypeObj = AssetType::find($asset_type_id))
                {
                    return false;
                }
                elseif ($UserObj->roleIsAtLeast(Role::CLIENT_GENERIC_USER_ROLE))
                {
                    return $UserObj->client_id == $AssetTypeObj->client_id;
                }
                else
                {
                    throw new AuthServiceException('Access policy failure' . __FILE__ . ':' . __LINE__);
                }
            }
        );

        $gate->define(
            'authenticating_entities_access_policy',
            function (User $UserObj, $authenticating_entity_id, $related_params_arr = [])
            {
                if ( ! $AuthenticatingEntityObj = AuthenticatingEntity::find($authenticating_entity_id))
                {
                    return false;
                }
                if ($UserObj->roleIsAtLeast(Role::WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE))
                {
                    return true;
                }
                else
                {
                    throw new AuthServiceException('Access policy failure' . __FILE__ . ':' . __LINE__);
                }
            }
        );

        $gate->define(
            'tenant_industry_access_policy',
            function (User $UserObj, $tenant_industry_id, $related_params_arr = [])
            {
                /** @var TenantIndustry $TenantIndustryObj */
                if ( ! $TenantIndustryObj = TenantIndustry::find($tenant_industry_id))
                {
                    return false;
                }
                if ($UserObj->roleIsAtLeast(Role::CLIENT_GENERIC_USER_ROLE))
                {
                    return $UserObj->client_id == $TenantIndustryObj->client_id;
                }
            }
        );

        $gate->define(
            'tenant_attributes_access_policy',
            function (User $UserObj, $tenant_attribute_id, $related_params_arr = [])
            {
                /** @var TenantAttribute $TenantAtributeObj */
                if ( ! $TenantAtributeObj = TenantAttribute::find($tenant_attribute_id))
                {
                    return false;
                }
                if ($UserObj->roleIsAtLeast(Role::CLIENT_GENERIC_USER_ROLE))
                {
                    return $UserObj->client_id == $TenantAtributeObj->client_id;
                }
            }
        );

        $gate->define(
            'tenant_tenant_attributes_access_policy',
            function (User $UserObj, $tenant_tenant_attribute_id, $related_params_arr = [])
            {
                /** @var TenantTenantAttribute $TenantTenantAtributeObj */
                if ( ! $TenantTenantAtributeObj = TenantTenantAttribute::find($tenant_tenant_attribute_id))
                {
                    return false;
                }
                if ($UserObj->roleIsAtLeast(Role::CLIENT_GENERIC_USER_ROLE))
                {
                    return $UserObj->client_id == $TenantTenantAtributeObj->tenant->client_id;
                }
            }
        );

        $gate->define(
            'tenants_access_policy',
            function (User $UserObj, $tenant_id, $related_params_arr = [])
            {
                /** @var Tenant $TenantObj */
                if ( ! $TenantObj = Tenant::find($tenant_id))
                {
                    return false;
                }
                if ($UserObj->roleIsAtLeast(Role::CLIENT_ADMINISTRATIVE_USER_ROLE))
                {
                    return true;
                }
                else
                {
                    /** @var Lease $LeaseObj */
                    foreach ($TenantObj->leases as $LeaseObj)
                    {
                        if ($UserObj->canAccessProperty($LeaseObj->property_id))
                        {
                            return true;
                        }
                    }
                    return false;
                }
            }
        );
    }
}
