<?php

namespace App\Waypoint\Notifications;

use App\Waypoint\Collection;
use App\Waypoint\Models\User;
use Exception;
use function collect_waypoint;
use Illuminate\Notifications\Notification as NotificationBase;
use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Repositories\UserRepository;

class Notification extends NotificationBase
{
    /** @var App\Waypoint\Collection */
    public $RecipientUserObjArr;

    /*
     * do not implement a base constructor as it ties our hands re: method signatures
     * of other Notification
     */
    //public function __construct(Model $InvitedUserObj, Collection $RecipientUserObjArr=null){}

    const SUPPORT_EMAIL_ADDRESS = 'support@waypointbuilding.com';

    /**
     * @return App\Waypoint\Collection
     */
    public function getRecipientUserObjArr(): App\Waypoint\Collection
    {
        return $this->RecipientUserObjArr;
    }

    /**
     * @param mixed $RecipientUserObjArr
     */
    public function setRecipientUserObjArr($RecipientUserObjArr)
    {
        $this->RecipientUserObjArr = $RecipientUserObjArr;

        $this->RecipientUserObjArr = $this->RecipientUserObjArr->unique(
            function (User $UserObj)
            {
                return $UserObj->id;
            }
        );
    }

    /**
     * @return string
     */
    public function getBaseNotificationUrl()
    {
        return config('waypoint.notifications_base_url', 'https://app.waypointbuilding.com/');
    }

    /**
     * @param Collection $NotifiableUserObjArr
     * @param $NotificationObj
     * @throws GeneralException
     */
    public static function send($NotifiableUserObjArr, $NotificationObj)
    {
        try
        {
            if (App::environment() !== 'production')
            {
                /**
                 * @todo HER-2436
                 *
                 * under no circumstances will this method send emails to addresses that are not
                 * ^.*\@waypointbuilding\.com or ^.*\@unit_test_email_domain as defined in .env
                 */
                $NotifiableUserObjArr = $NotifiableUserObjArr->filter(
                    function (User $NotifiableUserObj)
                    {
                        if (
                            preg_match('/waypointbuilding\.com$/i', $NotifiableUserObj->email)
                            ||
                            preg_match('/' . preg_quote(config('waypoint.unit_test_email_domain', 'waypointbuilding.com')) . '$/i', $NotifiableUserObj->email)
                        )
                        {
                            return true;
                        }
                        return false;
                    }
                );
            }

            /**
             * OK, JUST IN CASE, let's check the user and client config's
             * Maybe we messed up in the controller or maybe this $notification
             * was generated via migration, artisan or CLI call
             *
             * Also, let's de-dup
             *
             * Note we use Lavarel Collection rather that waypoints since
             * Notification::send() needs it. Sigh
             */
            $FilteredNotifiableUserObjArr = [];
            /** @var User $UserObj */
            foreach ($NotifiableUserObjArr as $UserObj)
            {
                // send all notifications to the nominated address -- this respects NO user/client filters
                // @todo HER-2436 ALEX -- create better solution for adjusting email recipients for testing purposes
                if (
                    config('waypoint.send_all_notifications_to_this_address', false) &&
                    filter_var(config('waypoint.send_all_notifications_to_this_address'), FILTER_VALIDATE_EMAIL)
                )
                {
                    $UserObj->email = config('waypoint.send_all_notifications_to_this_address');

                    if ( ! config('waypoint.respect_notifications_config_settings', true))
                    {
                        $FilteredNotifiableUserObjArr[$UserObj->id] = $UserObj;
                        continue;
                    }
                }

                /**
                 * unless this is a notification that is related to provisioning,
                 * users that have not 'accepted' an invitation cannot get a notification
                 */
                if ( ! in_array(
                    get_class($NotificationObj),
                    [
                        ResetPasswordNotification::class,
                        InvitationNotification::class,
                        PasswordConfirmationNotification::class,
                    ]
                ))
                {
                    if (
                        ! $UserObj->active_status == User::ACTIVE_STATUS_ACTIVE ||
                        ! $UserObj->user_invitation_status == User::USER_INVITATION_STATUS_ACCEPTED
                    )
                    {
                        /**
                         * users that have not 'accepted' an invitation cannot get a notification
                         */
                        continue;
                    }
                }

                /**
                 * notifications is turned off at the client level
                 */
                if (
                    isset($UserObj->client->getConfigJSON()->NOTIFICATIONS)
                    && ! (bool) $UserObj->client->getConfigJSON()->NOTIFICATIONS
                )
                {
                    continue;
                }

                /**
                 * apply some 'per NOTIFICATION type' rules
                 */
                switch (get_class($NotificationObj))
                {
                    case AdvancedVarianceApprovedNotification::class:
                        $FilteredNotifiableUserObjArr = self::addUserToRecipientListBasedOnConfigs(
                            $UserObj,
                            $FilteredNotifiableUserObjArr,
                            User::VARIANCE_APPROVED_NOTIFICATIONS_FLAG
                        );
                        break;
                    case AdvancedVarianceLineItemCommentNotification::class:
                        $FilteredNotifiableUserObjArr = self::addUserToRecipientListBasedOnConfigs(
                            $UserObj,
                            $FilteredNotifiableUserObjArr,
                            User::VARIANCE_COMMENTED_NOTIFICATIONS_FLAG
                        );
                        break;
                    case AdvancedVarianceLineItemExplanationNotification::class:
                        $FilteredNotifiableUserObjArr = self::addUserToRecipientListBasedOnConfigs(
                            $UserObj,
                            $FilteredNotifiableUserObjArr,
                            User::VARIANCE_EXPLAINED_NOTIFICATIONS_FLAG
                        );
                        break;
                    case AdvancedVarianceLineItemFlaggedNotification::class:
                        $FilteredNotifiableUserObjArr = self::addUserToRecipientListBasedOnConfigs(
                            $UserObj,
                            $FilteredNotifiableUserObjArr,
                            User::VARIANCE_MARKED_NOTIFICATIONS_FLAG
                        );
                        break;
                    case AdvancedVarianceLineItemResolvedNotification::class:
                        $FilteredNotifiableUserObjArr = self::addUserToRecipientListBasedOnConfigs(
                            $UserObj,
                            $FilteredNotifiableUserObjArr,
                            User::VARIANCE_RESOLVED_NOTIFICATIONS_FLAG
                        );
                        break;

                    case AdvancedVarianceLockedNotification::class:
                        $FilteredNotifiableUserObjArr = self::addUserToRecipientListBasedOnConfigs(
                            $UserObj,
                            $FilteredNotifiableUserObjArr,
                            User::VARIANCE_LOCKED_NOTIFICATIONS_FLAG
                        );
                        break;
                    case ResetPasswordNotification::class:
                    case InvitationNotification::class:
                    case PasswordConfirmationNotification::class:
                        if ($NotifiableUserObjArr->count() !== 1)
                        {
                            throw new GeneralException('InvitationNotification failure');
                        }
                        $FilteredNotifiableUserObjArr[$UserObj->id] = $UserObj;
                        break;
                    case OpportunityOpenedNotification::class:
                        $FilteredNotifiableUserObjArr = self::addUserToRecipientListBasedOnConfigs(
                            $UserObj,
                            $FilteredNotifiableUserObjArr,
                            User::OPPORTUNITIES_CREATED_NOTIFICATIONS_FLAG
                        );
                        break;
                    case OpportunityUpdatedNotification::class:
                    case OpportunityAttachedNotification::class:
                    case OpportunityDetachedNotification::class:
                        $FilteredNotifiableUserObjArr = self::addUserToRecipientListBasedOnConfigs(
                            $UserObj,
                            $FilteredNotifiableUserObjArr,
                            User::OPPORTUNITIES_UPDATED_NOTIFICATIONS_FLAG
                        );
                        break;
                    case OpportunityCommentedNotification::class:
                        $FilteredNotifiableUserObjArr = self::addUserToRecipientListBasedOnConfigs(
                            $UserObj,
                            $FilteredNotifiableUserObjArr,
                            User::OPPORTUNITIES_COMMENTED_NOTIFICATIONS_FLAG
                        );
                        break;
                    default:
                        throw new GeneralException('Unknown Notification class at ' . __FILE__ . ':' . __LINE__);
                }
            }
            if ( ! count($FilteredNotifiableUserObjArr))
            {
                /**
                 * wow that was pointless
                 */
                return;
            }

            /**
             * note that this is a collection cause that's what
             * \App\Waypoint\Notifications\Facades\Notification::send() needs
             */
            $FilteredNotifiableUserObjArr = collect_waypoint(array_values($FilteredNotifiableUserObjArr));

            if (config('waypoint.enable_notifications_emails', false))
            {
                \App\Waypoint\Notifications\Facades\Notification::send($FilteredNotifiableUserObjArr, $NotificationObj);
            }
        }
        catch (GeneralException $e)
        {
            throw  $e;
        }
        catch (Exception $e)
        {
            throw new GeneralException(__CLASS__ . 'Notification::send() failed', 404, $e);
        }
    }

    public function format_list($list_to_format)
    {
        $last  = array_slice($list_to_format, -1);
        $first = join(', ', array_slice($list_to_format, 0, -1));
        $both  = array_filter(array_merge([$first], $last), 'strlen');
        echo join(' and ', $both);
    }

    /**
     * @param User $UserObj
     * @return string
     */
    protected function formatUserNameForEmail(User $UserObj): string
    {
        return '<span style="background-color: #909091; -webkit-border-radius: 3px 3px 3px 3px; border-radius: 3px 3px 3px 3px; color: white; padding: 1px 5px; white-space: nowrap;">' . $UserObj->firstname . ' ' . $UserObj->lastname . '</span>';
    }

    /**
     * @param string $comment_text
     * @return string
     */
    protected function replaceUserIdsWithNames(string $comment_text): string
    {
        return preg_replace_callback(
            '(\[~\d+\])',
            function ($matched_user_placeholder_array)
            {
                $UserRepoObj = App::make(UserRepository::class);
                if (preg_match('(\d+)', current($matched_user_placeholder_array), $matched_user_id_array))
                {
                    $UserObj = $UserRepoObj->find((int) current($matched_user_id_array));
                    return $this->formatUserNameForEmail($UserObj);
                }
                throw new GeneralException('could not extract user id from placeholder text');
            },
            $comment_text
        );
    }

    /**
     * @param User $UserObj
     * @param array $FilteredRecipientUserObjArr
     * @param string $notification_name
     * @return array
     *
     * Adds a user to the filtered list if the client/user has the approapriate config settings
     */
    protected static function addUserToRecipientListBasedOnConfigs(
        User $UserObj,
        array $FilteredRecipientUserObjArr,
        string $notification_name
    ): array {
        /**
         * overridden and enabled at client level
         */
        if (
            self::overriddenAtClientLevel($UserObj, $notification_name)
            && isset($UserObj->client->getConfigJSON()->{$notification_name})
            && (bool) $UserObj->client->getConfigJSON()->{$notification_name}
        )
        {
            $FilteredRecipientUserObjArr[$UserObj->id] = $UserObj;
        }

        /**
         * enabled at user level
         */
        if (
            isset($UserObj->getConfigJSON()->{$notification_name})
            && (bool) $UserObj->getConfigJSON()->{$notification_name}
        )
        {
            $FilteredRecipientUserObjArr[$UserObj->id] = $UserObj;
        }
        return $FilteredRecipientUserObjArr;
    }

    /**
     * @param User $UserObj
     * @param $notification_type
     * @return bool
     *
     * Is client override set for the particular notification type
     */
    protected static function overriddenAtClientLevel(User $UserObj, $notification_type): bool
    {
        return (
            isset($UserObj->client->getConfigJSON()->{$notification_type . '_ADMIN_OVERRIDE'})
            && (bool) $UserObj->client->getConfigJSON()->{$notification_type . '_ADMIN_OVERRIDE'}
        );
    }
}
