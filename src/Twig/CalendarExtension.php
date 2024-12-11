<?php

namespace App\Twig;

use App\Calendar\Calendar;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CalendarExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('calendar_render', [$this, 'renderCalendar'], ['is_safe' => ['html'], 'needs_environment' => true]),
        ];
    }

    public function renderCalendar(Environment $env, Calendar $calendar): string
    {
        return $env->render('common/calendar/yeah.html.twig', ['calendar' => $calendar->render()]);
    }
}
