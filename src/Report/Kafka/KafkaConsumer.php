<?php

declare(strict_types=1);

namespace App\Report\Kafka;

use App\Report\Service\ReportService;
use RdKafka\Conf;
use RdKafka\Consumer;
use RdKafka\ConsumerTopic;

final class KafkaConsumer
{
    private ConsumerTopic $topic;

    private Consumer $consumer;

    public function __construct(
        private ReportService $reportService,
        string $broker,
        string $topicName,
    ) {
        $conf = new Conf();
        $conf->set('group.id', 'report-consumer');
        $conf->set('metadata.broker.list', $broker);
        $conf->set('auto.offset.reset', 'earliest');

        $this->consumer = new Consumer($conf);
        $this->topic = $this->consumer->newTopic($topicName);

        $this->topic->consumeStart(0, RD_KAFKA_OFFSET_END); // Только новые сообщения
    }

    public function consume(): void
    {
        while (true) {
            $message = $this->topic->consume(0, 1000);
            if ($message && $message->err === RD_KAFKA_RESP_ERR_NO_ERROR) {
                $payload = json_decode($message->payload, true);

                if ($payload['status'] === 'started') {
                    $this->reportService->generateReportFile($payload['reportId']);
                }
            }

            usleep(500000); // 0.5 сек
        }
    }
}
