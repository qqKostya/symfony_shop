<?php

declare(strict_types=1);

namespace App\Report\Kafka;

use RdKafka\Conf;
use RdKafka\Producer;
use RdKafka\ProducerTopic;

final class KafkaProducer
{
    private Producer $producer;

    private ProducerTopic $topic;
    private const EVENT_REPORT_GENERATION_STARTED = 'report_generation_started';
    private const EVENT_REPORT_GENERATION_COMPLETED = 'report_generation_completed';

    public function __construct(string $broker, string $topic)
    {
        $conf = new Conf();
        $conf->set('metadata.broker.list', $broker);

        $this->producer = new Producer($conf);
        $this->topic = $this->producer->newTopic($topic);
    }

    /**
     * Универсальный метод для отправки событий в Kafka.
     */
    public function produceEvent(string $eventType, array $payload): void
    {
        $payload['event'] = $eventType;
        $message = json_encode($payload, JSON_UNESCAPED_UNICODE);
        $this->topic->produce(RD_KAFKA_PARTITION_UA, 0, $message);
        $this->producer->flush(1000);
    }

    public function produceReportGenerationEvent(string $reportId): void
    {
        $this->produceEvent(self::EVENT_REPORT_GENERATION_STARTED, [
            'reportId' => $reportId,
            'status'   => 'started',
        ]);
    }

    public function produceReportGeneratedEvent(string $reportId): void
    {
        $this->produceEvent(self::EVENT_REPORT_GENERATION_COMPLETED, [
            'reportId' => $reportId,
            'status'   => 'completed',
        ]);
    }
}
