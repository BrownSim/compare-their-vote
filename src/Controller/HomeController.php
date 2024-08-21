<?php

namespace App\Controller;

use App\Entity\Session;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('', name: 'hp')]
    public function home(EntityManagerInterface $em)
    {
        $lastSession = $em->getRepository(Session::class)->findOneBy(['status' => Session::SESSION_STATUS_LAST]);

        return $this->render('hp.html.twig', [
            'lastSession' => $lastSession,
            'matrix' => $this->generateFakeMatrix(),
        ]);
    }

    private function generateFakeMatrix(): array
    {
        $matrix = [];
        $memberBaseName = 'Member ';
        foreach (range(1, 6) as $item) {
            foreach (range(1, 6) as $item2) {
                if ($item === $item2) {
                    $matrix[$memberBaseName . $item][$memberBaseName . $item2] = [];
                    continue;
                }

                $matrix[$memberBaseName . $item][$memberBaseName . $item2] = [
                    'rate' => rand(10, 100),
                    'nb_vote' => 100,
                ];
            }
        }

        return $matrix;
    }
}
