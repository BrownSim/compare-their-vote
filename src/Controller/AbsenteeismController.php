<?php

namespace App\Controller;

use App\Datatable\DatatableBuilder;
use App\Datatable\Normalizer\GenericNormalizer;
use App\Entity\MemberVoteStatistic;
use App\Entity\PoliticalGroup;
use App\Form\Type\MemberFilterType;
use App\Manager\StatisticManager;
use App\Normaliser\Chart\AbsenceNormalizer as AbsenceChartNormalizer;
use App\Normaliser\Chart\ScatterPlotNormaliser;
use App\Normaliser\Datatable\AbsenceNormalizer as AbsenceDatatableNormalizer;
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
        private readonly GenericNormalizer $genericNormalizer,
        private readonly StatisticManager $statisticManager,
        private readonly ScatterPlotNormaliser $scatterPlotNormaliser,
        private readonly AbsenceDatatableNormalizer $absenceDatatableNormalizer,
        private readonly AbsenceChartNormalizer $absenceChartNormalizer
    ) {
    }

    #[Route(name: 'overview')]
    public function overview(): Response
    {
        $paginatedMembers = [];

        $membersVoteStatistic = $this->em->getRepository(MemberVoteStatistic::class)->findByMemberCountry();
        $politicalGroupAbsenceStatistics = $this->absenceChartNormalizer->analyseAbsenceByMpAndPoliticalGroup($membersVoteStatistic);

        return $this->render('absenteeism/overview.html.twig', [
            'paginatedMembers' => $paginatedMembers,
            'map' => json_encode($this->absenceDatatableNormalizer->countryVoteStatsToDatatable($membersVoteStatistic)),
            'politicalGroupAbsenceChartJson' => json_encode($politicalGroupAbsenceStatistics),
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

            $absencePredictionDatatableData = $this->absenceDatatableNormalizer->memberVoteStatsToDatatable($membersVoteStatistic);
            $absencePredictionChartData = $this->absenceChartNormalizer->memberVoteStatsToDatatable($membersVoteStatistic);
            $politicalGroupAbsenceStatistics = $this->absenceChartNormalizer->analyseAbsenceByMpAndPoliticalGroup($membersVoteStatistic);

            return $this->render('absenteeism/country.html.twig', [
                'filter' => $filter->createView(),
                'country' => $country,
                'politicalGroups' => $politicalGroups,
                'absenceChartJson' => json_encode($this->scatterPlotNormaliser->absenceDotPlot($membersVoteStatistic)),
                'absenceDatatable' => $this->datatableBuilder->build('absence'),
                'absencePredictionDatatableData' => json_encode($this->genericNormalizer->normalize('absence', $absencePredictionDatatableData)),
                'absenceComparisonChartJson' => json_encode($absencePredictionChartData),
                'politicalGroupAbsenceChartJson' => json_encode($politicalGroupAbsenceStatistics),
            ]);
        }

        return $this->render('absenteeism/country.html.twig', [
            'filter' => $filter->createView(),
        ]);
    }
}
