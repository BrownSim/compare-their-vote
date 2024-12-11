<?php

namespace App\Calendar;

class Calendar
{
    private ?\DateTimeInterface $startAt = null;

    private ?\DateTimeInterface $endAt = null;

    /** @var CalendarEvent[] */
    private array $events = [];

    private array $sortedEvents = [];

    public function addEvent(CalendarEvent $event): self
    {
        $this->events[] = $event;

        return $this;
    }

    public function setStartAt(?\DateTimeInterface $startAt): self
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function setEndAt(?\DateTimeInterface $endAt): self
    {
        $this->endAt = $endAt;

        return $this;
    }

    /**
     * Render a formated array for display a calendar with missing days at the beginning and end of months
     *
     * @return array
     */
    public function render(): array
    {
        $calendar = $this->prepareEvents();
        $render = [];

        foreach ($calendar as $year => $yearData) {
            foreach ($yearData as $month => $monthData) {
                $firstDay = $monthData[array_key_first($monthData)];
                $lastDay = $monthData[array_key_last($monthData)];

                $currentMonthRender = array_merge($this->getDaysFromStartOfWeek($firstDay['date']), $monthData);
                $daysToEndWeek = $this->getDaysToEndOfWeek($lastDay['date']);
                $render[$year][$month] = array_merge($currentMonthRender, $daysToEndWeek);
            }
        }

        return $render;
    }

    private function getDatesFromRanges(\DateTimeInterface $from, \DateTimeInterface $to): \DatePeriod
    {
        $interval = new \DateInterval('P1D');

        return new \DatePeriod($from, $interval, $to);
    }

    private function prepareEvents(): array
    {
        $calendar = [];
        $this->setStartAt(new \DateTime($this->startAt->format('Y-m-1')));
        $this->setEndAt(new \DateTime($this->endAt->format('Y-m-31')));

        foreach ($this->events as $event) {
            $eventDates = $this->getDatesFromRanges($event->getStartAt(), $event->getEndAt());
            if (iterator_count($eventDates) === 0) {
                $year = $event->getStartAt()->format('Y');
                $month = $event->getStartAt()->format('n');
                $day = $event->getStartAt()->format('d');

                $this->sortedEvents[$year][$month][$day][] = $event;
            } else {
                foreach ($eventDates as $date) {
                    $year = $date->format('Y');
                    $month = $date->format('n');
                    $day = $date->format('d');

                    $this->sortedEvents[$year][$month][$day][] = $event;
                }
            }
        }

        $dates = $this->getDatesFromRanges($this->startAt, $this->endAt);
        foreach ($dates as $date) {
            $year = $date->format('Y');
            $month = $date->format('n');
            $day = $date->format('d');

            $calendar[$year][$month][] = [
                'date' => $date,
                'events' => $this->sortedEvents[$year][$month][$day] ?? null
            ];
        }

        return $calendar;
    }

    private function getDaysFromStartOfWeek(\DateTimeInterface $dateTime): array
    {
        $monday = (clone $dateTime)->modify('last monday');

        return $this->getDaysMissingFormated($monday, $dateTime);
    }

    private function getDaysToEndOfWeek(\DateTimeInterface $dateTime): array
    {
        // DatePeriod include first given day, we don't need it, add 1 day to fix it
        $from = (clone $dateTime)->modify('+ 1 day');
        $to = (clone $dateTime)->modify('Sunday this week');

        // datePeriod does not include last day, add + 1 ti fix it
        $to->modify('+1 day');

        $days = $this->getDaysMissingFormated($from, $to);

        return $days;
    }

    private function getDaysMissingFormated(\DateTimeInterface $from, \DateTimeInterface $to)
    {
        $days = [];
        foreach ($this->getDatesFromRanges($from, $to) as $date) {
            $year = $date->format('Y');
            $month = $date->format('n');
            $day = $date->format('d');

            $days[] = ['date' => $date, 'events' => $this->sortedEvents[$year][$month][$day] ?? null];
        }

        return $days;
    }
}
