<?php

declare(strict_types=1);

namespace App\Report\Kafka;

use App\Report\Service\ReportService;
use Psr\Log\LoggerInterface;
use RdKafka\Conf;
use RdKafka\Consumer;
use RdKafka\ConsumerTopic;

final class KafkaConsumer
{
    private ConsumerTopic $topic;

    private Consumer $consumer;

    private LoggerInterface $logger;

    private array $validStatuses;

    public function __construct(
        private ReportService $reportService,
        string $broker,
        string $topicName,
        LoggerInterface $logger,
        array $validStatuses = ['started'],
    ) {
        $this->logger = $logger;
        $this->validStatuses = $validStatuses;

        $conf = new Conf();
        $conf->set('group.id', 'report-consumer');
        $conf->set('metadata.broker.list', $broker);
        $conf->set('auto.offset.reset', 'earliest');

        $this->consumer = new Consumer($conf);
        $this->topic = $this->consumer->newTopic($topicName);

        $this->topic->consumeStart(0, RD_KAFKA_OFFSET_END);
    }

    public function consume(): void
    {
        while (true) {
            $message = $this->topic->consume(0, 1000);

            if ($message === null) {
                $this->logger->error('Received null message, skipping...');
                continue;
            }

            if ($message->err !== RD_KAFKA_RESP_ERR_NO_ERROR) {
                $this->logger->error('Error consuming message: ' . $message->errstr());

                continue;
            }

            $payload = json_decode($message->payload, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->logger->error('Invalid JSON payload: ' . json_last_error_msg());

                continue; // Пропускаем сообщение с ошибкой JSON
            }

            if (!\in_array($payload['status'] ?? null, $this->validStatuses, true)) {
                $this->logger->warning('Invalid status received: ' . ($payload['status'] ?? 'null'));

                continue;
            }

            $this->processWithRetry($payload);

            usleep(5_000_000); // 0.5 сек
        }
    }

    /**
     * Метод обработки сообщения с попытками повторения.
     */
    private function processWithRetry(array $payload): void
    {
        $retryAttempts = 3;
        $retryDelay = 1_000_000;

        for ($attempt = 1; $attempt <= $retryAttempts; ++$attempt) {
            try {
                if ($payload['status'] === 'started') {
                    $this->reportService->generateReportFile($payload['reportId']);
                    $this->logger->info('Successfully processed report with ID: ' . $payload['reportId']);

                    return;
                }
            } catch (\Exception $e) {
                $this->logger->error(
                    'Error processing report (attempt ' . $attempt . ' of ' . $retryAttempts . '): ' . $e->getMessage(),
                );
            }

            if ($attempt < $retryAttempts) {
                usleep($retryDelay);
            }
        }

        $this->sendToDeadLetterQueue($payload);
    }

    /**
     * Метод для отправки сообщения в очередь ошибок (Dead Letter Queue).
     */
    private function sendToDeadLetterQueue(array $payload): void
    {
        $this->logger->error('Sending failed message to Dead Letter Queue: ' . json_encode($payload));
    }
}
