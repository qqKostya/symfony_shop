<?php

declare(strict_types=1);

namespace App\Report\Controller;

use App\Report\Service\ReportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin')]
final class ReportController extends AbstractController
{
    public function __construct(private ReportService $reportService) {}

    #[Route('/generate-report', methods: [Request::METHOD_GET])]
    public function generateReport(): Response
    {
        $reportId = $this->reportService->startReportGeneration();

        return $this->reportService->getReportFile($reportId);
    }
}
