<?php

namespace App\Controller;

use App\Datatable\DatatableBuilder;
use App\Datatable\Normalizer\DatatableGenericNormalizer;
use App\Entity\Member;
use App\Entity\MemberVoteStatistic;
use App\Entity\PoliticalGroup;
use App\Form\Type\MemberFilterType;
use App\Manager\CalendarManager;
use App\Manager\StatisticManager;
use App\Normaliser\Chart\AbsenceMpAndGroupNormalizer;
use App\Normaliser\Chart\AbsenceTrendNormalizer;
use App\Normaliser\Chart\ScatterPlotNormaliser;
use App\Normaliser\Datatable\CountryAbsenteeismNormalizer;
use App\Normaliser\Datatable\MemberAbsenteeismNormalizer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/absenteeism', name: 'absenteeism_')]
class AbsenteeismController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly DatatableBuilder $datatableBuilder,
        private readonly DatatableGenericNormalizer $genericNormalizer,
        private readonly StatisticManager $statisticManager,
        private readonly ScatterPlotNormaliser $scatterPlotNormaliser,
        private readonly CountryAbsenteeismNormalizer $countryAbsenteeismNormalizer,
        private readonly MemberAbsenteeismNormalizer $memberAbsenteeismNormalizer,
        private readonly AbsenceTrendNormalizer $absenceTrendNormalizer,
        private readonly AbsenceMpAndGroupNormalizer $absenceMpAndGroupNormalizer,
        private readonly CalendarManager $calendarManager,
    ) {
    }

    #[Route(name: 'overview')]
    public function overview(): Response
    {
        $paginatedMembers = [];

        $membersVoteStatistic = $this->em->getRepository(MemberVoteStatistic::class)->findByMemberCountry();
        $politicalGroupAbsenceStatistics = $this->absenceMpAndGroupNormalizer->process($membersVoteStatistic);

        return $this->render('absenteeism/overview.html.twig', [
            'paginatedMembers' => $paginatedMembers,
            'map' => json_encode($this->countryAbsenteeismNormalizer->process($membersVoteStatistic)),
            'politicalGroupAbsenceChart' => json_encode($politicalGroupAbsenceStatistics),
        ]);
    }

    #[Route(path: '/country', name: 'country')]
    public function byCountry(Request $request): Response
    {
        $filter = $this->createForm(MemberFilterType::class);
        $filter->handleRequest($request);

        if ($filter->isSubmitted() && $filter->isValid()) {
            $country = $filter->get('country')->getData();
            $membersVoteStatistic = $this->em->getRepository(MemberVoteStatistic::class)->findByMemberCountry($country);

            $this->statisticManager->generateAbsencePrediction($membersVoteStatistic);
            $politicalGroups = $this->em->getRepository(PoliticalGroup::class)->findByMemberCountry($country);

            $absencePredictionDatatable = $this->memberAbsenteeismNormalizer->process($membersVoteStatistic);
            $absencePredictionDatatable = $this->genericNormalizer->normalize('absence', $absencePredictionDatatable);

            $absenceTrendChart = $this->absenceTrendNormalizer->process($membersVoteStatistic);
            $politicalGroupAbsenceStatistics = $this->absenceMpAndGroupNormalizer->process($membersVoteStatistic);

            $members = $this->em->getRepository(Member::class)->findMembersWithVotesByCountry($country);

            return $this->render('absenteeism/country.html.twig', [
                'filter' => $filter->createView(),
                'country' => $country,
                'politicalGroups' => $politicalGroups,
                'absenceDatatable' => $this->datatableBuilder->build('absence'),
                'absencePredictionDatatable' => json_encode($absencePredictionDatatable),
                'absenceChart' => json_encode($this->scatterPlotNormaliser->absenceDotPlot($membersVoteStatistic)),
                'absenceTrendChart' => json_encode($absenceTrendChart),
                'politicalGroupAbsenceChart' => json_encode($politicalGroupAbsenceStatistics),
                'ganttAbsence' => $this->calendarManager->generateAbsenceGantForMembers($members),
            ]);
        }

        return $this->render('absenteeism/country.html.twig', [
            'filter' => $filter->createView(),
        ]);
    }
}
