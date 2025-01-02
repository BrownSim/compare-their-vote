<?php

namespace App\Manager;

use App\Calendar\Calendar;
use App\Calendar\CalendarEvent;
use App\Calendar\Gantt;
use App\Calendar\GanttEvent;
use App\Calendar\GanttSeries;
use App\Entity\MemberVote;

class CalendarManager
{
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

    public function generateAbsenceGantForMembers(array $members): Gantt
    {
        $from = $to = null;
        $gantt = new Gantt();

        foreach ($members as $member) {
            $ganttSeries = (new GanttSeries())
                ->setId($member->getId())
                ->setLabel($member->getFirstName() . ' ' . $member->getLastName())
            ;

            $missingFrom = null;
            $missingTo = null;
            $missingEvents = [];
            $lastVoteDate = null;
            foreach ($member->getVotes() as $vote) {
                $date = $vote->getDate();

                if (null === $from || $from > $date) {
                    $from = $date;
                }

                if (null === $to || $to < $date) {
                    $to = $date;
                }

                if (null === $lastVoteDate || $lastVoteDate < $date) {
                    $lastVoteDate = $date;
                }

                if (MemberVote::VOTE_DID_NOT_VOTE === $vote->getResult()) {
                    if (null === $missingFrom) {
                        $missingFrom = $date->setTime(00, 00, 00);
                        $missingTo = $date->setTime(23, 59, 59);

                        continue;
                    }

                    $missingTo = $date->setTime(23, 59, 59);
                }

                if (MemberVote::VOTE_DID_NOT_VOTE !== $vote->getResult() && null !== $missingFrom) {
                    $missingEvents = $this->missingEventEnd($missingFrom, $missingTo, $missingEvents);
                    $missingFrom = null;
                    $missingTo = null;
                }
            }

            $votes = $member->getVotes();
            if (MemberVote::VOTE_DID_NOT_VOTE === array_pop($votes)->getResult()) {
                $missingEvents = $this->missingEventEnd($missingFrom, $missingTo, $missingEvents);
            }

            $mergedEvents = [];

            // merge consecutive missing days, we will display it in two-day event
            // use pointer to prevent array copy, need it for unset
            foreach ($missingEvents as &$missingEvent) {
                $eventFrom = $missingEvent['from'];
                $eventTo = $missingEvent['to'];
                $dtFrom = (clone $missingEvent['from'])->setTime(00, 00, 00);

                while (isset($missingEvents[$dtFrom->format('Y-m-d')]) || $dtFrom <= $eventTo) {
                    if (isset($missingEvents[$dtFrom->format('Y-m-d')]) &&
                        $missingEvents[$dtFrom->format('Y-m-d')]['to'] >= $eventTo) {
                        $eventTo = $missingEvents[$dtFrom->format('Y-m-d')]['to'];

                        unset($missingEvents[$dtFrom->format('Y-m-d')]);
                    }

                    $dtFrom = $dtFrom->modify('+ 1 day');
                }

                $mergedEvents[] = ['from' => $eventFrom, 'to' => $eventTo];
            }

            unset($missingEvent);

            foreach ($mergedEvents as $missingEvent) {
                $event = (new GanttEvent())
                    ->setStartAt($missingEvent['from'])
                    ->setEndAt($missingEvent['to'])
                    ->setType('vote')
                ;

                $ganttSeries->addEvent($event);
            }

            $gantt->addSeries($ganttSeries);
        }

        $gantt->setStartAt($from);
        $gantt->setEndAt($to);

        return $gantt;
    }

    private function missingEventEnd(\DateTimeInterface $missingFrom, \DateTimeInterface $missingTo, array $missingEvents): array
    {
        $formatedDate = $missingFrom->format('Y-m-d');

        // we will display all dates with missing vote
        // we need one event by day, this trick prevent multiple missing events for one date
        if (isset($missingEvents[$formatedDate]) && $missingEvents[$formatedDate]['to'] < $missingTo) {
            $missingEvents[$formatedDate]['to'] = $missingTo;
        } elseif (!isset($missingEvents[$formatedDate])) {
            $missingEvents[$formatedDate]['from'] = $missingFrom;
            $missingEvents[$formatedDate]['to'] = $missingTo;
        }

        return $missingEvents;
    }
}
