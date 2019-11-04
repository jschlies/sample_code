<?php

namespace App\Waypoint;

use \Gelf\Publisher as Gelf_Publisher;
use \Gelf\Transport\TcpTransport as Gelf_Transport_TcpTransport;

class Graylog
{
    /** @var Gelf_Publisher */
    public $publisher = null;

    public function __construct()
    {
        $this->publisher = $this->getPublisher();
    }

    /**
     * @return Gelf_Publisher
     */
    public function getPublisher()
    {
        /**
         * We need a transport - UDP via port 12201 is standard.
         * @var Gelf_Transport_TcpTransport $transport
         */
        $transport = new Gelf_Transport_TcpTransport(
            config('services.graylog.host', 'graylog.waypointbuilding.com'),
            config('graylog_port', '12201')
        );
        /**
         * While the UDP transport is itself a publisher, we wrap it in a real Publisher for convenience.
         * A publisher allows for message validation before transmission, and also supports sending
         * messages to multiple backends at once.
         * @var Gelf_Publisher $publisher
         */
        $publisher = new Gelf_Publisher();
        $publisher->addTransport($transport);

        return $publisher;
    }
}
