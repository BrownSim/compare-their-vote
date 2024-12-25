<?php

namespace App\Calendar;

class AbstractCalendar
{
    protected ?\DateTimeInterface $startAt = null;

    protected ?\DateTimeInterface $endAt = null;

    public function setStartAt(?\DateTimeInterface $startAt): self
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getStartAt(): ?\DateTimeInterface
    {
        return $this->startAt;
    }

    public function setEndAt(?\DateTimeInterface $endAt): self
    {
        $this->endAt = $endAt;

        return $this;
    }

    public function getEndAt(): ?\DateTimeInterface
    {
        return $this->endAt;
    }

    protected function getDatesFromRanges(\DateTimeInterface $from, \DateTimeInterface $to): \DatePeriod
    {
        $interval = new \DateInterval('P1D');

        return new \DatePeriod($from, $interval, $to);
    }
}
