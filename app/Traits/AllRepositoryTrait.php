<?php

namespace App\Waypoint;

use App\Waypoint\Repositories\AccessListDetailRepository;
use App\Waypoint\Repositories\AccessListFullRepository;
use App\Waypoint\Repositories\AccessListPropertyRepository;
use App\Waypoint\Repositories\AccessListRepository;
use App\Waypoint\Repositories\AccessListSlimRepository;
use App\Waypoint\Repositories\AccessListSummaryRepository;
use App\Waypoint\Repositories\AccessListTrimmedSummaryRepository;
use App\Waypoint\Repositories\AccessListUserRepository;
use App\Waypoint\Repositories\AdvancedVarianceApprovalRepository;
use App\Waypoint\Repositories\AdvancedVarianceDetailRepository;
use App\Waypoint\Repositories\AdvancedVarianceExplanationTypeRepository;
use App\Waypoint\Repositories\AdvancedVarianceLineItemRepository;
use App\Waypoint\Repositories\AdvancedVarianceLineItemWorkflowRepository;
use App\Waypoint\Repositories\AdvancedVarianceRepository;
use App\Waypoint\Repositories\AdvancedVarianceThresholdRepository;
use App\Waypoint\Repositories\ApiKeyRepository;
use App\Waypoint\Repositories\ApiLogRepository;
use App\Waypoint\Repositories\AssetTypeRepository;
use App\Waypoint\Repositories\AuthenticatingEntityRepository;
use App\Waypoint\Repositories\CalculatedFieldEquationRepository;
use App\Waypoint\Repositories\CalculatedFieldRepository;
use App\Waypoint\Repositories\CalculateVariousPropertyListsRepository;
use App\Waypoint\Repositories\ClientCategoryRepository;
use App\Waypoint\Repositories\ClientDetailRepository;
use App\Waypoint\Repositories\ClientFullRepository;
use App\Waypoint\Repositories\ClientRepository;
use App\Waypoint\Repositories\CommentDetailRepository;
use App\Waypoint\Repositories\CustomReportRepository;
use App\Waypoint\Repositories\CustomReportTypeRepository;
use App\Waypoint\Repositories\DownloadHistoryRepository;
use App\Waypoint\Repositories\EcmProjectRepository;
use App\Waypoint\Repositories\FailedJobRepository;
use App\Waypoint\Repositories\FavoriteRepository;
use App\Waypoint\Repositories\HeartbeatDetailRepository;
use App\Waypoint\Repositories\HeartbeatRepository;
use App\Waypoint\Repositories\LeaseDetailRepository;
use App\Waypoint\Repositories\LeaseRepository;
use App\Waypoint\Repositories\LeaseScheduleRepository;
use App\Waypoint\Repositories\LeaseTenantRepository;
use App\Waypoint\Repositories\Ledger\PropertyGroupCalcStatusRepository;
use App\Waypoint\Repositories\NativeAccountAmountRepository;
use App\Waypoint\Repositories\NativeAccountRepository;
use App\Waypoint\Repositories\NativeAccountTypeRepository;
use App\Waypoint\Repositories\NativeAccountTypeTrailerRepository;
use App\Waypoint\Repositories\NativeCoaFullRepository;
use App\Waypoint\Repositories\NativeCoaRepository;
use App\Waypoint\Repositories\NotificationLogRepository;
use App\Waypoint\Repositories\OpportunityRepository;
use App\Waypoint\Repositories\PasswordRuleRepository;
use App\Waypoint\Repositories\PermissionRepository;
use App\Waypoint\Repositories\PermissionRoleRepository;
use App\Waypoint\Repositories\PropertyDetailRepository;
use App\Waypoint\Repositories\PropertyGroupPropertyRepository;
use App\Waypoint\Repositories\PropertyGroupRepository;
use App\Waypoint\Repositories\PropertyLeaseRollupRepository;
use App\Waypoint\Repositories\PropertyNativeCoaRepository;
use App\Waypoint\Repositories\PropertyRepository;
use App\Waypoint\Repositories\PropertySummaryRepository;
use App\Waypoint\Repositories\RelatedUserRepository;
use App\Waypoint\Repositories\RelatedUserTypeRepository;
use App\Waypoint\Repositories\ReportTemplateAccountGroupRepository;
use App\Waypoint\Repositories\ReportTemplateMappingRepository;
use App\Waypoint\Repositories\ReportTemplateRepository;
use App\Waypoint\Repositories\RoleRepository;
use App\Waypoint\Repositories\RoleUserRepository;
use App\Waypoint\Repositories\SuiteDetailRepository;
use App\Waypoint\Repositories\SuiteLeaseRepository;
use App\Waypoint\Repositories\SuiteRepository;
use App\Waypoint\Repositories\SuiteTenantRepository;
use App\Waypoint\Repositories\TenantAttributeRepository;
use App\Waypoint\Repositories\TenantIndustryRepository;
use App\Waypoint\Repositories\TenantRepository;
use App\Waypoint\Repositories\TenantTenantAttributeRepository;
use App\Waypoint\Repositories\UserAdminRepository;
use App\Waypoint\Repositories\UserDetailRepository;
use App\Waypoint\Repositories\UserInvitationRepository;
use App\Waypoint\Repositories\UserRepository;
use App\Waypoint\Repositories\UserSummaryRepository;
use Illuminate\Support\Facades\App;

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
trait AllRepositoryTrait
{
    /**
     * @var AccessListDetailRepository $AccessListDetailRepositoryObj
     * @var AccessListFullRepository $AccessListFullRepositoryObj
     * @var AccessListPropertyRepository $AccessListPropertyRepositoryObj
     * @var AccessListRepository $AccessListRepositoryObj
     * @var AccessListSlimRepository $AccessListSlimRepositoryObj
     * @var AccessListSummaryRepository $AccessListSummaryRepositoryObj
     * @var AccessListTrimmedSummaryRepository $AccessListTrimmedSummaryRepositoryObj
     * @var AccessListUserRepository $AccessListUserRepositoryObj
     * @var AdvancedVarianceApprovalRepository $AdvancedVarianceApprovalRepositoryObj
     * @var AdvancedVarianceDetailRepository $AdvancedVarianceDetailRepositoryObj
     * @var AdvancedVarianceExplanationTypeRepository $AdvancedVarianceExplanationTypeRepositoryObj
     * @var AdvancedVarianceLineItemRepository $AdvancedVarianceLineItemRepositoryObj
     * @var AdvancedVarianceLineItemWorkflowRepository $AdvancedVarianceLineItemWorkflowRepositoryObj
     * @var AdvancedVarianceRepository $AdvancedVarianceRepositoryObj
     * @var AdvancedVarianceThresholdRepository $AdvancedVarianceThresholdRepositoryObj
     * @var ApiKeyRepository $ApiKeyRepositoryObj
     * @var ApiLogRepository $ApiLogRepositoryObj
     * @var AssetTypeRepository $AssetTypeRepositoryObj
     * @var AuthenticatingEntityRepository $AuthenticatingEntityRepositoryObj
     * @var CalculatedFieldEquationRepository $CalculatedFieldEquationRepositoryObj
     * @var CalculatedFieldRepository $CalculatedFieldRepositoryObj
     * @var CalculateVariousPropertyListsRepository $CalculateVariousPropertyListsRepositoryObj
     * @var ClientCategoryRepository $ClientCategoryRepositoryObj
     * @var ClientDetailRepository $ClientDetailRepositoryObj
     * @var ClientFullRepository $ClientFullRepositoryObj
     * @var ClientRepository $ClientRepositoryObj
     * @var CommentDetailRepository $CommentDetailRepositoryObj
     * @var CustomReportRepository $CustomReportRepositoryObj
     * @var CustomReportTypeRepository $CustomReportTypeRepositoryObj
     * @var DownloadHistoryRepository $DownloadHistoryRepositoryObj
     * @var EcmProjectRepository $EcmProjectRepositoryObj
     * @var FailedJobRepository $FailedJobRepositoryObj
     * @var FavoriteRepository $FavoriteRepositoryObj
     * @var HeartbeatDetailRepository $HeartbeatDetailRepositoryObj
     * @var HeartbeatRepository $HeartbeatRepositoryObj
     * @var LeaseRepository $LeaseRepositoryObj
     * @var LeaseScheduleRepository $LeaseScheduleRepositoryObj
     * @var LeaseTenantRepository $LeaseTenantRepositoryObj
     * @var NativeAccountAmountRepository $NativeAccountAmountRepositoryObj
     * @var NativeAccountRepository $NativeAccountRepositoryObj
     * @var NativeAccountTypeRepository $NativeAccountTypeRepositoryObj
     * @var NativeAccountTypeTrailerRepository $NativeAccountTypeTrailerRepositoryObj
     * @var NativeCoaFullRepository $NativeCoaFullRepositoryObj
     * @var NativeCoaRepository $NativeCoaRepositoryObj
     * @var NotificationLogRepository $NotificationLogRepositoryObj
     * @var OpportunityRepository $OpportunityRepositoryObj
     * @var PermissionRoleRepository $PermissionRoleRepositoryObj
     * @var PropertyDetailRepository $PropertyDetailRepositoryObj
     * @var PropertyGroupCalcStatusRepository $PropertyGroupCalcStatusRepositoryObj
     * @var PropertyGroupPropertyRepository $PropertyGroupPropertyRepositoryObj
     * @var PropertyGroupRepository $PropertyGroupRepositoryObj
     * @var PropertyLeaseRollupRepository $PropertyLeaseRollupRepositoryObj
     * @var PropertyNativeCoaRepository $PropertyNativeCoaRepositoryObj
     * @var PropertyRepository $PropertyRepositoryObj
     * @var PropertySummaryRepository $PropertySummaryRepositoryObj
     * @var RelatedUserRepository $RelatedUserRepositoryObj
     * @var RelatedUserTypeRepository $RelatedUserTypeRepositoryObj
     * @var ReportTemplateAccountGroupRepository $ReportTemplateAccountGroupRepositoryObj
     * @var ReportTemplateMappingRepository $ReportTemplateMappingRepositoryObj
     * @var ReportTemplateRepository $ReportTemplateRepositoryObj
     * @var RoleRepository $RoleRepositoryObj
     * @var RoleUserRepository $RoleUserRepositoryObj
     * @var SuiteDetailRepository $SuiteDetailRepositoryObj
     * @var SuiteLeaseRepository $SuiteLeaseRepositoryObj
     * @var SuiteRepository $SuiteRepositoryObj
     * @var SuiteTenantRepository $SuiteTenantRepositoryObj
     * @var TenantAttributeRepository $TenantAttributeRepositoryObj
     * @var TenantIndustryRepository $TenantIndustryRepositoryObj
     * @var TenantRepository $TenantRepositoryObj
     * @var TenantTenantAttributeRepository $TenantTenantAttributeRepositoryObj
     * @var UserAdminRepository $UserAdminRepositoryObj
     * @var UserAdminRepository $UserAdminRepositoryObj
     * @var UserDetailRepository $UserDetailRepositoryObj
     * @var UserInvitationRepository $UserInvitationRepositoryObj
     * @var UserRepository $UserRepositoryObj
     * @var UserSummaryRepository $UserSummaryRepositoryObj
     */

    protected $AccessListDetailRepositoryObj;
    protected $AccessListFullRepositoryObj;
    protected $AccessListPropertyRepositoryObj;
    protected $AccessListRepositoryObj;
    protected $AccessListSlimRepositoryObj;
    protected $AccessListSummaryRepositoryObj;
    protected $AccessListTrimmedSummaryRepositoryObj;
    protected $AccessListUserRepositoryObj;
    protected $AdvancedVarianceApprovalRepositoryObj;
    protected $AdvancedVarianceDetailRepositoryObj;
    protected $AdvancedVarianceExplanationTypeRepositoryObj;
    protected $AdvancedVarianceLineItemRepositoryObj;
    protected $AdvancedVarianceLineItemWorkflowRepositoryObj;
    protected $AdvancedVarianceRepositoryObj;
    protected $AdvancedVarianceThresholdRepositoryObj;
    protected $ApiKeyRepositoryObj;
    protected $ApiLogRepositoryObj;
    protected $AssetTypeRepositoryObj;
    protected $AuthenticatingEntityRepositoryObj;
    protected $CalculatedFieldEquationRepositoryObj;
    protected $CalculatedFieldRepositoryObj;
    protected $CalculateVariousPropertyListsRepositoryObj;
    protected $ClientCategoryRepositoryObj;
    protected $ClientDetailRepositoryObj;
    protected $ClientFullRepositoryObj;
    protected $ClientRepositoryObj;
    protected $CommentDetailRepositoryObj;
    protected $CustomReportRepositoryObj;
    protected $CustomReportTypeRepositoryObj;
    protected $DownloadHistoryRepositoryObj;
    protected $EcmProjectRepositoryObj;
    protected $FailedJobRepositoryObj;
    protected $FavoriteRepositoryObj;
    protected $HeartbeatDetailRepositoryObj;
    protected $HeartbeatRepositoryObj;
    protected $LeaseRepositoryObj;
    protected $LeaseScheduleRepositoryObj;
    protected $LeaseTenantRepositoryObj;
    protected $NativeAccountAmountRepositoryObj;
    protected $NativeAccountRepositoryObj;
    protected $NativeAccountTypeRepositoryObj;
    protected $NativeAccountTypeTrailerRepositoryObj;
    protected $NativeCoaFullRepositoryObj;
    protected $NativeCoaRepositoryObj;
    protected $NotificationLogRepositoryObj;
    protected $OpportunityRepositoryObj;
    protected $PermissionRepositoryObj;
    protected $PermissionRoleRepositoryObj;
    protected $PropertyDetailRepositoryObj;
    protected $PropertyGroupCalcStatusRepositoryObj;
    protected $PropertyGroupPropertyRepositoryObj;
    protected $PropertyGroupRepositoryObj;
    protected $PropertyLeaseRollupRepositoryObjObj;
    protected $PropertyNativeCoaRepositoryObj;
    protected $PropertyRepositoryObj;
    protected $PropertySummaryRepositoryObj;
    protected $RelatedUserRepositoryObj;
    protected $RelatedUserTypeRepositoryObj;
    protected $ReportTemplateAccountGroupRepositoryObj;
    protected $ReportTemplateMappingRepositoryObj;
    protected $ReportTemplateRepositoryObj;
    protected $RoleRepositoryObj;
    protected $RoleUserRepositoryObj;
    protected $SuiteDetailRepositoryObj;
    protected $SuiteLeaseRepositoryObj;
    protected $SuiteRepositoryObj;
    protected $SuiteTenantRepositoryObj;
    protected $TenantAttributeRepositoryObj;
    protected $TenantIndustryRepositoryObj;
    protected $TenantRepositoryObj;
    protected $TenantTenantAttributeRepositoryObj;
    protected $UserAdminRepositoryObj;
    protected $UserDetailRepositoryObj;
    protected $UserInvitationRepositoryObj;
    protected $UserRepositoryObj;
    protected $UserSummaryRepositoryObj;

    /**
     * @param bool $suppress_events
     */
    protected function loadAllRepositories($suppress_events = false)
    {
        $this->AccessListDetailRepositoryObj                = App::make(AccessListDetailRepository::class)->setSuppressEvents($suppress_events);
        $this->AccessListFullRepositoryObj                  = App::make(AccessListFullRepository::class)->setSuppressEvents($suppress_events);
        $this->AccessListPropertyRepositoryObj              = App::make(AccessListPropertyRepository::class)->setSuppressEvents($suppress_events);
        $this->AccessListRepositoryObj                      = App::make(AccessListRepository::class)->setSuppressEvents($suppress_events);
        $this->AccessListSlimRepositoryObj                  = App::make(AccessListSlimRepository::class)->setSuppressEvents($suppress_events);
        $this->AccessListSummaryRepositoryObj               = App::make(AccessListSummaryRepository::class)->setSuppressEvents($suppress_events);
        $this->AccessListTrimmedSummaryRepositoryObj        = App::make(AccessListTrimmedSummaryRepository::class)->setSuppressEvents($suppress_events);
        $this->AccessListUserRepositoryObj                  = App::make(AccessListUserRepository::class)->setSuppressEvents($suppress_events);
        $this->AdvancedVarianceApprovalRepositoryObj        = App::make(AdvancedVarianceApprovalRepository::class)->setSuppressEvents($suppress_events);
        $this->AdvancedVarianceDetailRepositoryObj          = App::make(AdvancedVarianceDetailRepository::class)->setSuppressEvents($suppress_events);
        $this->AdvancedVarianceExplanationTypeRepositoryObj = App::make(AdvancedVarianceExplanationTypeRepository::class)->setSuppressEvents($suppress_events);
        $this->AdvancedVarianceLineItemRepositoryObj        = App::make(AdvancedVarianceLineItemRepository::class)->setSuppressEvents($suppress_events);
        $this->AdvancedVarianceLineItemWorkflowRepositoryObj                     = App::make(AdvancedVarianceLineItemWorkflowRepository::class)->setSuppressEvents($suppress_events);
        $this->AdvancedVarianceRepositoryObj                = App::make(AdvancedVarianceRepository::class)->setSuppressEvents($suppress_events);
        $this->AdvancedVarianceThresholdRepositoryObj       = App::make(AdvancedVarianceThresholdRepository::class)->setSuppressEvents($suppress_events);
        $this->ApiKeyRepositoryObj                          = App::make(ApiKeyRepository::class)->setSuppressEvents($suppress_events);
        $this->ApiLogRepositoryObj                          = App::make(ApiLogRepository::class)->setSuppressEvents($suppress_events);
        $this->AssetTypeRepositoryObj                       = App::make(AssetTypeRepository::class)->setSuppressEvents($suppress_events);
        $this->AuthenticatingEntityRepositoryObj            = App::make(AuthenticatingEntityRepository::class)->setSuppressEvents($suppress_events);
        $this->CalculatedFieldEquationRepositoryObj         = App::make(CalculatedFieldEquationRepository::class)->setSuppressEvents($suppress_events);
        $this->CalculatedFieldRepositoryObj                 = App::make(CalculatedFieldRepository::class)->setSuppressEvents($suppress_events);
        $this->CalculateVariousPropertyListsRepositoryObj   = App::make(CalculateVariousPropertyListsRepository::class)->setSuppressEvents($suppress_events);
        $this->ClientCategoryRepositoryObj                  = App::make(ClientCategoryRepository::class)->setSuppressEvents($suppress_events);
        $this->ClientDetailRepositoryObj                    = App::make(ClientDetailRepository::class)->setSuppressEvents($suppress_events);
        $this->ClientFullRepositoryObj                      = App::make(ClientFullRepository::class)->setSuppressEvents($suppress_events);
        $this->ClientRepositoryObj                          = App::make(ClientRepository::class)->setSuppressEvents($suppress_events);
        $this->CommentDetailRepositoryObj                   = App::make(CommentDetailRepository::class)->setSuppressEvents($suppress_events);
        $this->CustomReportRepositoryObj                    = App::make(CustomReportRepository::class)->setSuppressEvents($suppress_events);
        $this->CustomReportTypeRepositoryObj                = App::make(CustomReportTypeRepository::class)->setSuppressEvents($suppress_events);
        $this->DownloadHistoryRepository                    = App::make(DownloadHistoryRepository::class)->setSuppressEvents($suppress_events);
        $this->DownloadHistoryRepositoryObj                 = App::make(DownloadHistoryRepository::class)->setSuppressEvents($suppress_events);
        $this->EcmProjectRepositoryObj                      = App::make(EcmProjectRepository::class)->setSuppressEvents($suppress_events);
        $this->FailedJobRepositoryObj                       = App::make(FailedJobRepository::class)->setSuppressEvents($suppress_events);
        $this->FavoriteRepositoryObj                        = App::make(FavoriteRepository::class)->setSuppressEvents($suppress_events);
        $this->HeartbeatDetailRepositoryObj                 = App::make(HeartbeatDetailRepository::class)->setSuppressEvents($suppress_events);
        $this->HeartbeatRepositoryObj                       = App::make(HeartbeatRepository::class)->setSuppressEvents($suppress_events);
        $this->LeaseDetailRepositoryObj                     = App::make(LeaseDetailRepository::class)->setSuppressEvents($suppress_events);
        $this->LeaseRepositoryObj                           = App::make(LeaseRepository::class)->setSuppressEvents($suppress_events);
        $this->LeaseScheduleRepositoryObj                   = App::make(LeaseScheduleRepository::class)->setSuppressEvents($suppress_events);
        $this->LeaseTenantRepositoryObj                     = App::make(LeaseTenantRepository::class)->setSuppressEvents($suppress_events);
        $this->NativeAccountAmountRepositoryObj             = App::make(NativeAccountAmountRepository::class)->setSuppressEvents($suppress_events);
        $this->NativeAccountRepositoryObj                   = App::make(NativeAccountRepository::class)->setSuppressEvents($suppress_events);
        $this->NativeAccountTypeRepositoryObj               = App::make(NativeAccountTypeRepository::class)->setSuppressEvents($suppress_events);
        $this->NativeAccountTypeTrailerRepositoryObj        = App::make(NativeAccountTypeTrailerRepository::class);
        $this->NativeCoaFullRepositoryObj                   = App::make(NativeCoaFullRepository::class)->setSuppressEvents($suppress_events);
        $this->NativeCoaRepositoryObj                       = App::make(NativeCoaRepository::class)->setSuppressEvents($suppress_events);
        $this->NotificationLogRepositoryObj                 = App::make(NotificationLogRepository::class)->setSuppressEvents($suppress_events);
        $this->OpportunityRepositoryObj                     = App::make(OpportunityRepository::class)->setSuppressEvents($suppress_events);
        $this->PasswordRuleRepositoryObj                    = App::make(PasswordRuleRepository::class)->setSuppressEvents($suppress_events);
        $this->PermissionRepositoryObj                      = App::make(PermissionRepository::class)->setSuppressEvents($suppress_events);
        $this->PermissionRoleRepositoryObj                  = App::make(PermissionRoleRepository::class)->setSuppressEvents($suppress_events);
        $this->PropertyDetailRepositoryObj                  = App::make(PropertyDetailRepository::class)->setSuppressEvents($suppress_events);
        $this->PropertyGroupCalcStatusRepositoryObj         = App::make(PropertyGroupCalcStatusRepository::class)->setSuppressEvents($suppress_events);
        $this->PropertyGroupPropertyRepositoryObj           = App::make(PropertyGroupPropertyRepository::class)->setSuppressEvents($suppress_events);
        $this->PropertyGroupRepositoryObj                   = App::make(PropertyGroupRepository::class)->setSuppressEvents($suppress_events);
        $this->PropertyLeaseRollupRepositoryObj             = App::make(PropertyLeaseRollupRepository::class)->setSuppressEvents($suppress_events);
        $this->PropertyNativeCoaRepositoryObj               = App::make(PropertyNativeCoaRepository::class)->setSuppressEvents($suppress_events);
        $this->PropertyRepositoryObj                        = App::make(PropertyRepository::class)->setSuppressEvents($suppress_events);
        $this->PropertySummaryRepositoryObj                 = App::make(PropertySummaryRepository::class)->setSuppressEvents($suppress_events);
        $this->RelatedUserRepositoryObj                     = App::make(RelatedUserRepository::class)->setSuppressEvents($suppress_events);
        $this->RelatedUserTypeRepositoryObj                 = App::make(RelatedUserTypeRepository::class)->setSuppressEvents($suppress_events);
        $this->ReportTemplateAccountGroupRepositoryObj      = App::make(ReportTemplateAccountGroupRepository::class)->setSuppressEvents($suppress_events);
        $this->ReportTemplateMappingRepositoryObj           = App::make(ReportTemplateMappingRepository::class)->setSuppressEvents($suppress_events);
        $this->ReportTemplateRepositoryObj                  = App::make(ReportTemplateRepository::class)->setSuppressEvents($suppress_events);
        $this->RoleRepositoryObj                            = App::make(RoleRepository::class)->setSuppressEvents($suppress_events);
        $this->RoleUserRepositoryObj                        = App::make(RoleUserRepository::class)->setSuppressEvents($suppress_events);
        $this->SuiteDetailRepositoryObj                     = App::make(SuiteDetailRepository::class)->setSuppressEvents($suppress_events);
        $this->SuiteLeaseRepositoryObj                      = App::make(SuiteLeaseRepository::class)->setSuppressEvents($suppress_events);
        $this->SuiteRepositoryObj                           = App::make(SuiteRepository::class)->setSuppressEvents($suppress_events);
        $this->SuiteTenantRepositoryObj                     = App::make(SuiteTenantRepository::class)->setSuppressEvents($suppress_events);
        $this->TenantAttributeRepositoryObj                 = App::make(TenantAttributeRepository::class)->setSuppressEvents($suppress_events);
        $this->TenantIndustryRepositoryObj                  = App::make(TenantIndustryRepository::class)->setSuppressEvents($suppress_events);
        $this->TenantRepositoryObj                          = App::make(TenantRepository::class)->setSuppressEvents($suppress_events);
        $this->TenantTenantAttributeRepositoryObj           = App::make(TenantTenantAttributeRepository::class)->setSuppressEvents($suppress_events);
        $this->UserAdminRepositoryObj                       = App::make(UserAdminRepository::class)->setSuppressEvents($suppress_events);
        $this->UserDetailRepositoryObj                      = App::make(UserDetailRepository::class)->setSuppressEvents($suppress_events);
        $this->UserInvitationRepositoryObj                  = App::make(UserInvitationRepository::class)->setSuppressEvents($suppress_events);
        $this->UserRepositoryObj                            = App::make(UserRepository::class)->setSuppressEvents($suppress_events);
        $this->UserSummaryRepositoryObj                     = App::make(UserSummaryRepository::class)->setSuppressEvents($suppress_events);
    }

    protected function unsetAllRepositories()
    {
        unset($this->AccessListDetailRepositoryObj);
        unset($this->AccessListFullRepositoryObj);
        unset($this->AccessListPropertyRepositoryObj);
        unset($this->AccessListRepositoryObj);
        unset($this->AccessListSlimRepositoryObj);
        unset($this->AccessListSummaryRepositoryObj);
        unset($this->AccessListUserRepositoryObj);
        unset($this->AdvancedVarianceApprovalRepositoryObj);
        unset($this->AdvancedVarianceDetailRepositoryObj);
        unset($this->AdvancedVarianceExplanationTypeRepositoryObj);
        unset($this->AdvancedVarianceLineItemRepositoryObj);
        unset($this->AdvancedVarianceLineItemWorkflowRepositoryObj);
        unset($this->AdvancedVarianceRepositoryObj);
        unset($this->ApiKeyRepositoryObj);
        unset($this->ApiLogRepositoryObj);
        unset($this->AssetTypeRepositoryObj);
        unset($this->AuthenticatingEntityRepositoryObj);
        unset($this->CalculatedFieldEquationRepositoryObj);
        unset($this->CalculatedFieldEquationRepositoryObj);
        unset($this->CalculatedFieldRepositoryObj);
        unset($this->CalculateVariousPropertyListsRepositoryObj);
        unset($this->CapexJobCodeCategoryRepositoryObj);
        unset($this->CapexJobCodePhaseRepositoryObj);
        unset($this->CapexJobCodeRepositoryObj);
        unset($this->CapexJobCodeScheduleRepositoryObj);
        unset($this->CapexJobCodeStatusRepositoryObj);
        unset($this->ClientCategoryRepositoryObj);
        unset($this->ClientDetailRepositoryObj);
        unset($this->ClientFullRepositoryObj);
        unset($this->ClientRepositoryObj);
        unset($this->CommentDetailRepositoryObj);
        unset($this->CustomReportRepositoryObj);
        unset($this->CustomReportTypeRepositoryObj);
        unset($this->DownloadHistoryRepositoryObj);
        unset($this->EcmProjectRepositoryObj);
        unset($this->FailedJobRepositoryObj);
        unset($this->FavoriteRepositoryObj);
        unset($this->HeartbeatDetailRepositoryObj);
        unset($this->HeartbeatRepositoryObj);
        unset($this->LeaseDetailRepositoryObj);
        unset($this->LeaseRepositoryObj);
        unset($this->LeaseScheduleRepositoryObj);
        unset($this->LeaseTenantRepositoryObj);
        unset($this->NativeAccountAmountRepositoryObj);
        unset($this->NativeAccountRepositoryObj);
        unset($this->NativeAccountTypeTrailerRepositoryObj);
        unset($this->NativeCoaFullRepositoryObj);
        unset($this->NativeCoaRepositoryObj);
        unset($this->NotificationLogRepositoryObj);
        unset($this->OpportunityRepositoryObj);
        unset($this->PasswordRuleRepositoryObj);
        unset($this->PermissionRepositoryObj);
        unset($this->PermissionRoleRepositoryObj);
        unset($this->PropertyDetailRepositoryObj);
        unset($this->PropertyGroupCalcStatusRepositoryObj);
        unset($this->PropertyGroupPropertyRepositoryObj);
        unset($this->PropertyGroupRepositoryObj);
        unset($this->PropertyLeaseRollupRepositoryObj);
        unset($this->PropertyNativeCoaRepositoryObj);
        unset($this->PropertyRepositoryObj);
        unset($this->PropertySummaryRepositoryObj);
        unset($this->RelatedUserRepositoryObj);
        unset($this->RelatedUserTypeRepositoryObj);
        unset($this->ReportTemplateAccountGroupRepositoryObj);
        unset($this->ReportTemplateMappingRepositoryObj);
        unset($this->ReportTemplateRepositoryObj);
        unset($this->RoleRepositoryObj);
        unset($this->RoleUserRepositoryObj);
        unset($this->SuiteDetailRepositoryObj);
        unset($this->SuiteLeaseRepositoryObj);
        unset($this->SuiteRepositoryObj);
        unset($this->SuiteTenantRepositoryObj);
        unset($this->TenantAttributeRepositoryObj);
        unset($this->TenantIndustryRepositoryObj);
        unset($this->TenantRepositoryObj);
        unset($this->TenantTenantAttributeRepositoryObj);
        unset($this->UserAdminRepositoryObj);
        unset($this->UserDetailRepositoryObj);
        unset($this->UserInvitationRepositoryObj);
        unset($this->UserRepositoryObj);
        unset($this->UserSummaryRepositoryObj);
    }
}