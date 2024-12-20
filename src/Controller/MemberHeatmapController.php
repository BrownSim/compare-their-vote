<?php

namespace App\Controller;

use App\Entity\MemberToMemberVoteComparison;
use App\Form\Type\MemberMapType;
use App\Manager\MatrixManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MemberHeatmapController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly MatrixManager $matrixManager
    ) {
    }

    #[Route('/member-heatmap', name: 'member-heatmap', defaults: ['template' => 'memberHeatmap/index.html.twig'])]
    #[Route('/member-heatmap-fullscreen', name: 'member-heatmap-fullscreen', defaults: ['template' => 'memberHeatmap/fullscreen.html.twig'])]
    public function show(Request $request): Response
    {
        $form = $this->createForm(MemberMapType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('mapType')->getData() === 1) {
                $politicalGroup = $form->get('group')->getData();
                $country = $form->get('country')->getData();
                $data = $this->em->getRepository(MemberToMemberVoteComparison::class)->compareByGroup($politicalGroup, $country);
            } else {
                $mpCountry = $form->get('mpCountry')->getData();
                $country = $form->get('country')->getData();
                $data = $this->em->getRepository(MemberToMemberVoteComparison::class)->compareByCountry($mpCountry, $country);
            }

            $matrix = $this->matrixManager->generateMatrix($data);

            return $this->render($request->attributes->get('template'), [
                'form' => $form->createView(),
                'matrix' => $matrix,
            ]);
        }

        return $this->render($request->attributes->get('template'), [
            'form' => $form->createView(),
        ]);
    }
}
