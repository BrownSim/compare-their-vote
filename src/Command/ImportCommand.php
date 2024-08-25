<?php

declare(strict_types=1);

namespace App\Command;

use App\Api\HowTheyVote\Client;
use App\Entity\Country;
use App\Entity\GeoArea;
use App\Entity\Member;
use App\Entity\MemberVote;
use App\Entity\PoliticalGroup;
use App\Entity\PoliticalGroupVote;
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

    private array $countries = [];

    private array $geoAreas = [];

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

        foreach ($this->em->getRepository(Country::class)->findAll() as $country) {
            $this->countries[$country->getCode()] = $country;
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

        foreach ($data['geo_areas'] as $geoAreaData) {
            $vote->addGeoArea($this->createOrFindGeoArea($geoAreaData));
        }

        foreach ($this->findRelatedCountries($vote) as $relatedCountry) {
            $vote->addCountry($relatedCountry);
        }

        $this->em->persist($vote);
        $this->votes[$data['id']] = $vote;

        if (isset($data['member_votes'])) {
            foreach ($data['member_votes'] as $memberVote) {
                $this->createMemberVote($vote, $memberVote);
            }
        }

        if (isset($data['stats']['by_group'])) {
            foreach ($data['stats']['by_group'] as $statGroup) {
                $this->createPoliticalGroupVote($vote, $statGroup);
            }
        }

        return $vote;
    }

    private function createPoliticalGroupVote(Vote $vote, array $statGroup): void
    {
        $politicalGroupVote = (new PoliticalGroupVote())
            ->setVote($vote)
            ->setPoliticalGroup($this->createOrFindGroup($statGroup['group']))
            ->setStats([
                'FOR' => $statGroup['stats']['FOR'],
                'AGAINST' => $statGroup['stats']['AGAINST'],
                'ABSTENTION' => $statGroup['stats']['ABSTENTION'],
                'DID_NOT_VOTE' => $statGroup['stats']['DID_NOT_VOTE'],
            ]);

        $this->em->persist($politicalGroupVote);
    }

    private function createMemberVote(Vote $vote, array $data): void
    {
        $member = $this->createOrFindMember($data['member']);
        $memberVote = (new MemberVote())
            ->setValue($data['position'])
            ->setMember($member)
            ->setVote($vote)
        ;

        $this->em->persist($memberVote);
    }

    private function createOrFindMember($data): Member
    {
        if (isset($this->members[$data['id']])) {
            return $this->members[$data['id']];
        }

        $member = (new Member())
            ->setMepId($data['id'])
            ->setFirstName($data['first_name'])
            ->setLastName($data['last_name'])
            ->setThumb($data['thumb_url'])
            ->setGroup($this->createOrFindGroup($data['group']))
            ->setCountry($this->createOrFindCountry($data['country']))
        ;

        $this->em->persist($member);
        $this->members[$data['id']] = $member;

        return $member;
    }

    private function createOrFindGroup(array $data): PoliticalGroup
    {
        if (isset($this->politicalGroup[$data['code']])) {
            return $this->politicalGroup[$data['code']];
        }

        $group = (new PoliticalGroup())
            ->setCode($data['code'])
            ->setLabel($data['label'])
            ->setShortLabel($data['short_label'])
        ;

        $this->em->persist($group);
        $this->politicalGroup[$data['code']] = $group;

        return $group;
    }

    private function createOrFindCountry(array $data): Country
    {
        if (isset($this->countries[$data['code']])) {
            return $this->countries[$data['code']];
        }

        $country = (new Country())
            ->setCode($data['code'])
            ->setIsoAlpha($data['iso_alpha_2'])
            ->setLabel($data['label'])
        ;

        $this->em->persist($country);
        $this->countries[$data['code']] = $country;

        return $country;
    }

    public function createOrFindGeoArea(array $data): GeoArea
    {
        if (isset($this->geoAreas[$data['code']])) {
            return $this->geoAreas[$data['code']];
        }

        $geoArea = (new GeoArea())
            ->setCode($data['code'])
            ->setLabel($data['label'])
        ;

        $this->em->persist($geoArea);
        $this->geoAreas[$data['code']] = $geoArea;

        return $geoArea;
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

    private function findRelatedCountries(Vote $vote): array
    {
        $relatedCountries = [];

        foreach ($vote->getGeoAreas() as $geoArea) {
            foreach ($this->countries as $country) {
                if ($geoArea->getLabel() === $country->getLabel()) {
                    $relatedCountries[$country->getLabel()] = $country;
                }
            }
        }

        foreach ($this->countries as $country) {
            if (null !== $vote->getTitle() && false !== stripos($vote->getTitle(), $country->getLabel())) {
                $relatedCountries[$country->getLabel()] = $country;
            }
        }

        return $relatedCountries;
    }
}
