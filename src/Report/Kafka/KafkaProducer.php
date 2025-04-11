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

    public function __construct(string $broker, string $topic)
    {
        $conf = new Conf();
        $conf->set('metadata.broker.list', $broker);

        $this->producer = new Producer($conf);
        $this->topic = $this->producer->newTopic($topic);
    }

    public function produceReportGenerationEvent(string $reportId): void
    {
        $this->produce([
            'reportId' => $reportId,
            'status'   => 'started',
        ]);
    }

    public function produceReportGeneratedEvent(string $reportId): void
    {
        $this->produce([
            'reportId' => $reportId,
            'status'   => 'completed',
        ]);
    }

    private function produce(array $payload): void
    {
        $message = json_encode($payload, JSON_UNESCAPED_UNICODE);
        $this->topic->produce(RD_KAFKA_PARTITION_UA, 0, $message);
        $this->producer->flush(1000);
    }
}
