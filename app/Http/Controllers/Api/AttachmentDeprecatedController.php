<?php

namespace App\Waypoint\Http\Controllers\Api;

use App;
use App\Waypoint\Events\AdvancedVarianceLineItemDetachedEvent;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Exceptions\PolicyException;
use App\Waypoint\Models\AdvancedVariance;
use App\Waypoint\Models\AdvancedVarianceLineItem;
use App\Waypoint\Models\Opportunity;
use App\Waypoint\Repositories\AttachmentRepository;
use App\Waypoint\Models\Attachment;
use App\Waypoint\Http\ApiController as BaseApiController;
use App\Waypoint\Repositories\DownloadHistoryRepository;
use Auth;
use Carbon\Carbon;
use Exception;
use Gate;

/**
 * Class AttachmentDeprecatedController
 * @codeCoverageIgnore
 */
class AttachmentDeprecatedController extends BaseApiController
{
    protected $AttachmentRepositoryObj;

    /**
     * AttachmentController constructor.
     */
    public function __construct(AttachmentRepository $AttachmentRepositoryObj)
    {
        parent::__construct($AttachmentRepositoryObj);
    }

    /**
     * @param integer $client_id
     * @param integer $attachment_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \BadMethodCallException
     */
    public function destroy($client_id, $attachment_id)
    {
        try
        {
            /** @var Attachment $AttachmentToDeleteObj */
            if ( ! $AttachmentToDeleteObj = App::make(AttachmentRepository::class)->find($attachment_id))
            {
                throw new GeneralException('No such attachment');
            }
            $class_in_question = $AttachmentToDeleteObj->model_type;
            $attachable_id     = $AttachmentToDeleteObj->model_id;

            /** @var Opportunity|AdvancedVariance|App\Waypoint\Models\AdvancedVarianceLineItem $ObjectInQuestionObj */
            /** @noinspection PhpUndefinedMethodInspection */
            $ObjectInQuestionObj = $class_in_question::find($attachable_id);

            if (Gate::denies($ObjectInQuestionObj->getTable() . '_access_policy', $ObjectInQuestionObj->id))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }

            if ($AttachmentToDeleteObj->createdByUser->id !== $this->getCurrentLoggedInUserObj()->id)
            {
                throw new GeneralException('This user is not the creator of this attachment');
            }
            $AttachmentToDeleteObj->delete();

            if ($class_in_question == Opportunity::class)
            {
                $this->post_job_to_queue(
                    [
                        'opportunity_id'   => $ObjectInQuestionObj->id,
                        'recipient_id_arr' => [$this->getCurrentLoggedInUserObj()->id],
                        'attachment_name'  => $AttachmentToDeleteObj->filename,
                        'detacher_user_id' => $this->CurrentLoggedInUserObj->id,
                    ],
                    App\Waypoint\Jobs\OpportunityDetachedNotificationJob::class,
                    config('queue.queue_lanes.OpportunityDetachedNotification', false)
                );
            }
            elseif ($class_in_question == AdvancedVariance::class)
            {
                $this->post_job_to_queue(
                    [
                        'advanced_variance_id' => $ObjectInQuestionObj->id,
                        'as_of_month'          => $ObjectInQuestionObj->as_of_month,
                        'as_of_year'           => $ObjectInQuestionObj->as_of_year,
                        'recipient_id_arr'     => $ObjectInQuestionObj->getArrayOfIDs(),
                        'attachment_name'      => $AttachmentToDeleteObj->filename,
                        'detacher_user_id'     => $this->CurrentLoggedInUserObj->id,
                    ],
                    App\Waypoint\Jobs\AdvancedVarianceDetachedNotificationJob::class,
                    config('queue.queue_lanes.AdvancedVarianceAttachmentNotification', false)
                );
            }
            elseif ($class_in_question == AdvancedVarianceLineItem::class)
            {
                $this->post_job_to_queue(
                    [
                        'advanced_variance_id'           => $ObjectInQuestionObj->advancedVariance->id,
                        'advanced_variance_line_item_id' => $ObjectInQuestionObj->id,
                        'as_of_month'                    => $ObjectInQuestionObj->as_of_month,
                        'as_of_year'                     => $ObjectInQuestionObj->as_of_year,
                        'recipient_id_arr'               => $ObjectInQuestionObj->advancedVariance->getExpectedRecipiants()->pluck('id')->toArray(),
                        'attachment_name'                => $AttachmentToDeleteObj->filename,
                        'detacher_user_id'               => $this->CurrentLoggedInUserObj->id,
                    ],
                    App\Waypoint\Jobs\AdvancedVarianceLineItemDetachedNotificationJob::class,
                    config('queue.queue_lanes.AdvancedVarianceLineItemDetachedNotification', false)
                );
            }
        }
        catch (GeneralException $e)
        {
            throw $e;
        }
        catch (Exception $e)
        {
            throw new GeneralException('An error has occured', 500, $e);
        }

        return $this->sendResponse($AttachmentToDeleteObj, 'Attachment deleted successfully');
    }

    /**
     * @param integer $attachment_id
     * @throws GeneralException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function downloadAttachment($attachment_id)
    {
        /** @var Attachment $AttachmentToDeleteObj */
        if ( ! $AttachmentToDeleteObj = App::make(AttachmentRepository::class)->find($attachment_id))
        {
            throw new GeneralException('No such attachment');
        }

        $class_in_question = $AttachmentToDeleteObj->model_type;
        $attachable_id     = $AttachmentToDeleteObj->model_id;

        /** @var Opportunity|AdvancedVariance|AdvancedVarianceLineItem $ObjectInQuestionObj */
        /** @noinspection PhpUndefinedMethodInspection */
        $ObjectInQuestionObj = $class_in_question::find($attachable_id);

        if (Gate::denies($ObjectInQuestionObj->getTable() . '_access_policy', $ObjectInQuestionObj->id))
        {
            throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
        }
        /** @var Attachment $FileAttachmentObj */
        $FileAttachmentObj = $ObjectInQuestionObj->attachment($AttachmentToDeleteObj->key);

        $DownloadHistoryRepositoryObj = App::make(DownloadHistoryRepository::class);
        $DownloadHistoryRepositoryObj->create(
            [
                'original_file_name' => $AttachmentToDeleteObj->key,
                'download_time'      => Carbon::now()->format('Y-m-d H:i:s'),
                'download_md5'       => md5($FileAttachmentObj->getContents()),
                'download_type'      => $FileAttachmentObj->filetype,
                'user_id'            => Auth::getUser() ? Auth::getUser()->id : null,
                'data'               => $FileAttachmentObj->filepath,
            ]
        );

        /**
         * for reasons internal to Bnb\Laravel\Attachments, we need to flip 'filename'
         * to 'key'. the real name of the file.
         *
         * Also note that $FileAttachmentObj->output(); exits PHP
         */
        $FileAttachmentObj->output();
    }
}
