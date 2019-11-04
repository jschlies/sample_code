<?php

namespace App\Waypoint\Http\Controllers\Api;

use App;
use App\Waypoint\Exceptions\EntityTagException;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Exceptions\UploadException;
use App\Waypoint\Http\ApiController as BaseApiController;
use App\Waypoint\Http\Requests\Api\CreateImageRequest;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\EntityTag;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\User;
use App\Waypoint\Repositories\ClientRepository;
use App\Waypoint\Repositories\ImageRepository;
use App\Waypoint\Repositories\PropertyRepository;
use App\Waypoint\Repositories\UserRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * @codeCoverageIgnore
 */
class ImageDeprecatedController extends BaseApiController
{
    /** @var  ImageRepository */
    private $ImageRepositoryObj;

    /**
     * @todo See HER-1768
     */
    public function __construct(ImageRepository $ImageRepositoryObj)
    {
        $this->ImageRepositoryObj = $ImageRepositoryObj;
        parent::__construct($ImageRepositoryObj);
    }

    /**
     * @param integer $client_id
     * @param CreateImageRequest $CreateImageApiRequestObj
     * @return JsonResponse|null
     * @throws EntityTagException
     * @throws GeneralException
     * @throws UploadException
     * @throws \Intervention\Image\Exception\NotWritableException
     * @throws \InvalidArgumentException
     */
    public function storeClientImage($client_id, CreateImageRequest $CreateImageApiRequestObj)
    {
        $input = $CreateImageApiRequestObj->all();
        if (
            ! isset($input['image_subtype']) ||
            ! in_array($input['image_subtype'], ['CLIENTIMAGE']))
        {
            throw new GeneralException('No valid image_subtype provided');
        }
        $input['entity_model']  = Client::class;
        $input['image_subtype'] = 'USERIMAGE';

        $ImageObj = $this->ImageRepositoryObj->create($input);

        return $this->sendResponse($ImageObj, 'Image saved successfully');
    }

    /**
     * @param CreateImageRequest $CreateImageRequestObj
     * @return JsonResponse|null
     * @throws EntityTagException
     * @throws UploadException
     * @throws GeneralException
     * @throws \BadMethodCallException
     * @throws \Exception
     * @throws \Intervention\Image\Exception\NotWritableException
     * @throws \InvalidArgumentException
     */
    public function storePropertyImage(CreateImageRequest $CreateImageRequestObj)
    {
        $input = $CreateImageRequestObj->all();
        if ( ! isset($input['image_subtype']) || ! in_array($input['image_subtype'], ['PROPERTYIMAGE']))
        {
            throw new GeneralException('Invalid image_subtype provided');
        }
        $input['entity_model']  = Property::class;
        $input['image_subtype'] = 'PROPERTYIMAGE';

        $ImageObj = $this->ImageRepositoryObj->create($input);

        return $this->sendResponse($ImageObj, 'Image saved successfully');
    }

    /**
     * @param CreateImageRequest $CreateImageRequestObj
     * @return JsonResponse|null
     * @throws EntityTagException
     * @throws UploadException
     * @throws GeneralException
     * @throws \BadMethodCallException
     * @throws \Exception
     * @throws \Intervention\Image\Exception\NotWritableException
     * @throws \InvalidArgumentException
     */
    public function storeUserImage(CreateImageRequest $CreateImageRequestObj)
    {
        $input = $CreateImageRequestObj->all();
        if ( ! isset($input['image_subtype']) || ! in_array($input['image_subtype'], ['USERIMAGE']))
        {
            throw new GeneralException('No image_subtype provided');
        }
        $input['entity_model']  = User::class;
        $input['image_subtype'] = 'UserImage';

        $ImageObj = $this->ImageRepositoryObj->create($input);

        return $this->sendResponse($ImageObj, 'Image saved successfully');
    }

    /**
     * @param integer $client_id
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws ModelNotFoundException
     */
    public function showClientImages($client_id)
    {
        if ( ! $ClientObj = App::make(ClientRepository::class)->find($client_id))
        {
            throw new ModelNotFoundException('No such client');
        }
        return $this->sendResponse($ClientObj->getImageJSON(), 'Image(s) retrieved successfully');
    }

    /**
     * @param integer $client_id
     * @param integer $property_id
     * @param CreateImageRequest $CreateImageRequestObj
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws ModelNotFoundException
     */
    public function showPropertyImages($client_id, $property_id, CreateImageRequest $CreateImageRequestObj)
    {
        if ( ! $PropertyObj = App::make(PropertyRepository::class)->find($property_id))
        {
            throw new ModelNotFoundException('No such property');
        }
        return $this->sendResponse($PropertyObj->getImageJSON(), 'Image(s) retrieved successfully');
    }

    /**
     * @param integer $client_id
     * @param integer $user_id
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws ModelNotFoundException
     */
    public function showUserImages($client_id, $user_id)
    {
        if ( ! $UserObj = App::make(UserRepository::class)->find($user_id))
        {
            throw new ModelNotFoundException('No such user');
        }
        return $this->sendResponse($UserObj->getImageJSON(), 'Image saved successfully');
    }

    /**
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws \BadMethodCallException
     */
    public function getAvailable()
    {
        return $this->sendResponse(EntityTag::$image_values, 'Image(s) Available');
    }
}
