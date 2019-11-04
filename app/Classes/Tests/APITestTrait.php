<?php

namespace App\Waypoint\Tests;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Validator;
use Illuminate\Http\JsonResponse;

/**
 * Class ApiTestTrait
 * @package App\Waypoint\Tests
 *
 * @codeCoverageIgnore
 */
trait ApiTestTrait
{
    /** @var  string */
    private $method;

    /** @var  string */
    private $uri;

    /** @var  array */
    private $data;

    /** @var  array */
    private $headers;

    private $JSONResponse;

    /** @var  array */
    private $JSONContent;

    /**
     * @param string $actualModel
     * @throws GeneralException
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \PHPUnit\Framework\Exception
     */
    public function assertApiListResponse($actualModel, $minimum_number_objects_required = 1, $absolute_number_objects_required = null)
    {
        $this->assertApiSuccess();

        if ($absolute_number_objects_required)
        {
            $this->assertTrue($absolute_number_objects_required = count($this->getDataObjectArr()));
        }
        $this->assertTrue($minimum_number_objects_required <= count($this->getDataObjectArr()));

        $i = 0;
        foreach ($this->getDataObjectArr() as $responseDataElementName => $responseDataElement)
        {
            $this->assertRegExp("/^[A-z]*_\d*$/", $responseDataElementName, $responseDataElementName . ' is not a valid id');
            $this->assertTrue(is_array($responseDataElement), 'Response element is not an array');

            if (env('JSON_VALIDATION_ENABLED'))
            {
                /** @var Validator $ValidatorObj
                 *
                 * NOTE NOTE NOTE
                 * this is laravel Illuminate\Validation validation, not json-schema validation
                 */
                $ValidatorObj = \Validator::make($responseDataElement, $actualModel::get_model_rules(null, $responseDataElement['id']));
                if ($fails = $ValidatorObj->fails())
                {
                    $this->assertFalse(
                        $fails,
                        'Validation fails on object ' . $actualModel . $responseDataElement['id']
                    );
                }
            }
            $this->assertTrue(isset($responseDataElement['model_name']));
            $this->assertTrue(isset($responseDataElement['id']));
            $this->assertEquals($responseDataElement['model_name'], $actualModel);
            foreach ($responseDataElement as $responseDataElementElement)
            {
                $this->assertFalse(is_object($responseDataElementElement));
            }
            if ($i++ > config('waypoint.unittest_loop'))
            {
                break;
            }
        }
        $this->generateMock();
    }

    /**
     * @param array $response_status_code_arr
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function assertApiFailure($response_status_code_arr = [400, 500])
    {
        if ($this->getJSONContent()['message'] == 'Route unavailable')
        {
            throw new GeneralException('Api failure due to Route unavailable');
        }
        $response_status_code_arr[] = 404;
        $response_status_code_arr[] = 400;
        $response_status_code_arr[] = 500;
        $this->assertTrue($this->getJSONResponse() !== null);
        $this->assertTrue($this->getJSONResponse()->getStatusCode() !== null);
        if ( ! in_array(
            $this->getJSONResponse()->getStatusCode(),
            $response_status_code_arr
        )
        )
        {
            $string = isset($this->getJSONContent()['message']) ? 'Last Message :' . $this->getJSONContent()['message'] : null;
            $string .= '---' . isset($this->getJSONContent()['metadata']) ? print_r($this->getJSONContent()['metadata'], true) : ' no metadata ' . '---' . PHP_EOL;
            echo $string;
        }
        $this->assertTrue(
            in_array(
                $this->getJSONResponse()->getStatusCode(),
                $response_status_code_arr
            ),
            'Actual status code of ' . $this->getJSONResponse()->getStatusCode() . ' not found in expected status codes: ' . print_r($response_status_code_arr, true)
        );

        foreach ($response_status_code_arr as $response_status_code)
        {
            if ($this->getJSONResponse()->getStatusCode() == $response_status_code)
            {
                if ($response_status_code != 500)
                {
                    $this->assertTrue(is_array($this->getJSONContent()['data']));
                    $this->assertTrue(is_array($this->getJSONContent()['errors']));
                    $this->assertTrue(strlen($this->getJSONContent()['message']) > 3);
                    $this->assertTrue(is_array($this->getJSONContent()['metadata']));
                    $this->assertTrue(is_array($this->getJSONContent()['warnings']));
                    $this->assertFalse($this->getJSONContent()['success']);
                }
                return;
            }
        }
        $this->assertTrue(false, 'response code never found');
    }

    public function assertApiSuccess()
    {
        if (200 !== (integer) $this->getJSONResponse()->getStatusCode())
        {
            /**
             * we do this so at the very least, $this->getJSONContent()['message'] will appear in the
             * codeship sysout to provide some minimal clue as to what happened
             */
            $string = isset($this->getJSONContent()['message']) ? 'Last Message :' . $this->getJSONContent()['message'] : null;
            $string .= '---' . isset($this->getJSONContent()['metadata']) ? print_r($this->getJSONContent()['metadata'], true) : ' no metadata ' . '---' . PHP_EOL;
            echo $string;
        }
        $this->assertEquals(200, $this->getJSONResponse()->getStatusCode());
        $this->assertTrue($this->getJSONContent()['success']);
        $this->generateMock();
    }

    /**
     * @param $audit_arr
     * @param string $case
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function assertAuditIsValid($audit_arr, $case = 'unknown')
    {
        /**
         * see http://www.laravel-auditing.com/docs/4.0/audit-presentation
         */
        if ($case == 'created')
        {
            $this->assertTrue(is_array($audit_arr));
            $this->assertTrue(is_array($audit_arr['update_history']));
            $this->assertTrue(is_array($audit_arr['update_history'][0]['Metadata']));
            $this->assertTrue(is_array($audit_arr['update_history'][0]['Modified']));
            $this->assertTrue(count($audit_arr['update_history']) >= 1);
            $this->assertEquals($audit_arr['update_history'][0]['Metadata']['audit_event'], 'created');

            foreach ($audit_arr['update_history'] as $update_history_arr)
            {
                $this->assertTrue(isset($update_history_arr['Metadata']['user_id']));
                $this->assertTrue(isset($update_history_arr['Metadata']['audit_created_at']));
            }
        }
        elseif ($case == 'updated')
        {
            $this->assertTrue(is_array($audit_arr));
            $this->assertTrue(is_array($audit_arr['update_history']));
            $this->assertTrue(is_array($audit_arr['update_history'][0]['Metadata']));
            $this->assertTrue(is_array($audit_arr['update_history'][0]['Modified']));
            $this->assertTrue(is_array($audit_arr['update_history'][1]['Metadata']));
            $this->assertTrue(is_array($audit_arr['update_history'][1]['Modified']));
            $this->assertTrue(count($audit_arr['update_history']) >= 2);
            $this->assertEquals($audit_arr['update_history'][0]['Metadata']['audit_event'], 'created');
            $this->assertEquals($audit_arr['update_history'][1]['Metadata']['audit_event'], 'updated');

            foreach ($audit_arr['update_history'] as $update_history_arr)
            {
                $this->assertTrue(isset($update_history_arr['Metadata']['user_id']));
                $this->assertTrue(isset($update_history_arr['Metadata']['audit_created_at']));
            }
        }
        elseif ($case == 'unknown')
        {
            $this->assertTrue(is_array($audit_arr));
            $this->assertTrue(is_array($audit_arr['update_history']));
            $this->assertTrue(is_array($audit_arr['update_history'][0]['Metadata']));
            $this->assertTrue(is_array($audit_arr['update_history'][0]['Modified']));
            $this->assertTrue(count($audit_arr['update_history']) == 1);
            $this->assertEquals($audit_arr['update_history'][0]['Metadata']['audit_event'], 'created');

            foreach ($audit_arr['update_history'] as $update_history_arr)
            {
                $this->assertTrue(isset($update_history_arr['Metadata']['user_id']));
                $this->assertTrue(isset($update_history_arr['Metadata']['audit_created_at']));
            }
        }
        else
        {
            $this->assertTrue(false, 'invalid case passed' . $case);
        }
    }

    /**
     * @throws GeneralException
     */
    private function generateMock()
    {
        if (config('waypoint.unittest_generate_mocks', false))
        {
            if (strtoupper($this->getMethod()) !== 'DELETE')
            {
                if ( ! isset($this->getJSONContent()['data']) || ! is_array($this->getJSONContent()['data']))
                {
                    throw new GeneralException('un-mockable model' . __LINE__);
                }

                if ( ! $unittest_mocks_folder = config('waypoint.unittest_mocks_folder'))
                {
                    $unittest_mocks_folder = storage_path('exports') . '/mocks';
                }
                if ( ! file_exists($unittest_mocks_folder))
                {
                    mkdir($unittest_mocks_folder, 0777, true);
                }

                $seq = 0;
                do
                {
                    $mock_file_name = $unittest_mocks_folder . '/' . $this->getMethod() . str_replace(
                            '/', '_', explode("?", $this->getUri(), 2)[0]
                        ) . '.' . $seq++ . '.json';

                    $mock_file_name = preg_replace("/_\d*[_\.]/", '_999_', $mock_file_name, -1);

                } while (file_exists($mock_file_name));

                $fh = fopen($mock_file_name, 'w') or die("can't open file");
                fwrite($fh, json_encode($this->getJSONContent(), JSON_PRETTY_PRINT));
                fclose($fh);
            }
        }
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param mixed $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @return mixed
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param mixed $uri
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }

    /**
     * Visit the given URI with a JSON request.
     *
     * @param string $method
     * @param string $uri
     * @param array $data
     * @param array $headers
     * @return JsonResponse
     */
    public function json($method, $uri, array $data = [], array $headers = [])
    {
        $this->setMethod($method);
        $this->setUri($uri);
        $this->setData($data);
        $this->setHeaders($headers);

        /** @noinspection PhpUndefinedMethodInspection */
        /** @noinspection PhpParamsInspection */
        $this->setJSONResponse(parent::json($method, $uri, $data, $headers)->baseResponse);

        echo('x');

        return $this->getJSONResponse();
    }

    /**
     * @return mixed|JsonResponse
     */
    public function getJSONResponse()
    {
        return $this->JSONResponse;
    }

    /**
     * @param \Illuminate\Http\JsonResponse $JSONResponse
     */
    public function setJSONResponse($JSONResponse)
    {
        $this->JSONResponse = $JSONResponse;
        /** @noinspection PhpUndefinedFieldInspection */
        if ($this->JSONResponse->getStatusCode() !== 500)
        {
            $this->setJSONContent(json_decode($JSONResponse->getContent(), true));
            /**
             * $this->assertEquals(0, json_last_error());
             * @todo See HER-2403
             */
        }
    }

    /**
     * @return array
     */
    public function getJSONContent()
    {
        return $this->JSONContent;
    }

    /**
     * @return mixed
     */
    public function getFirstDataObject()
    {
        if (isset($this->getJSONContent()['data']['id']))
        {
            return $this->getJSONContent()['data'];
        }
        return reset($this->getJSONContent()['data']);
    }

    /**
     * @return mixed
     */
    public function getDataObjectArr()
    {
        return $this->getJSONContent()['data'];
    }

    /**
     * @param array $JSONContent
     */
    public function setJSONContent($JSONContent)
    {
        $this->JSONContent = $JSONContent;
    }
}