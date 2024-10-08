<?php

namespace App\Controller;

use App\Datatable\DatatableBuilder;
use App\Datatable\Normalizer\GenericNormalizer;
use App\Entity\Session;
use App\Form\Type\MemberSearchType;
use App\Manager\MemberManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class CompareMemberController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em
    ) {
    }

    #[Route('compare-member', name: 'compare-member')]
    public function home(Request $request, MemberManager $memberManager, DatatableBuilder $datatableBuilder, GenericNormalizer $normalizer)
    {
        $lastSession = $this->em->getRepository(Session::class)->findOneBy(['status' => Session::SESSION_STATUS_LAST]);

        $form = $this->createForm(MemberSearchType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $members = array_merge([$formData['member']], $formData['members']);
            $data = $memberManager->compare($formData['mainMember'], $members, $formData['voteValue']);

            foreach ($data as &$datum) {
                $datum['normalizedData'] = json_encode($normalizer->normalize('member', $datum['data']['votes_list']));
            }
            unset($datum);

            return $this->render('compareMember/index.html.twig', [
                'data' => $data,
                'form' => $form->createView(),
                'lastSession' => $lastSession,
                'datatable' => $datatableBuilder->build('member'),
            ]);
        }

        return $this->render('compareMember/index.html.twig', [
            'form' => $form->createView(),
            'lastSession' => $lastSession,
        ]);
    }
}
