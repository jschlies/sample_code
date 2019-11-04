<?php

namespace App\Waypoint\Console\Commands;

use App;
use App\Waypoint\Command;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\User;
use DB;
use Exception;
use function preg_match;
use function strlen;

/**
 * Class AddUsersCommand
 * @package App\Console\Commands
 *
 * See https://laravel.com/docs/5.1/artisan
 */
class DeactivateUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:deactivate_user  
                        {--client_id=null : Mark user(s) as inactive for client } 
                        {--email=0 : email} 
                        {--email_regex=0 : /email\@email.com/  - and wrap in quotes for better results} 
                        {--delete_from_auth0 : Delete users with \'email\' or match \'email_regex\' if there are no longer users recs matching \'email\' or match \'email_regex\'  } 
                        {--dry_run : Does nothing but list users that would have been marked as inactive  } 
                        {--purge : Delete user record if possible   }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a user';

    /**
     * AddUsersCommand constructor.
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
     * @todo push this logic into a repository
     */
    public function handle()
    {
        parent::handle();

        $client_id         = $this->option('client_id');
        $email             = $this->option('email');
        $email_regex       = $this->option('email_regex');
        $delete_from_auth0 = $this->option('delete_from_auth0');
        $dry_run           = $this->option('dry_run');
        $purge             = $this->option('purge');

        if ($email && $email_regex)
        {
            throw new GeneralException("cannot pass email and email_regex", 400);
        }
        if ( ! $email && ! $email_regex)
        {
            throw new GeneralException("must pass email or email_regex", 400);
        }
        if ($email_regex && $email_regex[0] !== $email_regex[strlen($email_regex) - 1])
        {
            throw new GeneralException("email_regex must include delimiters", 400);
        }
        if ($email_regex && preg_match($email_regex, null) === false)
        {
            throw new GeneralException("invalid email_regex", 400);
        }

        $this->deactivateUsersByClientEmailOrRegex($client_id, $email, $email_regex, $delete_from_auth0, $dry_run, $purge);

        return true;
    }

    /**
     * @param integer $client_id
     * @param string $email
     * @param string $email_regex
     * @param $delete_from_auth0
     * @param $dry_run
     * @throws GeneralException
     */
    public function deactivateUsersByClientEmailOrRegex($client_id, $email = null, $email_regex = null, $delete_from_auth0 = false, $dry_run = true, $purge = false)
    {
        /** @var User[] $UsersToDeactivateObjArr */
        $UsersToDeactivateObjArr = $this->UserRepositoryObj->getUsersByClientEmailOrRegex(
            $client_id,
            $email,
            $email_regex
        )->filter(
            function ($UserObj)
            {
                return $UserObj->active_status = User::ACTIVE_STATUS_ACTIVE;
            }
        );

        if ( ! count($UsersToDeactivateObjArr))
        {
            $this->alert('No users found with $email = ' . $email . ' $email_regex = ' . $email_regex);
            return;
        }

        if (count($UsersToDeactivateObjArr))
        {
            if ($purge)
            {
                try
                {
                    /** @var User $UserToDeactivateObj */
                    foreach ($UsersToDeactivateObjArr as $UserToDeactivateObj)
                    {
                        $this->alert('-----------------------------------------------');
                        if ($UserToDeactivateObj->active_status !== User::ACTIVE_STATUS_ACTIVE)
                        {
                            if ($dry_run)
                            {
                                $this->alert('(Dry run) User marked inactive ' . $UserToDeactivateObj->firstname . ' ' . $UserToDeactivateObj->lastname . ' (' . $UserToDeactivateObj->email . ')');
                            }
                            else
                            {
                                $this->UserRepositoryObj->deactivateUsers([$UserToDeactivateObj]);
                                $this->alert('User marked inactive ' . $UserToDeactivateObj->firstname . ' ' . $UserToDeactivateObj->lastname . ' (' . $UserToDeactivateObj->email . ')');
                            }
                        }
                        else
                        {
                            $this->alert('Warning User is already inactive ' . $UserToDeactivateObj->firstname . ' ' . $UserToDeactivateObj->lastname . ' (' . $UserToDeactivateObj->email . ')');
                        }

                        if (
                            $UserToDeactivateObj->accessListUsers->count() > 0
                        )
                        {
                            if ($dry_run)
                            {
                                $this->alert('(Dry run) Deleting accessListUsers for user ' . $UserToDeactivateObj->email);
                            }
                            else
                            {
                                $this->alert('Deleting accessListUsers for user ' . $UserToDeactivateObj->email);
                                foreach ($UserToDeactivateObj->accessListUsers as $AccessListUserObj)
                                {
                                    $this->AccessListUserRepositoryObj->delete($AccessListUserObj->id);
                                }
                            }
                        }
                        if (
                            $UserToDeactivateObj->advancedVarianceApprovals->count() > 0
                        )
                        {
                            if ($dry_run)
                            {
                                $this->alert('(Dry run) ERROR Purging user ' . $UserToDeactivateObj->email . ' not allowed. User has relationships to other records advancedVarianceApprovals');
                            }
                            else
                            {
                                throw new GeneralException('Purging user ' . $UserToDeactivateObj->email . ' not allowed. User has relationships to other records advancedVarianceApprovals');
                            }
                        }
                        if (
                            $UserToDeactivateObj->advancedVarianceLineItemsAsExplanationTypeUser->count() > 0
                        )
                        {
                            if ($dry_run)
                            {
                                $this->alert('(Dry run) ERROR Purging user ' . $UserToDeactivateObj->email . ' not allowed. User has relationships to other records advancedVarianceLineItemsAsExplanationTypeUser');
                            }
                            else
                            {
                                throw new GeneralException('Purging user ' . $UserToDeactivateObj->email . ' not allowed. User has relationships to other records advancedVarianceLineItemsAsExplanationTypeUser');
                            }
                        }
                        if (
                            $UserToDeactivateObj->advancedVarianceLineItemsAsExplainer->count() > 0
                        )
                        {
                            if ($dry_run)
                            {
                                $this->alert('(Dry run) ERROR Purging user ' . $UserToDeactivateObj->email . ' not allowed. User has relationships to other records advancedVarianceLineItemsAsExplainer');
                            }
                            else
                            {
                                throw new GeneralException('Purging user ' . $UserToDeactivateObj->email . ' not allowed. User has relationships to other records advancedVarianceLineItemsAsExplainer');
                            }
                        }
                        if (
                            $UserToDeactivateObj->advancedVarianceLineItemsAsFlagger->count() > 0
                        )
                        {
                            if ($dry_run)
                            {
                                $this->alert('(Dry run) ERROR Purging user ' . $UserToDeactivateObj->email . ' not allowed. User has relationships to other records advancedVarianceLineItemsAsFlagger');
                            }
                            else
                            {
                                throw new GeneralException('Purging user ' . $UserToDeactivateObj->email . ' not allowed. User has relationships to other records advancedVarianceLineItemsAsFlagger');
                            }
                        }
                        if (
                            $UserToDeactivateObj->advancedVarianceLineItemsAsResolver->count() > 0
                        )
                        {
                            if ($dry_run)
                            {
                                $this->alert('(Dry run) ERROR Purging user ' . $UserToDeactivateObj->email . ' not allowed. User has relationships to other records advancedVarianceLineItemsAsResolver');
                            }
                            else
                            {
                                throw new GeneralException('Purging user ' . $UserToDeactivateObj->email . ' not allowed. User has relationships to other records advancedVarianceLineItemsAsResolver');
                            }
                        }
                        if (
                            $UserToDeactivateObj->advancedVariancesAsLocker->count() > 0
                        )
                        {
                            if ($dry_run)
                            {
                                $this->alert('(Dry run) ERROR Purging user ' . $UserToDeactivateObj->email . ' not allowed. User has relationships to other records advancedVariancesAsLocker');
                            }
                            else
                            {
                                throw new GeneralException('Purging user ' . $UserToDeactivateObj->email . ' not allowed. User has relationships to other records advancedVariancesAsLocker');
                            }
                        }
                        if (
                            $UserToDeactivateObj->apiKeys->count() > 0
                        )
                        {
                            if ($dry_run)
                            {
                                $this->alert('(Dry run) Deleting  apiKeys for user ' . $UserToDeactivateObj->email);
                            }
                            else
                            {
                                $this->alert('Deleting  apiKeys for user ' . $UserToDeactivateObj->email);
                                foreach ($UserToDeactivateObj->apiKeys as $ApiKeyObj)
                                {
                                    $this->ApiKeyRepositoryObj->delete($ApiKeyObj->id);
                                }
                            }
                        }
                        if (
                            $UserToDeactivateObj->attachmentsAsCreatedBy->count() > 0
                        )
                        {
                            if ($dry_run)
                            {
                                $this->alert('(Dry run) ERROR Purging user ' . $UserToDeactivateObj->email . ' not allowed. User has relationships to other records attachmentsAsCreatedBy');
                            }
                            else
                            {
                                throw new GeneralException('Purging user ' . $UserToDeactivateObj->email . ' not allowed. User has relationships to other records attachmentsAsCreatedBy');
                            }
                        }
                        if (
                            $UserToDeactivateObj->commentMentions->count() > 0
                        )
                        {
                            if ($dry_run)
                            {
                                $this->alert('(Dry run) ERROR Purging user ' . $UserToDeactivateObj->email . ' not allowed. User has relationships to other records commentMentions');
                            }
                            else
                            {
                                throw new GeneralException('Purging user ' . $UserToDeactivateObj->email . ' not allowed. User has relationships to other records commentMentions');
                            }
                        }
                        if (
                            $UserToDeactivateObj->downloadHistories->count() > 0
                        )
                        {
                            if ($dry_run)
                            {
                                $this->alert('(Dry run) ERROR Purging user ' . $UserToDeactivateObj->email . ' not allowed. User has relationships to other records downloadHistories');
                            }
                            else
                            {
                                throw new GeneralException('Purging user ' . $UserToDeactivateObj->email . ' not allowed. User has relationships to other records downloadHistories');
                            }
                        }
                        if (
                            $UserToDeactivateObj->opportunitiesAsAssignedTo->count() > 0
                        )
                        {
                            if ($dry_run)
                            {
                                $this->alert('(Dry run) ERROR Purging user ' . $UserToDeactivateObj->email . ' not allowed. User has relationships to other records opportunitiesAsAssignedTo');
                            }
                            else
                            {
                                throw new GeneralException('Purging user ' . $UserToDeactivateObj->email . ' not allowed. User has relationships to other records opportunitiesAsAssignedTo');
                            }
                        }
                        if (
                            $UserToDeactivateObj->opportunitiesAsCreatedBy->count() > 0
                        )
                        {
                            if ($dry_run)
                            {
                                $this->alert('(Dry run) ERROR Purging user ' . $UserToDeactivateObj->email . ' not allowed. User has relationships to other records opportunitiesAsCreatedBy');
                            }
                            else
                            {
                                throw new GeneralException('Purging user ' . $UserToDeactivateObj->email . ' not allowed. User has relationships to other records opportunitiesAsCreatedBy');
                            }
                        }
                        if (
                            $UserToDeactivateObj->propertyGroups->count() > 0
                        )
                        {
                            if ($dry_run)
                            {
                                $this->alert('(Dry run) Deleting propertyGroups for user ' . $UserToDeactivateObj->email);
                            }
                            else
                            {
                                $this->alert('Deleting propertyGroups for user ' . $UserToDeactivateObj->email);
                                foreach ($UserToDeactivateObj->propertyGroups as $PropertyGroupObj)
                                {
                                    $this->PropertyGroupRepositoryObj->delete(

                                        $PropertyGroupObj->id
                                    );
                                }
                            }

                        }
                        if (
                            $UserToDeactivateObj->relatedUsers->count() > 0
                        )
                        {
                            if ($dry_run)
                            {
                                $this->alert('(Dry run) ERROR Purging user ' . $UserToDeactivateObj->email . ' not allowed. User has relationships to other records relatedUsers');
                            }
                            else
                            {
                                throw new GeneralException('Purging user ' . $UserToDeactivateObj->email . ' not allowed. User has relationships to other records relatedUsers');
                            }
                        }
                        if (
                            $UserToDeactivateObj->createdOpportunities->count() > 0
                        )
                        {
                            if ($dry_run)
                            {
                                $this->alert('(Dry run) ERROR Purging user ' . $UserToDeactivateObj->email . ' not allowed. User has relationships to other records createdOpportunities');
                            }
                            else
                            {
                                throw new GeneralException('Purging user ' . $UserToDeactivateObj->email . ' not allowed. User has relationships to other records createdOpportunities');
                            }
                        }
                        if (
                            $UserToDeactivateObj->assignedOpportunities->count() > 0
                        )
                        {
                            if ($dry_run)
                            {
                                $this->alert('(Dry run) ERROR Purging user ' . $UserToDeactivateObj->email . ' not allowed. User has relationships to other records assignedOpportunities');
                            }
                            else
                            {
                                throw new GeneralException('Purging user ' . $UserToDeactivateObj->email . ' not allowed. User has relationships to other records assignedOpportunities');
                            }
                        }
                        if (
                            $UserToDeactivateObj->userInvitations->count() > 0
                        )
                        {
                            if ($dry_run)
                            {
                                $this->alert('(Dry run) Deleting userInvitations for user ' . $UserToDeactivateObj->email);
                            }
                            else
                            {
                                foreach ($UserToDeactivateObj->userInvitations as $UserInvitationObj)
                                {
                                    $this->alert('Deleting userInvitations for user ' . $UserToDeactivateObj->email);
                                    $this->UserInvitationRepositoryObj->delete($UserInvitationObj->id);
                                }
                            }
                        }

                        if ($dry_run)
                        {
                            $this->alert('(Dry run) Purged ' . $UserToDeactivateObj->firstname . ' ' . $UserToDeactivateObj->lastname . ' (' . $UserToDeactivateObj->email . ')');
                        }
                        else
                        {
                            DB::delete(
                                "
                                    DELETE FROM users 
                                        WHERE id = :ID                    
                                ",
                                [
                                    'ID' => $UserToDeactivateObj->id,
                                ]
                            );
                            $this->alert('Purged ' . $UserToDeactivateObj->firstname . ' ' . $UserToDeactivateObj->lastname . ' (' . $UserToDeactivateObj->email . ')');
                        }
                    }
                }
                catch (GeneralException $e)
                {
                    throw $e;
                }
                catch (Exception $e)
                {
                    throw new GeneralException('Purging user ' . $UserToDeactivateObj->email . ' not allowed ' . $e->getMessage());
                }
            }
            else
            {
                foreach ($UsersToDeactivateObjArr as $UserToDeactivateObj)
                {
                    if ($dry_run)
                    {
                        $this->alert('(Dry run) Deactivating user ' . $UserToDeactivateObj->email . ' not allowed. User has relationships to other records relatedUsers');
                    }
                    else
                    {
                        $this->UserRepositoryObj->deactivateUsers($UsersToDeactivateObjArr);
                        $this->alert('Deactivated ' . $UserToDeactivateObj->firstname . ' ' . $UserToDeactivateObj->lastname . ' (' . $UserToDeactivateObj->email . ')');
                    }
                }
            }
        }

        if ($delete_from_auth0)
        {
            foreach ($this->UserRepositoryObj->getAllUsersFromAuth0() as $Auth0UserObj)
            {
                if (
                    ($email && $email == $Auth0UserObj->email) ||
                    ($email_regex && preg_match($email_regex, $Auth0UserObj->email))
                )
                {
                    if ( ! $this->UserRepositoryObj->findWhere(
                        [
                            ['email', '=', $email,],
                            ['active_status', '=', User::ACTIVE_STATUS_ACTIVE,],
                        ]
                    )->first())
                    {

                        if ($dry_run)
                        {
                            $this->alert('(Dry run) Deleted from Auth0 ' . $Auth0UserObj->email);

                        }
                        else
                        {
                            $this->UserRepositoryObj->deleteUserFromAuth0($Auth0UserObj->email, $UserToDeactivateObj->authenticatingEntity->identity_connection);
                            $this->alert('Deleted from Auth0 ' . $Auth0UserObj->email);
                        }
                    }
                }
            }
        }
    }
}