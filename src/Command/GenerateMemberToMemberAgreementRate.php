<?php

namespace App\Command;

use App\Entity\Member;
use App\Entity\MemberToMemberVoteComparison;
use App\Manager\MemberManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:member:agreement', description: 'Import data from api')]
class GenerateMemberToMemberAgreementRate extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly MemberManager $memberManager,
        ?string $name = null
    ) {
        parent::__construct($name);

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $members = $this->em->getRepository(Member::class)->findMembersWithVotes();
        $bar = new ProgressBar($output, count($members));

        foreach ($members as $member1) {
            $member1Votes = [];
            foreach ($member1->getMemberVotes() as $memberVote) {
                $member1Votes[$memberVote->getVote()->getId()] = $memberVote;
            }

            foreach ($members as $member2) {
                $member2Votes = [];
                if ($member1->getId() === $member2->getId()) {
                    continue;
                }

                foreach ($member2->getMemberVotes() as $memberVote) {
                    if (isset($member1Votes[$memberVote->getVote()->getId()])) {
                        $member2Votes[$memberVote->getVote()->getId()] = $memberVote;
                    }
                }

                $voteData = $this->memberManager->compareArray($member1Votes, $member2Votes);

                $voteComparison = (new MemberToMemberVoteComparison())
                    ->setMember1($member1)
                    ->setMember2($member2)
                    ->setGroupMember1($member1->getGroup())
                    ->setGroupMember2($member2->getGroup())
                    ->setCountryMember1($member1->getCountry())
                    ->setCountryMember2($member2->getCountry())
                    ->setAgreementRate($voteData['rate']['same'])
                    ->setNbVote($voteData['total'])
                ;

                $this->em->persist($voteComparison);
            }

            $bar->advance();
        }

        $bar->finish();

        $output->writeln('');
        $output->writeln('Saving');
        $this->em->flush();

        return Command::SUCCESS;
    }
}
