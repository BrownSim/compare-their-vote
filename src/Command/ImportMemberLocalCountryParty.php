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
    private const BASE_URL = 'https://www.europarl.europa.eu';

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
            $parties[$party->getCountry()->getId()][$party->getLabel()] = $party;
        }

        foreach ($this->em->getRepository(Member::class)->findAll() as $member) {
            $members[$member->getMepId()] = $member;
        }


        $client = new Client();
        $bar = new ProgressBar($output, count($members));

        foreach ($members as $member) {
            $bar->advance();

            $html = $client->get('https://www.europarl.europa.eu/meps/en/'.$member->getMepId())->getBody()->getContents();
            $crawler = new Crawler($html);
            $link = null;

            try {
                $link = $crawler->filter('.erpl_accordion-item:last-child .erpl_links-list-nav ul li:first-child a')->attr('href');
            } catch (\Exception $e) {
            }

            if (null === $link) {
                continue;
            }

            $html = $client->get(self::BASE_URL.$link)->getBody()->getContents();
            $crawler = new Crawler($html);
            $party = null;

            try {
                $party = $crawler->filter('.erpl_meps-status')->each(function (Crawler $node) {
                    if ($node->filter('.erpl_title-h4')->text('National parties') === 'National parties') {
                        return $node->filter('ul li:first-child')->text();
                    }

                    return null;
                });
            } catch (\Exception $e) {
            }

            $party = array_filter($party);

            if (null === $party || count($party) === 0) {
                continue;
            }

            try {
                //party data looks like this : dd-mm-yyy : party name (country) or dd-mm-yyyy - dd-mm-yyyy : party name (country)
                //remove everything between ()
                $party = preg_replace("/\([^)]+\)/",'', reset($party));

                //remove dates
                $party = trim(substr($party, strpos($party, ':') + 1));
            } catch (\Exception $e) {
                $party = null;
            }

            if (null === $party) {
                continue;
            }

            $existingCountry = $parties[$member->getCountry()->getId()][$party] ?? null;
            if (null !== $existingCountry) {
                $member->setParty($existingCountry);
                continue;
            }

            $newParty = (new Party())
                ->setLabel($party)
                ->setCountry($member->getCountry())
            ;

            $this->em->persist($newParty);
            $parties[$newParty->getCountry()->getId()][$party] = $newParty;

            $member->setParty($newParty);
        }

        $bar->finish();
        $this->em->flush();

        $output->writeln('Saving ...');
        $output->writeln('Done 🥳');

        return self::SUCCESS;
    }
}
