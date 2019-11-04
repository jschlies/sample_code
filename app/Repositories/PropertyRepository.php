<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Events\PreCalcPropertiesEvent;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\ModelDateFormatterTrait;
use App\Waypoint\Models\User;
use App\Waypoint\SmartyStreets;
use ArrayObject;
use Cache;
use Carbon\Carbon;
use Exception;
use FireEngineRed\SmartyStreetsLaravel\SmartyStreetsService;
use App\Waypoint\Models\Property;
use App;
use Illuminate\Container\Container as Application;
use App\Waypoint\Models\Role;
use App\Waypoint\Exceptions\SmartyStreetsException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Goodby\CSV\Import\Standard\Interpreter;
use Goodby\CSV\Import\Standard\Lexer;
use Goodby\CSV\Import\Standard\LexerConfig;
use App\Waypoint\Exceptions\UploadException;
use Chumper\Zipper\Zipper;

/**
 * Class PropertyRepository
 * @package App\Waypoint\Repositories
 */
class PropertyRepository extends PropertyRepositoryBase
{
    /** @var PropertyGroupRepository */
    protected $PropertyGroupRepositoryObj;

    /** @var PropertyGroupPropertyRepository */
    protected $PropertyGroupPropertyRepositoryObj;

    /** @var AccessListPropertyRepository */
    protected $AccessListPropertyRepositoryObj;

    /** @var NativeCoaRepository */
    protected $NativeCoaRepositoryObj;

    /** @var AccessListRepository */
    protected $AccessListRepositoryObj;

    /** @var SmartyStreets */
    protected $SmartyStreetsObj;

    /**
     * PropertyRepository constructor.
     * @param \Illuminate\Container\Container $app
     * @throws \App\Waypoint\Exceptions\DeploymentException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function __construct(Application $app)
    {
        parent::__construct($app);

        $this->PropertyGroupRepositoryObj         = $this->makeRepository(PropertyGroupRepository::class);
        $this->PropertyGroupPropertyRepositoryObj = $this->makeRepository(PropertyGroupPropertyRepository::class);
        $this->AccessListRepositoryObj            = $this->makeRepository(AccessListRepository::class);
        $this->AccessListPropertyRepositoryObj    = $this->makeRepository(AccessListPropertyRepository::class);
        $this->NativeCoaRepositoryObj             = $this->makeRepository(NativeCoaRepository::class);
        $this->SmartyStreetsObj                   = new App\Waypoint\SmartyStreets();
    }

    /**
     * @param array $attributes
     * @return Property
     * @throws App\Waypoint\Exceptions\GeneralException
     * @throws SmartyStreetsException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function create(array $attributes)
    {
        if ( ! isset($attributes['display_address']) || ! $attributes['display_address'])
        {
            if (isset($attributes['street_address']) && $attributes['street_address'])
            {
                $attributes['display_address'] = $attributes['street_address'];
            }
        }
        if ( ! isset($attributes['active_status']) || ! $attributes['active_status'])
        {
            $attributes['active_status'] = Property::ACTIVE_STATUS_ACTIVE;
        }
        if ( ! isset($attributes['active_status_date']) || ! $attributes['active_status_date'])
        {
            $attributes['active_status_date'] = Carbon::now()->format('Y-m-d H:i:s');
        }
        if ( ! isset($attributes['acquisition_date']) || ! $attributes['acquisition_date'])
        {
            $attributes['acquisition_date'] = null;
        }
        if ( ! isset($attributes['management_type']) || ! $attributes['management_type'])
        {
            $attributes['management_type'] = Property::MANAGEMENT_TYPE_DEFAULT;
        }
        if ( ! isset($attributes['lease_type']) || ! $attributes['lease_type'])
        {
            $attributes['lease_type'] = Property::LEASE_TYPE_DEFAULT;
        }
        if ( ! isset($attributes['custom_attributes']) || ! $attributes['custom_attributes'])
        {
            $attributes['custom_attributes'] = json_encode(new ArrayObject());
        }
        /**
         * because in MySQL, you can't default a blob
         */
        if ( ! isset($attributes['config_json']) || ! $attributes['config_json'])
        {
            $attributes['config_json'] = json_encode(new ArrayObject());
        }
        if ( ! isset($attributes['image_json']) || ! $attributes['image_json'])
        {
            $attributes['image_json'] = json_encode(new ArrayObject());
        }
        if ( ! isset($attributes['longitude']) || ! $attributes['longitude'])
        {
            $attributes['longitude'] = 0;
        }
        if ( ! isset($attributes['latitude']) || ! $attributes['latitude'])
        {
            $attributes['latitude'] = 0;
        }
        if ( ! isset($attributes['load_factor_old']) || ! $attributes['load_factor_old'])
        {
            $attributes['load_factor_old'] = 1;
        }
        if ( ! isset($attributes['suppress_address_validation']))
        {
            $attributes['suppress_address_validation'] = false;
        }
        $attributes['address_validation_failed'] = false;
        $attributes['raw_upload']                = json_encode($attributes);
        /**
         * Don't forget that this can be over-ridden in phpunit.xml
         */
        if ( ! $attributes['suppress_address_validation'])
        {
            $this->SmartyStreetsObj->updateWithCleanAddress($attributes);
        }

        $PropertyObj = parent::create($attributes);

        $PropertyNativeCoaRepositoryObj = App::make(PropertyNativeCoaRepository::class);
        /**
         * for now, tie the property to the clients first mappingGroup
         */
        if (
            ! isset($attributes['native_coa_id']) ||
            ! $attributes['native_coa_id']
        )
        {
            if ($NativeCoaObj = $PropertyObj->client->nativeCoas->first())
            {
                $PropertyNativeCoaRepositoryObj->create(
                    [
                        'property_id'   => $PropertyObj->id,
                        'native_coa_id' => $NativeCoaObj->id,
                    ]
                );
            }
            else
            {
                throw new GeneralException('No nativeCoas defined for ' . $PropertyObj->client->name);
            }
        }
        else
        {
            if ( ! $NativeCoaObj = $this->NativeCoaRepositoryObj->find($attributes['native_coa_id']))
            {
                throw new GeneralException('No such nativeCoas defined for ' . $PropertyObj->client->name);
            }
            if ($NativeCoaObj->client_id != $PropertyObj->client_id)
            {
                throw new GeneralException('No such nativeCoas defined for ' . $PropertyObj->client->name);
            }
            $PropertyNativeCoaRepositoryObj->create(
                [
                    'property_id'   => $PropertyObj->id,
                    'native_coa_id' => $NativeCoaObj->id,
                ]
            );
        }
        Cache::tags('Property_' . $PropertyObj->client_id)->flush();
        /**
         * deal with edge case of new property
         */
        event(
            new PreCalcPropertiesEvent(
                $PropertyObj->client,
                [
                    'event_trigger_message'        => 'Triggered at ' . __CLASS__ . ':' . __LINE__,
                    'event_trigger_id'             => waypoint_generate_uuid(),
                    'event_trigger_class'          => self::class,
                    'event_trigger_class_instance' => get_class($this),
                    'event_trigger_object_class'   => get_class($PropertyObj),
                    'event_trigger_absolute_class' => __CLASS__,
                    'event_trigger_file'           => __FILE__,
                    'event_trigger_line'           => __LINE__,
                    'wipe_out_list'                =>
                        [
                            'properties' => [],
                        ],
                    'launch_job_property_id_arr'   => [$PropertyObj->id],
                ]
            )
        );

        return $PropertyObj;
    }

    /**
     * @return null|SmartyStreetsService
     */
    public function getSmartyStreetsServiceObj()
    {
        if ( ! $this->getSmartyStreetsServiceObj())
        {
            $this->setSmartyStreetsServiceObj(new SmartyStreetsService());
        }
        return $this->SmartyStreetsServiceObj;
    }

    /**
     * @param SmartyStreetsService $SmartyStreetsServiceObj
     */
    public function setSmartyStreetsServiceObj(SmartyStreetsService $SmartyStreetsServiceObj)
    {
        $this->SmartyStreetsServiceObj = $SmartyStreetsServiceObj;
    }

    /**
     * Update a entity in repository by id
     *
     * @param array $attributes
     * @param integer $id
     * @return Property
     * @throws \App\Waypoint\Exceptions\SmartyStreetsException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function update(array $attributes, $id)
    {
        $PropertyObj = $this->find($id);

        $raw_upload_json = json_decode($PropertyObj->raw_upload);
        foreach ($attributes as $key => $value)
        {
            $raw_upload_json->$key = $value;
        }
        $attributes['raw_upload'] = json_encode($raw_upload_json);

        if (isset($attributes['custom_attributes']) && ! $attributes['custom_attributes'])
        {
            $attributes['custom_attributes'] = json_encode(new ArrayObject());
        }
        if (isset($attributes['suppress_address_validation']))
        {
            if ( ! $attributes['suppress_address_validation'])
            {
                {
                    $this->SmartyStreetsObj->updateWithCleanAddress($attributes);
                }
            }
            elseif ( ! $PropertyObj->suppress_address_validation)
            {
                $this->SmartyStreetsObj->updateWithCleanAddress($attributes);
            }
        }

        $PropertyObj = parent::update($attributes, $id);
        Cache::tags('Property_' . $PropertyObj->client_id)->flush();

        return $PropertyObj;
    }

    /**
     * Delete a entity in repository by id
     *
     * @param int $id
     * @return bool
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function delete($property_id)
    {
        $PropertyObj = $this->find($property_id);
        $result      = parent::delete($property_id);
        Cache::tags('Property_' . $PropertyObj->client_id)->flush();

        return $result;
    }

    /**
     * @param array|\Symfony\Component\HttpFoundation\File\UploadedFile $upload_file
     * @return array|\Symfony\Component\HttpFoundation\File\UploadedFile
     */
    public function parseUploadPropertyFile($upload_file)
    {
        if ( ! is_object($upload_file))
        {
            $upload_file = new UploadedFile($upload_file, $upload_file);
        }

        $line_items = [];
        $LexerObj   = new Lexer(new LexerConfig());

        $InterpreterObj = new Interpreter();
        $InterpreterObj->unstrict();
        /**
         * Keep this in sync with PropertyReportController
         */
        $InterpreterObj->addObserver(
            function (array $row) use (&$line_items)
            {
                $line_items[] = [
                    'name'                => isset($row[0]) ? trim($row[0]) : null,
                    'display_name'        => isset($row[1]) ? trim($row[1]) : null,
                    'property_code'       => isset($row[2]) ? trim($row[2]) : null,
                    'description'         => isset($row[3]) ? trim($row[3]) : null,
                    'accounting_system'   => isset($row[4]) ? trim($row[4]) : null,
                    'street_address'      => isset($row[5]) ? trim($row[5]) : null,
                    'display_address'     => isset($row[6]) ? trim($row[6]) : null,
                    'city'                => isset($row[7]) ? trim($row[7]) : null,
                    'state'               => isset($row[8]) ? trim($row[8]) : null,
                    'country'             => isset($row[9]) ? trim($row[9]) : null,
                    'country_code'        => isset($row[10]) ? trim($row[10]) : null,
                    'square_footage'      => isset($row[11]) ? trim($row[11]) : null,
                    'asset_type'          => isset($row[12]) ? trim($row[12]) : null,
                    'year_built'          => isset($row[13]) ? trim($row[13]) : null,
                    'management_type'     => isset($row[14]) ? trim($row[14]) : null,
                    'lease_type'          => isset($row[15]) ? trim($row[15]) : null,
                    'time_zone'           => isset($row[16]) ? trim($row[16]) : null,
                    'property_groups'     => isset($row[17]) ? trim($row[17]) : null,
                    'access_lists'        => isset($row[18]) ? trim($row[18]) : null,
                    'property_image'      => isset($row[19]) ? trim($row[19]) : null,
                    'property_class'      => isset($row[20]) ? trim($row[20]) : null,
                    'year_renovated'      => isset($row[21]) ? trim($row[21]) : null,
                    'number_of_buildings' => isset($row[22]) ? trim($row[22]) : null,
                    'number_of_floors'    => isset($row[23]) ? trim($row[23]) : null,
                    'postal_code'         => isset($row[24]) ? trim($row[24]) : null,
                    'longitude'           => isset($row[25]) ? trim($row[25]) : null,
                    'latitude'            => isset($row[26]) ? trim($row[26]) : null,
                    'delete'              => isset($row[27]) ? trim($row[27]) : null,
                ];
            }
        );
        $LexerObj->parse($upload_file->getPath() . '/' . $upload_file->getFilename(), $InterpreterObj);
        return $line_items;
    }

    /**
     * @param $image_zip
     * @return \Chumper\Zipper\Zipper|null
     * @throws \App\Waypoint\Exceptions\UploadException
     */
    public function unzip_images($image_zip)
    {
        if ( ! is_object($image_zip))
        {
            $image_zip = new UploadedFile($image_zip, $image_zip);
        }

        $ZipperImageObj = null;

        if ($image_zip)
        {
            if ($image_zip->getError())
            {
                throw new UploadException('CreatePropertyRequestObj error = ' . self::getFileUploadStatusString($image_zip->getError()), 404);
            }
            if ( ! $image_zip->getPathname())
            {
                throw new UploadException('No filename provided', 404);
            }
            if ( ! file_exists($image_zip->getPathname()))
            {
                throw new UploadException('Failed processing non-existent file -- image_zip_path = ' . $image_zip->getPathname(), 404);
            }
            try
            {
                $ZipperObj = new Zipper();
                /** @var Zipper $ZipperImageObj */
                $ZipperImageObj = $ZipperObj->make($image_zip->getPathname());
            }
            catch (Exception $ExceptionObj)
            {
                throw new UploadException('unzip_images failed', 404, $ExceptionObj);
            }
        }
        return $ZipperImageObj;
    }

    /**
     * @param $line_items
     * @throws \App\Waypoint\Exceptions\UploadException
     */
    public function check_image_names($line_items)
    {
        foreach ($line_items as $line_item)
        {
            if (isset($line_item['property_image']) && $line_item['property_image'] && ! preg_match("/^[0-9A-z\.\/ ]*\.(jpg|png)$/", $line_item['property_image']))
            {
                throw new UploadException(
                    'Invalid image file name ' . $line_item['property_image'] . 'Please no non-alphaNumerics and use a lower-case extention ^[0-9A-z\.\/ ]*\.(jpg|png)$'
                );
            }
        }
    }

    /**
     * @param $property_groups
     * @param $PropertyObj
     * @param $ClientObj
     * @param $UserObj
     */
    public function process_property_groups($property_groups, $PropertyObj, $ClientObj, $UserObj)
    {
        /*
         * do not trigger exents in here
         */
        $this->PropertyGroupRepositoryObj->setSuppressEvents(true);
        $this->PropertyGroupPropertyRepositoryObj->setSuppressEvents(true);
        foreach (explode('|', $property_groups) as $property_group_name)
        {
            $property_group_name = trim($property_group_name);
            /**
             * create PropertyGroup if not exists
             */
            if ( ! $PropertyGroupObj = $this->PropertyGroupRepositoryObj->findWhere(
                [
                    'name'      => $property_group_name,
                    'user_id'   => $UserObj->id,
                    'client_id' => $UserObj->client_id,
                ]
            )->first()
            )
            {
                $PropertyGroupObj = $this->PropertyGroupRepositoryObj->create(
                    [
                        'name'                  => $property_group_name,
                        'client_id'             => $UserObj->client_id,
                        'user_id'               => $UserObj->id,
                        'is_all_property_group' => false,
                    ]
                );
            }
            /**
             * add $PropertyObj to the PropertyGroup if not already...
             */
            if ( ! $PropertyPropertyGroupObj = $this->PropertyGroupPropertyRepositoryObj->findWhere(
                [
                    'property_id'       => $PropertyObj->id,
                    'property_group_id' => $PropertyGroupObj->id,
                ]
            )->first()
            )
            {
                $this->PropertyGroupPropertyRepositoryObj->create(
                    [
                        'property_id'       => $PropertyObj->id,
                        'property_group_id' => $PropertyGroupObj->id,
                    ]
                );
            }
        }
    }

    /**
     * @param $access_lists
     * @param $PropertyObj
     * @param $ClientObj
     */
    public function process_access_lists($access_lists, $PropertyObj, $ClientObj)
    {
        /*
         * do not trigger exents in here
         */
        $this->AccessListRepositoryObj->setSuppressEvents(true);
        $this->AccessListPropertyRepositoryObj->setSuppressEvents(true);

        foreach (explode('|', $access_lists) as $access_list_name)
        {
            $access_list_name = trim($access_list_name);
            if ( ! $AccessListObj = $this->AccessListRepositoryObj->findWhere(
                [
                    'name'      => $access_list_name,
                    'client_id' => $ClientObj->id,
                ]
            )->first()
            )
            {
                $AccessListObj = $this->AccessListRepositoryObj->create(
                    [
                        'name'               => $access_list_name,
                        'client_id'          => $ClientObj->id,
                        'is_all_access_list' => false,
                    ]
                );
            }

            if ( ! $AccessListPropertyObj = $this->AccessListPropertyRepositoryObj->findWhere(
                [
                    'property_id'    => $PropertyObj->id,
                    'access_list_id' => $AccessListObj->id,
                ]
            )->first()
            )
            {
                $this->AccessListPropertyRepositoryObj->create(
                    [
                        'property_id'    => $PropertyObj->id,
                        'access_list_id' => $AccessListObj->id,
                    ]
                );
            }
        }
    }

    /**
     * @param $error_code
     * @return string
     * See https://blog.hqcodeshop.fi/archives/185-PHP-large-file-uploads.html
     */
    public static function getFileUploadStatusString($error_code)
    {
        switch ($error_code)
        {
            case UPLOAD_ERR_OK:
                $status = 'There is no error, the file uploaded with success.';
                break;
            case UPLOAD_ERR_INI_SIZE:
                $status = 'The uploaded file exceeds the upload_max_filesize directive in php.ini.';
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $status = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.' . ' Value is set to: ' . $_POST['MAX_FILE_SIZE'];
                break;
            case UPLOAD_ERR_PARTIAL:
                $status = 'The uploaded file was only partially uploaded.';
                break;
            case UPLOAD_ERR_NO_FILE:
                $status = 'No file was uploaded.';
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $status = 'Missing a temporary folder.';
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $status = 'Failed to write file to disk.';
                break;
            case UPLOAD_ERR_EXTENSION:
                $status = 'A PHP extension stopped the file upload. PHP does not provide a way to ascertain which extension caused the file upload to stop; examining the list of loaded extensions with phpinfo() may help.';
                break;
            default:
                return 'No idea. Huh?';
        }
        return $status . ' see http://php.net/manual/en/ini.core.php#ini.upload-max-filesize ';
    }

    /**
     * @param integer $property_id
     * @return App\Waypoint\Collection|array
     * @throws App\Waypoint\Exceptions\GeneralException
     */
    public function getUsersOfProperty($property_id)
    {
        $UserRepositoryObj = App::make(UserRepository::class);

        if ( ! $PropertyObj = $this->find($property_id))
        {
            throw new App\Waypoint\Exceptions\GeneralException('Property not found');
        }

        /**
         * grab all users on clients all-acess list
         */
        $all_access_list_user_arr = DB::select(
            DB::raw(

                "SELECT DISTINCT(access_list_users.user_id) AS user_id
                    FROM  access_list_users, access_lists
                    WHERE                           
                          access_list_users.access_list_id = access_lists.id AND
                          access_lists.is_all_access_list = TRUE AND
                          access_lists.client_id = :client_id
                "
            ),
            [
                'client_id' => $PropertyObj->client_id,
            ]
        );

        /**
         * grab all users with rights to property via non-all-access-list access_lists
         */
        $property_user_arr = DB::select(
            DB::raw(

                "SELECT DISTINCT(access_list_users.user_id) AS user_id
                    FROM  access_list_users, access_list_properties    
                    WHERE                           
                          access_list_users.access_list_id =access_list_properties.access_list_id AND
                          property_id = :property_id
                "
            ),
            [
                'property_id' => $property_id,
            ]
        );

        /**
         * grab all the CLIENT_ADMINISTRATIVE_USER_ROLE's
         */
        $client_admin_user_arr = DB::select(
            DB::raw(

                "SELECT DISTINCT(users.id) AS user_id
                    FROM  users, role_users, roles    
                    WHERE
                        users.client_id = :CLIENT_ID AND
                        users.id =   role_users.user_id AND
                        role_users.role_id = roles.id AND
                                    (
                                        roles.name = :WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE ||
                                        roles.name = :WAYPOINT_ASSOCIATE_ROLE ||
                                        roles.name = :CLIENT_ADMINISTRATIVE_USER_ROLE 
                                    ) 
                "
            ),
            [
                'CLIENT_ID'                          => $PropertyObj->client_id,
                'WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE' => Role::WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE,
                'WAYPOINT_ASSOCIATE_ROLE'            => Role::WAYPOINT_ASSOCIATE_ROLE,
                'CLIENT_ADMINISTRATIVE_USER_ROLE'    => Role::CLIENT_ADMINISTRATIVE_USER_ROLE,
            ]
        );

        /**
         * seems that array_unique returns what it should but the array indexes
         * are not as expected so we call array_values()
         *
         * REMEMBER THESE ARE ARRAYS OF stdObjects
         */
        $user_id_arr = array_values(
            array_unique(
                array_map(
                    function ($val)
                    {
                        return $val->user_id;
                    },
                    array_merge($property_user_arr, $client_admin_user_arr, $all_access_list_user_arr)
                )
            )
        );

        /**
         * this will remove waypoint_employee's and filter out inactive and uninvited
         */
        $return_me = new App\Waypoint\Collection();
        foreach ($UserRepositoryObj->with('accessLists')->findWhereIn('id', $user_id_arr) as $UserObj)
        {
            if (
                ! $UserObj->is_hidden &&
                (
                    $UserObj->active_status == User::ACTIVE_STATUS_ACTIVE ||
                    (
                        $UserObj->active_status == User::ACTIVE_STATUS_INACTIVE &&
                        $UserObj->user_invitation_status == User::USER_INVITATION_STATUS_PENDING
                    )
                )
            )
            {
                $return_me[] = $UserObj;
            }
        }
        return $return_me;
    }

    /**
     * @param integer $user_id
     * @param integer $property_id
     * @param null $related_object_subtype
     * @return bool
     * @throws GeneralException
     * @throws \BadMethodCallException
     *
     * @todo consolidate this (look in AdvancedVariance) into a trait
     */
    public function add_user($user_id, $property_id, $related_object_subtype = null)
    {
        $UserRepositoryObj = App::make(UserRepository::class);
        if ( ! $CandidateUserObj = $UserRepositoryObj->find($user_id))
        {
            return false;
        }
        if ( ! $CandidateUserObj->canAccessProperty($property_id))
        {
            return false;
        }

        /** @var RelatedUserTypeRepository $RelatedUserTypeRepositoryObj */
        $RelatedUserTypeRepositoryObj = App::make(RelatedUserTypeRepository::class);
        if ( ! $RelatedUserTypeObj = $RelatedUserTypeRepositoryObj->findWhere(
            [
                'client_id'              => $CandidateUserObj->client_id,
                'related_object_type'    => Property::class,
                'related_object_subtype' => $related_object_subtype,
            ]
        )->first())
        {
            throw new GeneralException('No RelatedUserTypeObj');
        }

        /** @var RelatedUserRepository $RelatedUserRepositoryObj */
        $RelatedUserRepositoryObj = App::make(RelatedUserRepository::class);
        $RelatedUserRepositoryObj->create(
            [
                'user_id'              => $CandidateUserObj->id,
                'related_object_id'    => $property_id,
                'related_user_type_id' => $RelatedUserTypeObj->id,
            ]
        );
    }

    /**
     * @param $property_code
     * @return Property
     * @throws GeneralException
     */
    public function findWithPropertyCode($property_code)
    {
        if ( ! $property_code)
        {
            throw new GeneralException('Property not found');
        }
        $results = DB::select(
            DB::raw(
                "
                    SELECT
                        properties.id
                        FROM properties
    
                    
                        WHERE
                            properties.property_code REGEXP '[^\,]" . $property_code . "[\,$]'
                "
            )
        );
        if ( ! $results)
        {
            throw new GeneralException('Property not found');
        }
        return $this->find($results[0]->id);
    }

    /**
     *
     * @return array
     * @throws \BadMethodCallException
     */
    public function getUserAccessiblePropertyObjArr($user_id)
    {
        $UserObj = User::find($user_id);


        $PropertyResponseObjArr = [];
        $RelatedUserTypesObjArr =
            \DB::table('vw_relatedUsers')
               ->where(
                   [
                       [
                           'client_id',
                           '=',
                           $UserObj->client_id,
                       ],
                       [
                           'related_object_type',
                           '=',
                           Property::class,
                       ],
                   ]
               )
               ->get()
               ->each(function ($RelatedUser) use (&$RelatedUserTypesObjArr)
               {
                   $RelatedUser->users = json_decode($RelatedUser->users, true) ?? null;
               });

        $AssetTypeObjArr =
            DB::table('asset_types')
              ->where('client_id', '=', $UserObj->client_id)
              ->get();

        DB::table('access_lists')
          ->join('access_list_users', 'access_lists.id', '=', 'access_list_users.access_list_id')
          ->join('access_list_properties', 'access_lists.id', '=', 'access_list_properties.access_list_id')
          ->join('properties', 'access_list_properties.property_id', '=', 'properties.id')
          ->select('properties.*')
          ->where('access_list_users.user_id', '=', $UserObj->id)
          ->get()
          ->each(function ($PropertyObj) use ($RelatedUserTypesObjArr, &$PropertyResponseObjArr, $AssetTypeObjArr)
          {
              $PropertyObj->{'relatedUserTypes'} =
                  $RelatedUserTypesObjArr
                      ->where('related_object_id', '=', $PropertyObj->id)
                      ->mapWithKeys(function ($RelatedUser)
                      {
                          return ['RelatedUserType_' . $RelatedUser->id => $RelatedUser];
                      })
                      ->all();

              $PropertyObj->{'assetType'} =
                  $AssetTypeObjArr->where('id', '=', $PropertyObj->asset_type_id)
                                  ->first();

              $PropertyObj->active_status_date =
                  ModelDateFormatterTrait::perhaps_format_date($PropertyObj->active_status_date);

              $PropertyObj->longitude         = (float) $PropertyObj->longitude;

              $PropertyObj->latitude          = (float) $PropertyObj->latitude;

              $PropertyObj->year_built        =
                  is_numeric($PropertyObj->year_built) ? $PropertyObj->year_built : null;

              $PropertyObj->custom_attributes =
                  json_decode($PropertyObj->custom_attributes, true);

              $PropertyObj->acquisition_date  =
                  ModelDateFormatterTrait::perhaps_format_date($PropertyObj->acquisition_date);

              $PropertyObj->config_json       =
                  json_decode($PropertyObj->config_json, true);

              $PropertyObj->image_json        =
                  json_decode($PropertyObj->image_json, true);

              $PropertyObj->created_at        =
                  ModelDateFormatterTrait::perhaps_format_date($PropertyObj->created_at);

              $PropertyObj->updated_at        =
                  ModelDateFormatterTrait::perhaps_format_date($PropertyObj->updated_at);

              $PropertyObj->model_name        = Property::class;

              $PropertyResponseObjArr['PropertySlim_' . $PropertyObj->id] = $PropertyObj;
          });

        return collect($PropertyResponseObjArr);

    }

    /**
     * @param $id
     * @param array $columns
     * @return Property
     */
    public function find($id, $columns = ['*'])
    {
        return parent::find($id);
    }
}
