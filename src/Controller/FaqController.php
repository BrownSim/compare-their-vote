<?php

namespace App\Controller;

use App\Entity\FAQ;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FaqController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    #[Route('/faq', name: 'faq')]
    public function show(): Response
    {
        return $this->render('faq/index.html.twig', [
            'items' => $this->em->getRepository(FAQ::class)->findAll(),
        ]);
    }
}
