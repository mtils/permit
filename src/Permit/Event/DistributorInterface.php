<?php namespace Permit\Event;

interface DistributorInterface{

    /**
     * Listen to event(s) $event
     *
     * @param mixed $events (string|array)
     * @param callable $listener
     * @param int $priority
     * @return void
     **/
    public function listen($events, $listener, $priority=0);

}