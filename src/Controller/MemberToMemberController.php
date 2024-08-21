<?php

namespace App\Controller;

use App\Entity\Session;
use App\Form\Type\MemberSearchType;
use App\Manager\MemberManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class MemberToMemberController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em
    ) {
    }

    #[Route('member-to-member', name: 'member-to-member')]
    public function home(Request $request, MemberManager $memberManager)
    {
        $lastSession = $this->em->getRepository(Session::class)->findOneBy(['status' => Session::SESSION_STATUS_LAST]);

        $form = $this->createForm(MemberSearchType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $members = array_merge([$formData['member']], $formData['members']);
            $data = $memberManager->compare($formData['mainMember'], $members, $formData['voteValue']);

            return $this->render('memberToMember/index.html.twig', [
                'data' => $data,
                'form' => $form->createView(),
                'lastSession' => $lastSession,
            ]);
        }

        return $this->render('memberToMember/index.html.twig', [
            'form' => $form->createView(),
            'lastSession' => $lastSession,
        ]);
    }
}