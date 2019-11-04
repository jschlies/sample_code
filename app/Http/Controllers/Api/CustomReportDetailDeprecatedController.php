<?php

namespace App\Waypoint\Http\Controllers\Api;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\HasAttachment;
use App\Waypoint\Http\ApiController as BaseApiController;
use App\Waypoint\Http\Requests\Generated\Api\CreateCustomReportRequest;
use App\Waypoint\Models\CustomReport;
use App\Waypoint\Models\CustomReportDetail;
use App\Waypoint\Models\CustomReportType;
use App\Waypoint\Models\Property;
use App\Waypoint\Repositories\CustomReportDetailRepository;
use App\Waypoint\Repositories\CustomReportTypeRepository;
use App\Waypoint\ResponseUtil;
use DB;
use Exception;
use Illuminate\Http\JsonResponse;
use Prettus\Validator\Exceptions\ValidatorException;
use Response;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;

/**
 * Class CustomReportDetailDeprecatedController
 * @codeCoverageIgnore
 */
class CustomReportDetailDeprecatedController extends BaseApiController
{
    use HasAttachment;

    /** @var CustomReportDetailRepository $CustomReportDetailRepositoryObj */
    private $CustomReportDetailRepositoryObj;
    /** @var CustomReportTypeRepository $CustomReportTypeRepositoryObj */
    private $CustomReportTypeRepositoryObj;
    /** @var CustomReportType $CustomReportTypeObj */
    private $CustomReportTypeObj;

    /**
     * CustomReportDetailController constructor.
     * @param CustomReportDetailRepository $CustomReportDetailRepositoryObj
     */
    public function __construct(CustomReportDetailRepository $CustomReportDetailRepositoryObj)
    {
        $this->CustomReportDetailRepositoryObj = $CustomReportDetailRepositoryObj;
        $this->CustomReportTypeRepositoryObj   = App::make(CustomReportTypeRepository::class);
        $this->CustomReportDetailRepositoryObj = App::make(CustomReportDetailRepository::class);
        parent::__construct($CustomReportDetailRepositoryObj);
    }

    /**
     * @param integer $client_id
     * @param integer $property_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     */
    public function index(
        $client_id,
        $property_id
    ) {
        if ( ! $PropertyObj = Property::find($property_id))
        {
            throw new GeneralException('We cannot find the property using the id given, please check the id and try again.');
        }

        $CustomReportTypesForThisClient = $this->CustomReportTypeRepositoryObj->findWhere(['client_id' => $client_id]);
        $CustomReports                  = $this->CustomReportDetailRepositoryObj->findWhere(['property_id' => $property_id]);

        return $this->sendResponse(
            $CustomReports->toArray(),
            'custom report listing generated successfully',
            [],
            ($CustomReports->count() == 0 ? 'no custom reports for this property' : []),
            ['report_types' => $CustomReportTypesForThisClient]
        );
    }

    /**
     * Store a newly created CustomReport in storage.
     *
     * @param CreateCustomReportRequest $CustomReportRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws \Exception
     */
    public function store(
        $client_id,
        $property_id,
        $custom_report_type_id,
        $year,
        $period,
        CreateCustomReportRequest $CustomReportRequestObj
    ) {
        if (count($CustomReportRequestObj->allFiles()) !== 1)
        {
            if (count($CustomReportRequestObj->allFiles()) > 1)
            {
                throw new GeneralException('Please upload one attachment at a time');
            }
            elseif (count($CustomReportRequestObj->allFiles()) < 1)
            {
                throw new GeneralException('Please upload at least one attachment');
            }
        }

        if ( ! $this->CustomReportTypeObj = $this->CustomReportTypeRepositoryObj->find($custom_report_type_id))
        {
            throw new GeneralException('could not find custom report type');
        }

        $this->CustomReportTypeRepositoryObj->validatePayloadWithoutRequestObj($this->CustomReportTypeObj->period_type, $period, $year);

        DB::beginTransaction();

        if ( ! $CustomReportDetailObj = $this->CustomReportDetailRepositoryObj->findWhere(
            [
                'property_id'           => $property_id,
                'custom_report_type_id' => $custom_report_type_id,
                'year'                  => $year,
                'period'                => $period,
            ]
        )->first())
        {
            /** @var CustomReportDetail $CustomReportDetailObj */
            $CustomReportDetailObj                        = App::make(CustomReportDetail::class);
            $CustomReportDetailObj->year                  = (int) $year;
            $CustomReportDetailObj->period                = $period;
            $CustomReportDetailObj->property_id           = (int) $property_id;
            $CustomReportDetailObj->custom_report_type_id = (int) $custom_report_type_id;
            $CustomReportDetailObj->download_url          = '';
            $CustomReportDetailObj->file_type             = '';
            $CustomReportDetailObj                        = $CustomReportDetailObj->save();
        }

        try
        {
            /** @var SymfonyUploadedFile $FileObj */
            foreach ($CustomReportRequestObj->allFiles() as $FileObj)
            {
                $CustomReportAttachmentObj = $CustomReportDetailObj->attach(
                    $FileObj->getRealPath(),
                    [
                        'disk'               => config('waypoint.attachment_data_store_disc', 's3_attachments'),
                        'title'              => $FileObj->getFilename(),
                        'description'        => $FileObj->getFilename(),
                        'key'                => $FileObj->getClientOriginalName(),
                        'created_by_user_id' => $this->getCurrentLoggedInUserObj()->id,
                    ]
                );
            }

            $CustomReportDetailObj->download_url = '/api/v1/ClientUser/attachments/' . $CustomReportAttachmentObj->id . '/download';
            $CustomReportDetailObj->file_type    = $CustomReportAttachmentObj->filetype;
            $CustomReportDetailObj               = $CustomReportDetailObj->save();

            DB::commit();
        }
        catch (GeneralException $e)
        {
            DB::rollBack();
            throw $e;
        }
        catch (Exception $e)
        {
            DB::rollBack();
            throw new GeneralException('An error has occured', 500, $e);
        }
        return $this->sendResponse($CustomReportDetailObj->toArray(), 'CustomReport saved successfully');
    }

    /**
     * @param $client_id
     * @param $property_id
     * @param $custom_report_id
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function destroy($client_id, $property_id, $custom_report_id)
    {
        /** @var CustomReport $CustomReportObj */
        $CustomReportObj = $this->CustomReportDetailRepositoryObj->findWithoutFail($custom_report_id);
        if (empty($CustomReportObj))
        {
            return Response::json(ResponseUtil::makeError('AccessList not found'), 404);
        }

        $this->CustomReportDetailRepositoryObj->delete($custom_report_id);

        return $this->sendResponse($custom_report_id, 'CustomReport deleted successfully');
    }
}