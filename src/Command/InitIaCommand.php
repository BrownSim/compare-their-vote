<?php

namespace App\Command;

use App\Analyser\VoteAnalyser;
use App\Entity\Vote;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:init:ia')]
class InitIaCommand extends Command
{
    private const LIMIT = 20;

    public function __construct(private readonly VoteAnalyser $analyser, private readonly EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $repository = $this->em->getRepository(Vote::class);

        $bar = new ProgressBar($output->section());
        $bar->setMaxSteps($repository->count());
        $bar->start();

        $offset = 0;

        do {
            $votes = $repository->findBy(criteria: [], limit: self::LIMIT, offset: $offset);
            $offset += self::LIMIT;

            foreach ($votes as $vote) {
                try {
                    $this->analyser->analyse($vote);
                } catch (\Exception) {
                }

                //prevent flood api
                sleep(20);
                $bar->advance();
            }
        } while (count($votes));

        $bar->finish();

        return Command::SUCCESS;
    }
}
