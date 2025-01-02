<?php

namespace App\Hydrator;

use App\Model\Member;
use Doctrine\ORM\EntityManagerInterface;

class MemberHydrator extends AbstractHydrator
{
    public function __construct(EntityManagerInterface $em, string $dtoClass = Member::class)
    {
        parent::__construct($em, $dtoClass);
    }
}
