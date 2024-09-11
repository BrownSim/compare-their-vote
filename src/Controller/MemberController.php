<?php

namespace App\Controller;

use App\Datatable\DatatableBuilder;
use App\Datatable\Normalizer\GenericNormalizer;
use App\Entity\Member;
use App\Entity\MemberVote;
use App\Form\Type\MemberFilterType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/member/', name: 'member_')]
class MemberController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PaginatorInterface $paginator,
        private readonly DatatableBuilder $datatableBuilder,
        private readonly GenericNormalizer $genericNormalizer
    ) {
    }

    #[Route(name: 'list')]
    public function list(Request $request): Response
    {
        $memberFilter = $this->createForm(MemberFilterType::class);

        return $this->render('member/list.html.twig', [
            'members' => $this->em->getRepository(Member::class)->findBy([], ['lastName' => 'ASC']),
            'filter' => $memberFilter,
        ]);
    }

    #[Route(path: '{mepId}', name: 'show')]
    public function show(#[MapEntity(mapping: ['mepId' => 'mepId'])] Member $member): Response
    {
        $nbDidntVote = $this->em->getRepository(MemberVote::class)->countMemberVoteByValue($member, MemberVote::VOTE_DID_NOT_VOTE);
        $paginatedVoteResults = $this->paginator->paginate(
            target: $this->em->getRepository(MemberVote::class)->getVoteResultByMemberQuery($member)
        );

        return $this->render('member/index.html.twig', [
            'member' => $member,
            'datatable' => $this->datatableBuilder->build('member_vote'),
            'voteResults' => $paginatedVoteResults,
            'nbDidnotVote' => $nbDidntVote,
        ]);
    }

    #[Route(path: '{mepId}/vote', name: 'list_vote', format: 'json')]
    public function listVote(
        Request $request,
        #[MapEntity(mapping: ['mepId' => 'mepId'])]
        Member $member
    ): Response {
        $paginatedVoteResults = $this->paginator->paginate(
            target: $this->em->getRepository(MemberVote::class)->getVoteResultByMemberQuery($member),
            page: $request->query->get('page', 1),
        );

        return new JsonResponse($this->genericNormalizer->normalize('member_vote', $paginatedVoteResults));
    }
}
