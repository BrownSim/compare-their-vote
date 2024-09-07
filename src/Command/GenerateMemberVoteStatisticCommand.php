<?php

namespace App\Command;

use App\Entity\Member;
use App\Entity\MemberVoteStatistic;
use App\Manager\MemberManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:member:statistic', description: 'Precalculate member statistic')]
class GenerateMemberVoteStatisticCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $membersVoteStatistic = $this->em->getRepository(Member::class)->findMembersWithVoteStatistics();
        foreach ($membersVoteStatistic as $membersWithVoteStatistic) {
            $memberVoteStatistic = (new MemberVoteStatistic())
                ->setMember($membersWithVoteStatistic['member'])
                ->setMiss($membersWithVoteStatistic['miss'])
                ->setAttendance($membersWithVoteStatistic['attendance'])
            ;

            $this->em->persist($memberVoteStatistic);
        }

        $this->em->flush();

        return Command::SUCCESS;
    }
}
