<?php

namespace App\Waypoint;

use App\Waypoint\Exceptions\GeneralException;
use \Gelf\Message as Gelf_Message;
use \Gelf\Publisher as Gelf_Publisher;
use \Gelf\Transport\TcpTransport as Gelf_Transport_TcpTransport;
use \Psr\Log\LogLevel as Psr_Log_LogLevel;
use App;
use App\Waypoint\Events\RepositoryEventBase;
use App\Waypoint\Exceptions\DeploymentException;
use InfyOm\Generator\Common\BaseRepository;
use Prettus\Repository\Events\RepositoryEntityDeleted;

class Repository extends BaseRepository
{
    /** @var bool */
    public $suppress_events = false;

    /** @var Gelf_Message */
    private $GraylogMessage;

    /**
     * @return boolean
     */
    public function isSuppressEvents()
    {
        return $this->suppress_events;
    }

    /**
     * @param $suppress_events
     * @return $this
     */
    public function setSuppressEvents($suppress_events)
    {
        $this->suppress_events = $suppress_events;
        return $this;
    }

    /**
     * @throws \Exception
     */
    public function model()
    {
        throw new GeneralException('BaseRepository::model() is not callable. This class needs to be extended');
    }

    /**
     * Delete a entity in repository by id
     *
     * @param int $id
     * @return bool
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function delete($id)
    {
        $this->applyScope();

        $temporarySkipPresenter = $this->skipPresenter;
        $this->skipPresenter(true);

        $model = $this->find($id);

        $originalModel = clone $model;

        $this->skipPresenter($temporarySkipPresenter);
        $this->resetModel();

        $deleted = $model->delete();

        $model = null;
        unset($model);

        if ( ! $this->isSuppressEvents())
        {
            $RepositoryEntityDeletedObj =
                new RepositoryEntityDeleted(
                    clone $this, $originalModel
                );

            event(
                $RepositoryEntityDeletedObj,
                [
                    'event_trigger_message'        => 'Triggered at ' . __CLASS__ . ':' . __LINE__,
                    'event_trigger_id'             => waypoint_generate_uuid(),
                    'event_trigger_class'          => self::class,
                    'event_trigger_class_instance' => get_class($this),
                    'event_trigger_object_class'   => get_class($RepositoryEntityDeletedObj),
                    'event_trigger_absolute_class' => __CLASS__,
                    'event_trigger_file'           => __FILE__,
                    'event_trigger_line'           => __LINE__,
                ]
            );
        }

        $originalModel = null;
        unset($originalModel);

        return $deleted;
    }

    /**
     * @param string $class_name
     * @return Repository
     * @throws DeploymentException
     */
    public function makeRepository($class_name)
    {
        $RepositoryObj = App::make($class_name);
        if ( ! $RepositoryObj instanceof Repository)
        {
            throw new DeploymentException();
        }
        $RepositoryObj->setSuppressEvents($this->suppress_events);
        return $RepositoryObj;
    }

    /**
     * @param Model $Object
     * @return bool
     *
     * This is needed for models such as ClientLogo and PropertyDetail. Remember that only certain
     * repository models have events defined. RepositoryEventBase::getEnabledModelRepositoryEvents() is that list.
     * See (for example) AccessListPropertyRepositoryBase->create().
     */
    protected function ObjectEnabledForEvents(Model $Object)
    {
        foreach (RepositoryEventBase::getEnabledModelRepositoryEvents() as $candidate_class)
        {
            if (is_a($Object, $candidate_class))
            {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $level
     * @param $short_message
     * @param null $long_message
     * @throws \RuntimeException
     */
    protected function logToGraylog($level, $short_message, $long_message = null)
    {
        $transport = new Gelf_Transport_TcpTransport(
            config('services.graylog.host', 'graylog.waypointbuilding.com'),
            config('graylog_port', '12201')
        );
        // While the UDP transport is itself a publisher, we wrap it in a real Publisher for convenience.
        // A publisher allows for message validation before transmission, and also supports sending
        // messages to multiple backends at once.
        $publisher = new Gelf_Publisher();
        $publisher->addTransport($transport);
        // Now we can create custom messages and publish them

        $this->GraylogMessage = new Gelf_Message();
        $this->GraylogMessage->setShortMessage($short_message)
                             ->setLevel(Psr_Log_LogLevel::ALERT)
                             ->setFullMessage($long_message ?: $short_message)
                             ->setFacility('hermes')
                             ->setHost(gethostname());
        $this->GraylogMessage->setAdditional("file", __FILE__);
        $this->GraylogMessage->setAdditional("line", __LINE__);
        $this->GraylogMessage->setAdditional("environment", env('APP_ENV'));
        $publisher->publish($this->GraylogMessage);

    }

    /**
     * Find data by multiple values in one field
     *
     * @param       $field
     * @param array $values
     * @param array $columns
     *
     * @return mixed
     */
    public function findWhereIn($field, array $values, $columns = ['*'])
    {
        return collect_waypoint(parent::findWhereIn($field, $values, $columns));
    }

    /**
     * Retrieve all data of repository
     *
     * @param array $columns
     *
     * @return mixed
     */
    public function all($columns = ['*'])
    {
        return collect_waypoint(parent::all($columns));
    }
}
