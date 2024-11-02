<?php

namespace App\Command;

use App\Entity\Member;
use App\Manager\MemberManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:member:agreement', description: 'Precalculate data (it\'s slooooooow)')]
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
        $output->writeln('Start precalculate correlation');

        $importedConnexion = [];

        foreach ($members as $member1) {
            $member1Votes = [];
            foreach ($member1->getMemberVotes() as $memberVote) {
                $member1Votes[$memberVote->getVote()->getId()] = $memberVote;
            }

            foreach ($members as $member2) {
                $member2Votes = [];

                // check if value is already imported
                if (isset($importedConnexion[$member1->getId()][$member2->getId()])
                    || isset($importedConnexion[$member2->getId()][$member1->getId()])) {
                    continue;
                }

                if ($member1->getId() === $member2->getId()) {
                    continue;
                }

                foreach ($member2->getMemberVotes() as $memberVote) {
                    if (isset($member1Votes[$memberVote->getVote()->getId()])) {
                        $member2Votes[$memberVote->getVote()->getId()] = $memberVote;
                    }
                }

                $voteData = $this->memberManager->compareArray($member1Votes, $member2Votes);
                $this->insertData(
                    member1:  $member1->getId(),
                    member2: $member2->getId(),
                    group1: $member1->getGroup()->getId(),
                    group2: $member2->getGroup()->getId(),
                    country1: $member1->getCountry()->getId(),
                    country2: $member2->getCountry()->getId(),
                    nBVote: $voteData['total'],
                    rate: $voteData['rate']['same'],
                );

                $comparisons = $this->compareMembersVotesRelatedToCountry($member1Votes, $member2Votes);
                foreach ($comparisons as $comparison) {
                    $this->insertData(
                        member1:  $member1->getId(),
                        member2: $member2->getId(),
                        group1: $member1->getGroup()->getId(),
                        group2: $member2->getGroup()->getId(),
                        country1: $member1->getCountry()->getId(),
                        country2: $member2->getCountry()->getId(),
                        nBVote: $comparison['total'],
                        rate: $comparison['rate'],
                        relatedCountry: $comparison['country']
                    );
                }

                $importedConnexion[$member1->getId()][$member2->getId()] = true;
            }

            $bar->advance();
        }

        $bar->finish();

        $output->writeln('');
        $output->writeln('Saving');

        return Command::SUCCESS;
    }

    private function compareMembersVotesRelatedToCountry(array $member1Votes, array $member2Votes)
    {
        $data = [];
        $comparison = [];
        foreach ($member1Votes as $member1Vote) {
            foreach ($member1Vote->getVote()->getCountries() as $country) {
                $data[$country->getId()]['member_1'][$member1Vote->getVote()->getId()] = $member1Vote;
                $data[$country->getId()]['country'] = $country->getId();
            }
        }

        foreach ($member2Votes as $member2Vote) {
            foreach ($member2Vote->getVote()->getCountries() as $country) {
                $data[$country->getId()]['member_2'][$member2Vote->getVote()->getId()] = $member2Vote;
            }
        }

        foreach ($data as $datum) {
            if (isset($datum['member_1']) === false || isset($datum['member_2']) === false) {
                continue;
            }

            $compare = $this->memberManager->compareArray($datum['member_1'], $datum['member_2']);
            $comparison[] = [
                'country' => $datum['country'],
                'rate' => $compare['rate']['same'],
                'total' => $compare['total']
            ];
        }

        return $comparison;
    }

    /**
     * Insert with MySql instead of Doctrine. To many data for using Doctrine
     */
    private function insertData(
        int $member1,
        int $member2,
        int $group1,
        int $group2,
        int $country1,
        int $country2,
        int $nBVote,
        float $rate,
        ?int $relatedCountry = null
    ): void {
        $relatedCountry = null === $relatedCountry ? 'NULL' : $relatedCountry;

        $sql = 'INSERT INTO member_to_member_vote_comparison (
                   member_1_id, 
                   member_2_id, 
                   group_member_1_id, 
                   group_member_2_id, 
                   country_member_1_id, 
                   country_member_2_id, 
                   related_rate_country_id, 
                   nb_vote, 
                   agreement_rate
                )
                VALUES(
                   '.$member1.',
                   '.$member2.',
                   '.$group1.',
                   '.$group2.',
                   '.$country1.',
                   '.$country2.',
                   '.$relatedCountry.',
                   '.$nBVote.',
                   '.$rate.'
                )';

        $this->em->getConnection()->prepare($sql)->executeQuery();
    }
}
