<?php namespace Permit\Event;

trait BusHolderTrait{

    /**
     * @var \Permit\Event\BusInterface
     **/
    protected $eventBus;

    /**
     * Fire an event with payload $payload. If $halt is set to true
     * stop propagation if some subscriber return something trueish
     *
     * @param string $event The event name
     * @param array $payload The event parameters
     * @param bool $halt Stop propagation on trueish return values
     * @return mixed
     **/
    public function fire($event, $payload=[], $halt=false)
    {
        return $this->getEventBus()->fire($event, $payload, $halt);
    }

    /**
     * Fire an event if a name was passed
     *
     * @param string $event The event name
     * @param array $payload The event parameters
     * @param bool $halt Stop propagation on trueish return values
     * @return mixed
     * @see self::fire()
     **/
    public function fireIfNamed($event, $payload=[], $halt=false)
    {
        if(!$event){
            return;
        }
        return $this->getEventBus()->fire($event, $payload, $halt);
    }

    /**
     * Listen to event(s) $event
     *
     * @param mixed $events (string|array)
     * @param callable $listener
     * @param int $priority
     * @return void
     **/
    public function listen($events, $listener, $priority=0)
    {
        return $this->getEventBus()->listen($events, $listener, $priority);
    }

    /**
     * Return the eventBus
     *
     * @return \Permit\Event\BusInterface
     **/
    public function getEventBus()
    {
        if (!$this->eventBus) {
            $this->eventBus = new SilentBus;
        }
        return $this->eventBus;
    }

    /**
     * Set the event bus
     *
     * @param \Permit\Event\BusInterface $eventBus
     * @return self
     **/
    public function setEventBus(BusInterface $eventBus)
    {
        $this->eventBus = $eventBus;
        return $this;
    }

}