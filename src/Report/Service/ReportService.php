<?php

declare(strict_types=1);

namespace App\Report\Service;

use App\Order\Entity\Enum\OrderStatus;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

final class ReportService
{
    private const REPORTS_DIR = 'var/reports/';

    public function __construct(
        private KafkaProducer $kafkaProducer,
        private EntityManagerInterface $entityManager,
        private Filesystem $filesystem,
    ) {
        if (!$this->filesystem->exists(self::REPORTS_DIR)) {
            $this->filesystem->mkdir(self::REPORTS_DIR);
        }
    }

    public function startReportGeneration(): string
    {
        $reportId = uniqid();

        $this->generateReportFile($reportId);
        $this->kafkaProducer->produceReportGenerationEvent($reportId);

        return $reportId;
    }

    private function generateReportFile(string $reportId): void
    {
        $soldItems = $this->getSoldItems();

        $filePath = $this->getReportFilePath($reportId);
        $file     = fopen($filePath, 'w');

        foreach ($soldItems as $item) {
            fwrite($file, json_encode($item, JSON_UNESCAPED_UNICODE) . "\n");
        }

        fclose($file);
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
        return self::REPORTS_DIR . $reportId . '.jsonl';
    }

    public function getReportFile(string $reportId): Response
    {
        $soldItems = $this->getSoldItems();
        $filePath = $this->getReportFilePath($reportId);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'ID заказа');
        $sheet->setCellValue('B1', 'ID пользователя');
        $sheet->setCellValue('C1', 'Название товара');
        $sheet->setCellValue('D1', 'Цена');
        $sheet->setCellValue('E1', 'Количество');

        $row = 2;
        foreach ($soldItems as $item) {
            $sheet->setCellValue("A{$row}", $item['order_id']);
            $sheet->setCellValue("B{$row}", $item['user_id']);
            $sheet->setCellValue("C{$row}", $item['product_name']);
            $sheet->setCellValue("D{$row}", $item['price']);
            $sheet->setCellValue("E{$row}", $item['amount']);
            ++$row;
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        $response = new BinaryFileResponse($filePath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            "{$reportId}.xlsx",
        );

        return $response;
    }
}
