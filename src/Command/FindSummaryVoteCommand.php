<?php

namespace App\Command;

use App\Entity\Member;
use App\Entity\Vote;
use App\Manager\MemberManager;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;

#[AsCommand(name: 'app:vote:find-summary')]
class FindSummaryVoteCommand extends Command
{
    private const BASE_URL = 'https://oeil.secure.europarl.europa.eu';

    private const PROCEDURE_FILE_BASE_URL = self::BASE_URL . '/oeil/en/procedure-file?reference=';

    public function __construct(
        private readonly EntityManagerInterface $em,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $client = new Client();
        $votes = $this->em->getRepository(Vote::class)->findBy(['summaryLink' => null]);

        $bar = new ProgressBar($output->section());
        $bar->setMaxSteps(count($votes));

        $iterator = 0;
        foreach ($votes as $vote) {
            try {
                $html = $client
                    ->get(self::PROCEDURE_FILE_BASE_URL . $vote->getProcedureReference())
                    ->getBody()
                    ->getContents()
                ;

                $crawler = new Crawler($html);
                $links = $crawler->filter('#erpl_accordion-item-doc-gateway-EP table tbody tr a')->each(
                    function (Crawler $node, $i) {
                        if (str_contains($node->attr('href'), 'document-summary')) {
                            return $node->attr('href');
                        }

                        return null;
                    }
                );

                $links = array_filter($links);
                $lastLink = self::BASE_URL . array_pop($links);
                $vote->setSummaryLink($lastLink);
            } catch (\Exception) {
            } finally {
                if ($iterator === 100) {
                    $this->em->flush();
                    $iterator = 1;
                }

                $iterator++;
                $bar->advance();
            }
        }

        $bar->finish();
        $this->em->flush();

        return Command::SUCCESS;
    }
}
