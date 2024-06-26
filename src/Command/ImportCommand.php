<?php

declare(strict_types=1);

namespace App\Command;

use App\Api\HowTheyVote\Client;
use App\Entity\Member;
use App\Entity\MemberVote;
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

    public function __construct(
        private readonly Client $client,
        private readonly EntityManagerInterface $em,
        ?string $name = null
    ) {
        parent::__construct($name);

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->progressBarVote = new ProgressBar($output->section());

        $votes = $this->getAllVotes();
        $this->progressBarVote->setMaxSteps(count($votes));

        foreach ($this->getAllVotes() as $vote) {
            $this->createOrFindVote($vote);
            $this->progressBarVote->advance();
        }

        $this->progressBarVote->finish();

        return Command::SUCCESS;
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
        $vote = $this->em->getRepository(Vote::class)->findOneBy(['officialId' => (int) $data['id']]);

        if (null !== $vote) {
            return $vote;
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

        $this->em->flush();
        $this->em->clear();

        return $vote;
    }

    public function createOrFindMember($data): Member
    {
        $member = $this->em->getRepository(Member::class)->findOneBy(['mepId' => $data['id']]);

        if (null !== $member) {
            return $member;
        }

        $member = (new Member())
            ->setMepId($data['id'])
            ->setFirstName($data['first_name'])
            ->setLastName($data['last_name'])
            ->setThumb($data['thumb_url'])
        ;

        $this->em->persist($member);

        return $member;
    }
}
