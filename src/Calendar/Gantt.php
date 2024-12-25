<?php

namespace App\Calendar;

class Gantt extends AbstractCalendar
{
    private ?\DatePeriod $years = null;

    private ?\DatePeriod $days = null;

    private array $daysByYear = [];

    private array $daysByYearAndMonth = [];

    /** @var GanttSeries[] */
    private array $series = [];

    public function addSeries(GanttSeries $series): self
    {
        $this->series[] = $series;

        return $this;
    }

    public function getSeries(): array
    {
        return $this->series;
    }

    public function getDays(): \DatePeriod
    {
        if (null === $this->days) {
            $this->days = $this->generateDaysRange();
        }

        return $this->days;
    }

    public function getYears(): \DatePeriod
    {
        if (null === $this->years) {
            $interval = new \DateInterval('P1Y');
            $this->years = new \DatePeriod($this->startAt, $interval, (clone $this->endAt)->modify('+ 1 year'));
        }

        return $this->years;
    }

    public function getNbDays(): int
    {
        return iterator_count($this->getDays());
    }

    public function getDaysByYear(): array
    {
        if ([] === $this->daysByYear) {
            foreach ($this->getDays() as $day) {
                $this->daysByYear[$day->format('Y')] = isset($this->daysByYear[$day->format('Y')])
                    ? $this->daysByYear[$day->format('Y')] + 1
                    : 1
                ;
            }
        }

        return $this->daysByYear;
    }

    public function getDaysByYearAndMonth(): array
    {
        if ([] === $this->daysByYearAndMonth) {
            foreach ($this->getDays() as $day) {
                $this->daysByYearAndMonth[$day->format('Y')][$day->format('m')] =
                    isset($this->daysByYearAndMonth[$day->format('Y')][$day->format('m')])
                    ? $this->daysByYearAndMonth[$day->format('Y')][$day->format('m')] + 1
                    : 1
                ;
            }
        }

        return $this->daysByYearAndMonth;
    }

    private function generateDaysRange(): \DatePeriod
    {
        return $this->getDatesFromRanges($this->startAt, $this->endAt);
    }
}
