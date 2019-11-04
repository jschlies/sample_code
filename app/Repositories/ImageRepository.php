<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Exceptions\EntityTagException;
use App\Waypoint\Exceptions\UploadException;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\ClientDefaultPropertyImage;
use App\Waypoint\Models\ClientDetail;
use App\Waypoint\Models\ClientLogo;
use App\Waypoint\Models\Image;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\PropertyDetail;
use App\Waypoint\Models\PropertySummary;
use App\Waypoint\Models\User;
use App\Waypoint\Models\UserDetail;
use App\Waypoint\Models\UserLogo;
use App\Waypoint\Models\UserSummary;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use ImageMagik;
use Webpatser\Uuid\Uuid;
use App\Waypoint\Repository as BaseRepository;

/**
 * Class ConfigRepository
 * @package App\Waypoint\Repositories
 */
class ImageRepository extends BaseRepository
{
    public static $image_dimensions = [
        Client::class                     => [
            'small'        => [
                'height'           => 150,
                'width'            => 150,
                'background-color' => null,
                'retinal'          => false,
            ],
            'small-sq'     => [
                'height'           => 150,
                'width'            => 125,
                'background-color' => null,
                'retinal'          => false,
            ],
            'small@2x'     => [
                'height'           => 150,
                'width'            => 300,
                'background-color' => null,
                'retinal'          => true,
            ],
            'small-sq@2x'  => [
                'height'           => 300,
                'width'            => 150,
                'background-color' => null,
                'retinal'          => true,
            ],
            'medium'       => [
                'height'           => 300,
                'width'            => 600,
                'background-color' => null,
                'retinal'          => false,
            ],
            'medium-sq'    => [
                'height'           => 600,
                'width'            => 300,
                'background-color' => null,
                'retinal'          => false,
            ],
            'medium@2x'    => [
                'height'           => 1200,
                'width'            => 1200,
                'background-color' => null,
                'retinal'          => true,
            ],
            'medium-sq@2x' => [
                'height'           => 1200,
                'width'            => 1200,
                'background-color' => null,
                'retinal'          => true,
            ],
            'large'        => [
                'height'           => 1200,
                'width'            => 1200,
                'background-color' => null,
                'retinal'          => false,
            ],
            'large-sq'     => [
                'height'           => 1200,
                'width'            => 1200,
                'background-color' => null,
                'retinal'          => false,
            ],
            'large@2x'     => [
                'height'           => 2400,
                'width'            => 2400,
                'background-color' => null,
                'retinal'          => true,
            ],
            'large-sq@2x'  => [
                'height'           => 2400,
                'width'            => 2400,
                'background-color' => null,
                'retinal'          => true,
            ],
        ],
        ClientLogo::class                 => [
            'small'        => [
                'height'           => 150,
                'width'            => 150,
                'background-color' => null,
                'retinal'          => false,
            ],
            'small-sq'     => [
                'height'           => 150,
                'width'            => 150,
                'background-color' => null,
                'retinal'          => false,
            ],
            'small@2x'     => [
                'height'           => 300,
                'width'            => 300,
                'background-color' => null,
                'retinal'          => true,
            ],
            'small-sq@2x'  => [
                'height'           => 300,
                'width'            => 300,
                'background-color' => null,
                'retinal'          => true,
            ],
            'medium'       => [
                'height'           => 600,
                'width'            => 600,
                'background-color' => null,
                'retinal'          => false,
            ],
            'medium-sq'    => [
                'height'           => 600,
                'width'            => 600,
                'background-color' => null,
                'retinal'          => false,
            ],
            'medium@2x'    => [
                'height'           => 1200,
                'width'            => 1200,
                'background-color' => null,
                'retinal'          => true,
            ],
            'medium-sq@2x' => [
                'height'           => 1200,
                'width'            => 1200,
                'background-color' => null,
                'retinal'          => true,
            ],
            'large'        => [
                'height'           => 1200,
                'width'            => 1200,
                'background-color' => null,
                'retinal'          => false,
            ],
            'large-sq'     => [
                'height'           => 1200,
                'width'            => 1200,
                'background-color' => null,
                'retinal'          => false,
            ],
            'large@2x'     => [
                'height'           => 2400,
                'width'            => 2400,
                'background-color' => null,
                'retinal'          => true,
            ],
            'large-sq@2x'  => [
                'height'           => 2400,
                'width'            => 2400,
                'background-color' => null,
                'retinal'          => true,
            ],
        ],
        ClientDefaultPropertyImage::class => [
            'small'        => [
                'height'           => 150,
                'width'            => 150,
                'background-color' => null,
                'retinal'          => false,
            ],
            'small-sq'     => [
                'height'           => 150,
                'width'            => 150,
                'background-color' => null,
                'retinal'          => false,
            ],
            'small@2x'     => [
                'height'           => 300,
                'width'            => 300,
                'background-color' => null,
                'retinal'          => true,
            ],
            'small-sq@2x'  => [
                'height'           => 300,
                'width'            => 300,
                'background-color' => null,
                'retinal'          => true,
            ],
            'medium'       => [
                'height'           => 600,
                'width'            => 600,
                'background-color' => null,
                'retinal'          => false,
            ],
            'medium-sq'    => [
                'height'           => 600,
                'width'            => 600,
                'background-color' => null,
                'retinal'          => false,
            ],
            'medium@2x'    => [
                'height'           => 1200,
                'width'            => 1200,
                'background-color' => null,
                'retinal'          => true,
            ],
            'medium-sq@2x' => [
                'height'           => 1200,
                'width'            => 1200,
                'background-color' => null,
                'retinal'          => true,
            ],
            'large'        => [
                'height'           => 1200,
                'width'            => 1200,
                'background-color' => null,
                'retinal'          => false,
            ],
            'large-sq'     => [
                'height'           => 1200,
                'width'            => 1200,
                'background-color' => null,
                'retinal'          => false,
            ],
            'large@2x'     => [
                'height'           => 2400,
                'width'            => 2400,
                'background-color' => null,
                'retinal'          => true,
            ],
            'large-sq@2x'  => [
                'height'           => 2400,
                'width'            => 2400,
                'background-color' => null,
                'retinal'          => true,
            ],
        ],
        Property::class                   => [
            'medium-sq' => [
                'height'           => 300,
                'width'            => 300,
                'background-color' => null,
                'retinal'          => false,
            ],
        ],
        User::class                       => [
            'medium-sq' => [
                'height'           => 600,
                'width'            => 600,
                'background-color' => null,
                'retinal'          => false,
            ],
        ],
        UserLogo::class                   => [
            'small'     => [
                'height'           => 150,
                'width'            => 175,
                'background-color' => null,
                'retinal'          => false,
            ],
            'small-sq'  => [
                'height'           => 150,
                'width'            => 150,
                'background-color' => null,
                'retinal'          => false,
            ],
            'medium'    => [
                'height'           => 600,
                'width'            => 700,
                'background-color' => null,
                'retinal'          => false,
            ],
            'medium-sq' => [
                'height'           => 600,
                'width'            => 600,
                'background-color' => null,
                'retinal'          => false,
            ],
            'large'     => [
                'height'           => 1200,
                'width'            => 1200,
                'background-color' => null,
                'retinal'          => false,
            ],
            'large-sq'  => [
                'height'           => 1200,
                'width'            => 1300,
                'background-color' => null,
                'retinal'          => false,
            ],
        ],
    ];

    /** @var \Intervention\Image\Image */
    private $ImageAssetObj = null;

    /**
     * @param array $attributes
     * @return object
     * @throws \App\Waypoint\Exceptions\EntityTagException
     * @throws \App\Waypoint\Exceptions\UploadException
     * @throws \Exception
     * @throws \Intervention\Image\Exception\NotWritableException
     * @throws \InvalidArgumentException
     */
    public function create(array $attributes)
    {
        if ( ! isset($attributes['upload_file']))
        {
            throw new EntityTagException('No upload file');
        }
        $attributes['data'] = [];
        if (is_object($attributes['upload_file']))
        {
            $original_upload_file = $attributes['upload_file']->path();
        }
        else
        {
            $original_upload_file = $attributes['upload_file'];
        }

        if ( ! file_exists($original_upload_file))
        {
            throw new UploadException('Issue with processing image ' . $original_upload_file, 404);
        }

        if ( ! preg_match("/^[0-9A-z_\-\. \/]*$/", $original_upload_file))
        {
            throw new EntityTagException('Invalid image file name ' . $original_upload_file);
        }

        try
        {
            /** @var UploadedFile $fileObj */
            $s3_object_key       = Uuid::generate() . '.png';
            $this->ImageAssetObj = ImageMagik::make($original_upload_file);
            $this->ImageAssetObj->encode('png', 75);
            $this->ImageAssetObj->save(storage_path('cache') . '/image/' . $s3_object_key);
        }
        catch (\Exception $ExceptionObj)
        {
            $name = '';
            if (isset($attributes['name']))
            {
                $name = $attributes['name'];
            }
            throw new UploadException($ExceptionObj->getMessage() . ' ' . $name, 404, $ExceptionObj);
        }

        /** @var \Aws\Sdk $AwsSdkObj */
        $AwsSdkObj = new \Aws\Sdk(config('aws', []));

        /** @var \Aws\S3\S3Client $S3ImageClientObj */
        $S3ImageClientObj = $AwsSdkObj->createClient(config('waypoint.image_data_store_disc', 's3_images'));

        $S3ImageClientObj->putObject(
            [
                'Bucket'              => config('waypoint.aws_hermes_images_bucket', 'hermes-images'),
                'Key'                 => $s3_object_key,
                'SourceFile'          => $this->ImageAssetObj->dirname . '/' . $this->ImageAssetObj->basename,
                'ResponseContentType' => 'image/png',
                'ContentType'         => 'image/png',
            ]
        );

        $attributes['data']['original']['url']    = $S3ImageClientObj->getObjectUrl(
            config('waypoint.aws_hermes_images_bucket', null),
            $s3_object_key
        );
        $attributes['data']['original']['height'] = $this->ImageAssetObj->height();
        $attributes['data']['original']['width']  = $this->ImageAssetObj->width();

        foreach (self::$image_dimensions[$attributes['entity_model']] as $spec_name => $spec)
        {
            /** @var UploadedFile $fileObj */
            $s3_object_key       = Uuid::generate() . '.png';
            $this->ImageAssetObj = ImageMagik::make($original_upload_file);
            $this->ImageAssetObj->save(storage_path('cache') . '/image/' . $s3_object_key);

            $this->contain($spec['width'], $spec['height'], $spec['background-color']);

            if ($spec['retinal'])
            {
                $this->ImageAssetObj->sharpen(20);
            }
            $s3_object_key = Uuid::generate() . '.png';
            $S3ImageClientObj->putObject(
                [
                    'Bucket'              => config('waypoint.aws_hermes_images_bucket', null),
                    'Key'                 => $s3_object_key,
                    'SourceFile'          => $this->ImageAssetObj->dirname . '/' . $this->ImageAssetObj->basename,
                    'ResponseContentType' => 'image/png',
                    'ContentType'         => 'image/png',

                ]
            );
            $attributes['data'][$spec_name]        = $spec;
            $attributes['data'][$spec_name]['url'] = $S3ImageClientObj->getObjectUrl(
                config('waypoint.aws_hermes_images_bucket', null),
                $s3_object_key
            );
        }

        /**
         * @todo See HER-1768
         */
        if (
            $attributes['entity_model'] == Client::class ||
            $attributes['entity_model'] == ClientDetail::class
        )
        {
            if ( ! $ClientObj = \App::make(ClientRepository::class)->find($attributes['entity_id']))
            {
                throw new ModelNotFoundException('No such Client or invalid Client or invalid entity_model ');
            }
            if ( ! isset($attributes['image_subtype']))
            {
                throw new ModelNotFoundException('No such Client or invalid Client or invalid entity_model or invalid image_subtype');
            }
            $ClientObj->updateImage($attributes['image_subtype'], $attributes['data']);
            return $ClientObj->getImageJSON(true);
        }
        elseif (
            $attributes['entity_model'] == User::class ||
            $attributes['entity_model'] == UserDetail::class ||
            $attributes['entity_model'] == UserSummary::class
        )
        {
            if ( ! $UserObj = \App::make(UserRepository::class)->find($attributes['entity_id']))
            {
                throw new ModelNotFoundException('No such User or invalid User or invalid entity_model ');
            }
            $UserObj->updateImage('UserImage', $attributes['data']);
            return $UserObj->getImageJSON(true);
        }
        elseif (
            $attributes['entity_model'] == Property::class ||
            $attributes['entity_model'] == PropertyDetail::class ||
            $attributes['entity_model'] == PropertySummary::class
        )
        {
            if ( ! $PropertyObj = \App::make(PropertyRepository::class)->find($attributes['entity_id']))
            {
                throw new ModelNotFoundException('No such Property or invalid Property or invalid entity_model ');
            }
            $PropertyObj->updateImage('PROPERTYIMAGE', $attributes['data']);
            return $PropertyObj->getImageJSON(true);
        }
        else
        {
            throw new ModelNotFoundException('No such object or invalid object or invalid entity_model ');
        }
    }

    /**
     * @param $maxWidth
     * @param $maxHeight
     * @param $bgcolor
     * @throws \Intervention\Image\Exception\NotWritableException
     */
    public function contain($maxWidth, $maxHeight, $bgcolor)
    {
        $originalWidth  = $this->ImageAssetObj->width();
        $originalHeight = $this->ImageAssetObj->height();
        if ($originalWidth > $maxWidth || $originalHeight > $maxHeight || 1)
        {
            $ratioX = $originalWidth / $maxWidth;
            $ratioY = $originalHeight / $maxHeight;
            if ($ratioX > $ratioY)
            {
                $this->ImageAssetObj->resize(
                    $maxWidth,
                    null,
                    function ($constraint)
                    {
                        $constraint->aspectRatio();
                    }
                );
            }
            else
            {
                $this->ImageAssetObj->resize(
                    null,
                    $maxHeight,
                    function ($constraint)
                    {
                        $constraint->aspectRatio();
                    }
                );
            }

            $this->ImageAssetObj->resizeCanvas($maxWidth, $maxHeight, 'center', false, $bgcolor);
            $this->ImageAssetObj->save();
        }
    }

    /**
     * Configure the Model
     *
     * @todo See HER-1768
     **/
    public function model()
    {
        return Image::class;
    }
}
