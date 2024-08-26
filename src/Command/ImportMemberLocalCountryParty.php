<?php

namespace App\Command;

use App\Entity\Member;
use App\Entity\Party;
use App\Manager\MemberManager;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;

#[AsCommand(name: 'app:member:party', description: 'Find member\'s party')]
class ImportMemberLocalCountryParty extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $parties = [];
        $members = [];

        foreach ($this->em->getRepository(Party::class)->findAll() as $party) {
            $parties[$party->getLabel()] = $party;
        }

        foreach ($this->em->getRepository(Member::class)->findAll() as $member) {
            $members[$member->getMepId()] = $member;
        }
        $guzzle = new Client();
        $html = $guzzle->get('https://www.europarl.europa.eu/meps/fr/full-list/all')
            ->getBody()
            ->getContents();

        $crawler = new Crawler($html);
        $membersData = $crawler->filter('.erpl_member-list-item')->each(function (Crawler $node) {
            return [
                'mepId' => basename($node->filter('a')->attr('href')),
                'party' => $node->filter('.sln-additional-info')->last()->text(),
            ];
        });

        foreach ($membersData as $memberDatum) {
            $member = $members[$memberDatum['mepId']];
            $party = $parties[$memberDatum['party']] ?? null;

            if (null === $party) {
                $party = (new Party())->setLabel($memberDatum['party']);
                $parties[$party->getLabel()] = $party;

                $this->em->persist($party);
            }

            $member->setParty($party);
        }

        $this->em->flush();

        return self::SUCCESS;
    }
}
