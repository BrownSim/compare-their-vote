<?php

namespace App\Controller;

use App\Entity\PoliticalGroup;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PoliticalGroupController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    #[Route('/political-groups', name: 'groups')]
    public function list(): Response
    {
        return $this->render('politicalGroup/index.html.twig', [
            'groups' => $this->em->getRepository(PoliticalGroup::class)->findAll(),
        ]);
    }
}
