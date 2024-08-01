<?php

namespace App\Controller;

use App\Entity\Session;
use App\Form\Type\MemberSearchType;
use App\Manager\MemberManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em
    ) {
    }

    #[Route('', name: 'hp')]
    public function home(Request $request, MemberManager $memberManager)
    {
        $form = $this->createForm(MemberSearchType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $members = array_merge([$formData['member']], $formData['members']);
            $data = $memberManager->compare($formData['mainMember'], $members, $formData['voteValue']);

            return $this->render('hp.html.twig', [
                'data' => $data,
                'form' => $form->createView(),
            ]);
        }

        return $this->render('hp.html.twig', [
            'form' => $form->createView(),
            'lastSession' => $this->em->getRepository(Session::class)->findOneBy(['status' => Session::SESSION_STATUS_LAST])
        ]);
    }
}
