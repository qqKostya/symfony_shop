<?php

// src/Report/Service/ReportService.php
namespace App\Report\Service;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\ORM\EntityManagerInterface;

class ReportService
{
    private KafkaProducer $kafkaProducer;
    private EntityManagerInterface $entityManager;
    private Filesystem $filesystem;
    private SerializerInterface $serializer;

    public function __construct(
        KafkaProducer $kafkaProducer,
        EntityManagerInterface $entityManager,
        Filesystem $filesystem,
        SerializerInterface $serializer
    ) {
        $this->kafkaProducer = $kafkaProducer;
        $this->entityManager = $entityManager;
        $this->filesystem = $filesystem;
        $this->serializer = $serializer;
    }

    public function startReportGeneration(): string
    {
        $reportId = uniqid('report_', true);

        $this->generateReportFile($reportId);

        $this->kafkaProducer->produceReportGenerationEvent($reportId);

        return $reportId;
    }

    private function generateReportFile(string $reportId): void
    {
        $soldItems = $this->getSoldItems();

        $filePath = '/path/to/reports/' . $reportId . '.jsonl';
        $file = fopen($filePath, 'w');

        foreach ($soldItems as $item) {
            $jsonLine = json_encode($item) . "\n";
            fwrite($file, $jsonLine);
        }

        fclose($file);
    }

    private function getSoldItems(): array
    {
        // Пример получения проданных товаров (можно заменить на реальные данные из БД)
        return [
            ["product_name" => "велосипед_10", "price" => 500, "amount" => 1, "user" => ["id" => 1]],
            ["product_name" => "велосипед_10", "price" => 500, "amount" => 1, "user" => ["id" => 2]],
        ];
    }

    public function completeReportGeneration(string $reportId): void
    {
        $this->kafkaProducer->produceReportGeneratedEvent($reportId);
    }
}
