<?php namespace Permit\Event;

class SilentBus implements DispatcherInterface, DistributorInterface
{

    /**
     * {@inheritdoc}
     *
     * @param string $event The event name
     * @param array $payload The event parameters
     * @param bool $halt Stop propagation on trueish return values
     * @return mixed
     **/
    public function fire($event, $payload=[], $halt=false){}

    /**
     * {@inheritdoc}
     *
     * @param mixed $events (string|array)
     * @param callable $listener
     * @param int $priority
     * @return void
     **/
    public function listen($events, $listener, $priority=0){}

}