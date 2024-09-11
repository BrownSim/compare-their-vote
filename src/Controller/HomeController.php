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
        foreach (range(1, 10) as $item) {
            foreach (range(1, 10) as $item2) {
                if ($item === $item2) {
                    $matrix[$memberBaseName . $item][$memberBaseName . $item2] = [];
                    continue;
                }

                $matrix[$memberBaseName . $item][$memberBaseName . $item2] = [
                    'rate' => $this->fakeMatrixRate($item, $item2),
                    'nb_vote' => 100,
                ];
            }
        }

        return $matrix;
    }

    private function fakeMatrixRate(int $m1, int $m2): int
    {
        if ($m1 <= 3 && $m2 <= 3) {
            return rand(85, 100);
        }

        if ($m1 > 3 && $m1 <= 6 && $m2 > 3 && $m2 <= 6) {
            return rand(85, 100);
        }

        if ($m1 <= 3 && $m2 <= 6) {
            return rand(50, 70);
        }

        if ($m1 <= 6 && $m2 <= 3) {
            return rand(50, 70);
        }

        if ($m1 > 6 && $m1 <= 9 && $m2 > 6 && $m2 <= 9) {
            return rand(85, 100);
        }

        return rand(10, 80);
    }
}
