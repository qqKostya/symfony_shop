<?php

declare(strict_types=1);

namespace App\Report\Controller;

use App\Report\Service\ReportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin')]
final class ReportController extends AbstractController
{
    public function __construct(private ReportService $reportService) {}

    #[Route('/generate-report', methods: [Request::METHOD_GET])]
    public function generateReport(): JsonResponse
    {
        $reportId = $this->reportService->startReportGeneration();

        return new JsonResponse(['reportId' => $reportId], Response::HTTP_ACCEPTED);
    }

    #[Route('/report/{reportId}', methods: [Request::METHOD_GET])]
    public function getReport(string $reportId): Response
    {
        if (!$this->reportService->isReportReady($reportId)) {
            return new JsonResponse(['status' => 'processing'], Response::HTTP_ACCEPTED);
        }

        return $this->reportService->getReportFile($reportId);
    }
}
