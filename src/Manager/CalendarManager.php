<?php

namespace App\Manager;

use App\Calendar\Calendar;
use App\Calendar\CalendarEvent;
use App\Entity\MemberVote;
use Doctrine\ORM\EntityManagerInterface;

class CalendarManager
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function generateAbsenceCalendarForMemberVotes($votes): Calendar
    {
        $dates = [];
        $calendar = new Calendar();

        foreach ($votes as $vote) {
            $dates[$vote['vote_date']->getTimestamp()] = $vote;
        }

        //find first and last dates
        ksort($dates);
        $from = (new \DateTime())->setTimestamp(array_key_first($dates));
        $to = (new \DateTime())->setTimestamp(array_key_last($dates));

        $missingFrom = null;
        foreach ($dates as $date) {
            if (MemberVote::VOTE_DID_NOT_VOTE === $date['vote_value']) {
                if (null === $missingFrom) {
                    $missingFrom = $date['vote_date'];
                    $missingTo = $date['vote_date'];

                    continue;
                }

                $missingTo = $date['vote_date'];
            }

            if (MemberVote::VOTE_DID_NOT_VOTE !== $date['vote_value'] && null !== $missingFrom) {
                $event = (new CalendarEvent());
                $event->setStartAt($missingFrom);
                $event->setEndAt($missingTo);
                $calendar->addEvent($event);

                $missingFrom = null;
                $missingTo = null;
            }
        }

        if (null !== $missingFrom && null !== $missingTo) {
            $missingTo = $to;

            $event = (new CalendarEvent());
            $event->setStartAt($missingFrom);
            $event->setEndAt($missingTo);
            $calendar->addEvent($event);
        }

        $calendar->setStartAt($from);
        $calendar->setEndAt($to);

        return $calendar;
    }
}
