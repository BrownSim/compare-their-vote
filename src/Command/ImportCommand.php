<?php

declare(strict_types=1);

namespace App\Command;

use App\Api\HowTheyVote\Client;
use App\Entity\PoliticalGroup;
use App\Entity\Member;
use App\Entity\MemberVote;
use App\Entity\Session;
use App\Entity\Vote;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:import', description: 'Import data from api')]
class ImportCommand extends Command
{
    private const PAGE_SIZE = 200;

    private readonly ProgressBar $progressBarVote;

    private array $politicalGroup = [];

    private array $members = [];

    private array $votes = [];

    public function __construct(
        private readonly Client $client,
        private readonly EntityManagerInterface $em,
        ?string $name = null
    ) {
        parent::__construct($name);

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->loadData();

        $votes = $this->getAllVotes();
        $this->progressBarVote = new ProgressBar($output->section());
        $this->progressBarVote->setMaxSteps(count($votes));

        $iterator = 1;

        foreach ($this->getAllVotes() as $vote) {
            $this->createOrFindVote($vote);
            $this->progressBarVote->advance();
            $iterator++;

            if ($iterator === 100) {
                $this->em->flush();
                $iterator = 1;
            }
        }

        $this->em->flush();

        $this->updateSession();
        $this->progressBarVote->finish();

        return Command::SUCCESS;
    }

    /**
     * Preload data for optimize import speed
     */
    private function loadData(): void
    {
        foreach ($this->em->getRepository(Member::class)->findAll() as $member) {
            $this->members[$member->getMepId()] = $member;
        }

        foreach ($this->em->getRepository(PoliticalGroup::class)->findAll() as $group) {
            $this->politicalGroup[$group->getCode()] = $group;
        }

        foreach ($this->em->getRepository(Vote::class)->findAll() as $vote) {
            $this->votes[$vote->getOfficialId()] = $vote;
        }
    }

    private function getAllVotes(): array
    {
        $votes = $this->client->listVotes(1, self::PAGE_SIZE);
        $total = $votes['total'];
        $votesToMerge[] = $votes['results'];

        $nbIteration = (int) ceil($total / self::PAGE_SIZE);
        foreach (range(2, $nbIteration) as $currentIteration) {
            $votesToMerge[] = $this->client->listVotes($currentIteration, self::PAGE_SIZE)['results'];
        }

        $votes = array_reverse(array_merge([], ...$votesToMerge));

        return $votes;
    }

    private function createOrFindVote(array $data): Vote
    {
        $data = $this->client->getVote((int) $data['id']);

        if (isset($this->votes[$data['id']])) {
            return $this->votes[$data['id']];
        }

        $vote = (new Vote())
            ->setOfficialId((int) $data['id'])
            ->setVoteDate(new \DateTimeImmutable($data['timestamp']))
            ->setDescription($data['description'])
            ->setReference($data['reference'])
            ->setIsFeatured($data['is_featured'])
            ->setTitle($data['display_title'])
        ;

        $this->em->persist($vote);
        $this->votes[$data['id']] = $vote;

        if (isset($data['member_votes'])) {
            foreach ($data['member_votes'] as $memberVote) {
                $member = $this->createOrFindMember($memberVote['member']);
                $memberVote = (new MemberVote())
                    ->setValue($memberVote['position'])
                    ->setMember($member)
                    ->setVote($vote)
                ;

                $this->em->persist($memberVote);
            }
        }

        return $vote;
    }

    private function createOrFindMember($data): Member
    {
        if (isset($this->members[$data['id']])) {
            return $this->members[$data['id']];
        }

        if (isset($this->politicalGroup[$data['group']['code']])) {
            $group = $this->politicalGroup[$data['group']['code']];
        } else {
            $group = (new PoliticalGroup())
                ->setCode($data['group']['code'])
                ->setLabel($data['group']['label'])
                ->setShortLabel($data['group']['short_label'])
            ;

            $this->em->persist($group);
            $this->politicalGroup[$data['group']['code']] = $group;
        }

        $member = (new Member())
            ->setMepId($data['id'])
            ->setFirstName($data['first_name'])
            ->setLastName($data['last_name'])
            ->setThumb($data['thumb_url'])
            ->setGroup($group)
        ;

        $this->em->persist($member);
        $this->members[$data['id']] = $member;

        return $member;
    }

    private function updateSession(): void
    {
        $lastSession = $this->client->getListSessions(1, 1, 'desc', Client::SESSION_STATUS_PAST);

        if (empty($lastSession['results'])) {
            $session = $this->em->getRepository(Session::class)->findOneBy(['status' => Session::SESSION_STATUS_LAST]);
            if (null !== $session) {
                $this->em->remove($session);
            }
        } else {
            $startAt = new \DateTime($lastSession['results']['0']['start_date']);
            $endDAt = new \DateTime($lastSession['results']['0']['end_date']);

            $session = $this->em->getRepository(Session::class)->findOneBy(['status' => Session::SESSION_STATUS_LAST]);
            $session = null === $session ? new Session() : $session;
            $session
                ->setStartAt($startAt)
                ->setEndAt($endDAt)
                ->setStatus(Session::SESSION_STATUS_LAST)
            ;

            if ($session->getId() === null) {
                $this->em->persist($session);
            }
        }

        $nextSession = $this->client->getListSessions(1, 10, 'desc', Client::SESSION_STATUS_UPCOMING);
        if (empty($nextSession['results'])) {
            $session = $this->em->getRepository(Session::class)->findOneBy(['status' => Session::SESSION_STATUS_NEXT]);
            if (null !== $session) {
                $this->em->remove($session);
            }
        } else {
            $startAt = new \DateTime($nextSession['results']['0']['start_date']);
            $endDAt = new \DateTime($nextSession['results']['0']['end_date']);

            $session = $this->em->getRepository(Session::class)->findOneBy(['status' => Session::SESSION_STATUS_NEXT]);
            $session = null === $session ? new Session() : $session;
            $session
                ->setStartAt($startAt)
                ->setEndAt($endDAt)
                ->setStatus(Session::SESSION_STATUS_NEXT)
            ;

            if ($session->getId() === null) {
                $this->em->persist($session);
            }
        }


        $this->em->flush();
    }
}
