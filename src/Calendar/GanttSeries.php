<?php

namespace App\Calendar;

class GanttSeries
{
    private ?string $id = null;

    private ?string $label = null;

    private array $events = [];

    private array $orderedEvents = [];

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getEvents(): array
    {
        return $this->events;
    }

    /**
     * order events by start date
     */
    public function getOrderedEvents(): array
    {
        if ([] !== $this->getEvents() && [] === $this->orderedEvents) {
            $this->orderedEvents = $this->getEvents();
            usort($this->orderedEvents, fn($a, $b) => $a->getStartAt() <=> $b->getStartAt());
        }

        return $this->orderedEvents;
    }

    public function addEvent(GanttEvent $event): self
    {
        $this->events[] = $event;

        return $this;
    }
}
