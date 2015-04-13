<?php namespace Permit\Event;

interface DispatcherInterface
{

    /**
     * Fire an event with payload $payload. If $halt is set to true
     * stop propagation if some subscriber return something trueish
     *
     * @param string $event The event name
     * @param array $payload The event parameters
     * @param bool $halt Stop propagation on trueish return values
     * @return mixed
     **/
    public function fire($event, $payload=[], $halt=false);

}