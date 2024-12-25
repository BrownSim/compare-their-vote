<?php

namespace App\Twig;

use App\Calendar\Calendar;
use App\Calendar\Gantt;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CalendarExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('calendar_render', [$this, 'renderCalendar'], ['is_safe' => ['html'], 'needs_environment' => true]),
            new TwigFunction('gantt_render', [$this, 'renderGantt'], ['is_safe' => ['html'], 'needs_environment' => true]),
            new TwigFunction('gantt_daterange', [$this, 'daterange']),
        ];
    }

    public function renderCalendar(Environment $env, Calendar $calendar): string
    {
        return $env->render('common/calendar/yeah.html.twig', ['calendar' => $calendar->render()]);
    }

    public function renderGantt(Environment $env, Gantt $gantt): string
    {
        return $env->render('common/calendar/gantt.html.twig', ['gantt' => $gantt]);
    }

    public function daterange(\DateTimeInterface $from, \DateTimeInterface $to): \DatePeriod
    {
        $interval = new \DateInterval('P1D');

        return new \DatePeriod(
            $from->setTime(00, 00, 00),
            $interval,
            $to->setTime(23, 59, 59),
            \DatePeriod::INCLUDE_END_DATE
        );
    }
}
