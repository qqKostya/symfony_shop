<?php

declare(strict_types=1);

namespace App\Report\Service;

use App\Order\Entity\Enum\OrderStatus;
use App\Report\Kafka\KafkaProducer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

final class ReportService
{
    private const REPORTS_DIR = 'var/reports/';

    public function __construct(
        private KafkaProducer $kafkaProducer,
        private EntityManagerInterface $entityManager,
        private Filesystem $filesystem,
        private ParameterBagInterface $parameterBag,
    ) {
        $kernelDir = $this->parameterBag->get('kernel.project_dir');

        $reportsDir = $kernelDir . \DIRECTORY_SEPARATOR . self::REPORTS_DIR;

        if (!$this->filesystem->exists($reportsDir)) {
            $this->filesystem->mkdir($reportsDir);
        }
    }

    public function startReportGeneration(): string
    {
        $reportId = uniqid();
        $this->kafkaProducer->produceReportGenerationEvent($reportId);

        return $reportId;
    }

    public function generateReportFile(string $reportId): void
    {
        $soldItems = $this->getSoldItems();

        $filePath = $this->getReportFilePath($reportId);
        $file     = fopen($filePath, 'w');

        foreach ($soldItems as $item) {
            fwrite($file, json_encode($item, JSON_UNESCAPED_UNICODE) . "\n");
        }

        fclose($file);

        $this->completeReportGeneration($reportId);
    }

    private function getSoldItems(): array
    {
        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('o.id AS order_id, u.id AS user_id, p.name AS product_name, p.cost AS price, oi.quantity AS amount')
            ->from('App\Order\Entity\Order', 'o')
            ->join('o.user', 'u')
            ->join('App\Order\Entity\OrderItem', 'oi', 'WITH', 'oi.order = o.id')
            ->join('oi.product', 'p')
            ->where('o.status = :status')
            ->setParameter('status', OrderStatus::PAID);

        return $qb->getQuery()->getArrayResult();
    }

    public function completeReportGeneration(string $reportId): void
    {
        $this->kafkaProducer->produceReportGeneratedEvent($reportId);
    }

    public function getReportFilePath(string $reportId): string
    {
        $kernelDir = $this->parameterBag->get('kernel.project_dir');

        return $kernelDir . \DIRECTORY_SEPARATOR . self::REPORTS_DIR . $reportId . '.jsonl';
    }

    public function getReportFile(string $reportId): Response
    {
        $filePath = $this->getReportFilePath($reportId);

        if (!$this->filesystem->exists($filePath)) {
            return new Response('Файл отчёта не найден', Response::HTTP_NOT_FOUND);
        }

        return new BinaryFileResponse($filePath, Response::HTTP_OK, [
            'Content-Type' => 'application/jsonl',
            'Content-Disposition' => "attachment; filename=\"{$reportId}.jsonl\"",
        ]);
    }

    public function isReportReady(string $reportId): bool
    {
        $filePath = $this->getReportFilePath($reportId);

        return $this->filesystem->exists($filePath);
    }
}
