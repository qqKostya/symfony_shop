<?php

namespace App\Report\Controller;

use App\Report\Service\ReportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
class ReportController extends AbstractController
{
    private ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    #[Route('/generate-report', name: 'generate_report', methods: [Request::METHOD_GET])]
    public function generateReport(): JsonResponse
    {
        $reportId = $this->reportService->startReportGeneration();
        return new JsonResponse(['reportId' => $reportId], JsonResponse::HTTP_OK);
    }

}