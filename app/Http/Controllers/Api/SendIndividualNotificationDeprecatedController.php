<?php

namespace App\Waypoint\Http\Controllers\API;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\User;
use App\Waypoint\Repositories\UserInvitationRepository;
use App\Waypoint\Repositories\UserRepository;
use App\Waypoint\Http\APIController as BaseAPIController;
use App\Waypoint\ResponseUtil;
use Exception;
use Response;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;

/**
 * Class SendIndividualNotificationDeprecatedController
 * @codeCoverageIgnore
 */
class SendIndividualNotificationDeprecatedController extends BaseAPIController
{
    use Notifiable;

    private $UserRepositoryObj = null;
    private $UserInvitationRepositoryObj;

    /**
     * SendEmailController constructor.
     * @param UserRepository $UserRepositoryObj
     */
    public function __construct(UserRepository $UserRepositoryObj)
    {
        $this->UserRepositoryObj = $UserRepositoryObj;
        parent::__construct($UserRepositoryObj);
        $this->UserInvitationRepositoryObj = App::make(UserInvitationRepository::class);
    }

    /**
     * @param integer $user_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     */
    public function resetPasswordNotification($user_id)
    {
        try
        {
            /** @var User $OpportunityObj */
            $ForgetfulUserObj = $this->UserRepositoryObj->find($user_id);
            if (empty($ForgetfulUserObj))
            {
                return Response::json(ResponseUtil::makeError('User not found'), 404);
            }

            $this->post_job_to_queue(
                [
                    'user_id'          => $ForgetfulUserObj->id,
                    'inviter_user_id'          => $this->getCurrentLoggedInUserObj()->id
                ],
                App\Waypoint\Jobs\ResetPasswordNotificationJob::class,
                config('queue.queue_lanes.ResetPasswordNotification', false)

            );
        }
        catch (Exception $e)
        {
            Log::alert('Failed password reset attempt ' . $ForgetfulUserObj->email);
        }

        /**
         * no matter what - respond with success
         */
        return $this->sendResponse(
            [], 'Reset Password!'
        );
    }

    /**
     * @param string $email
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \BadMethodCallException
     */
    public function passwordConfirmation($email)
    {
        try
        {
            $ForgetfulUserObj = $this->UserRepositoryObj->findWhere(
                ['email' => $email]
            )->first();

            if ($ForgetfulUserObj)
            {
                $this->post_job_to_queue(
                    [
                        'user_id'          => $ForgetfulUserObj->id,
                    ],
                    App\Waypoint\Jobs\PasswordConfirmationNotificationJob::class,
                    config('queue.queue_lanes.PasswordConfirmationNotification', false)

                );
            }
        }
        catch (Exception $e)
        {
            Log::alert('Failed passwordConfirmation attempt ' . $email);
        }

        /**
         * no matter what - respond with success
         */
        return $this->sendResponse(
            [], 'Password Confirmed!'
        );
    }
}