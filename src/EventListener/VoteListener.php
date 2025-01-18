<?php

namespace App\EventListener;

use App\Entity\Vote;
use App\Message\VoteToBeAnalysed;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsDoctrineListener(event: Events::postFlush)]
#[AsEntityListener(event: Events::postPersist, entity: Vote::class)]
class VoteListener
{
    private array $entities = [];

    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    public function postPersist(Vote $vote)
    {
        $this->entities[] = $vote;
    }

    public function postFlush(PostFlushEventArgs $args): void
    {
        foreach ($this->entities as $entity) {
            $this->bus->dispatch(new VoteToBeAnalysed($entity->getId()));
        }
    }
}
