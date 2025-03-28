<?php

declare(strict_types=1);

namespace App\Report\Kafka;

// use RdKafka\Producer;
// use RdKafka\ProducerTopic;

final class KafkaProducer
{
    //    private Producer $producer;
    //    private ProducerTopic $topic;
    //
    //    public function __construct(string $broker, string $topic)
    //    {
    //        $conf = new \RdKafka\Conf();
    //        $conf->set('metadata.broker.list', $broker);
    //
    //        $this->producer = new Producer($conf);
    //        $this->topic = $this->producer->newTopic($topic);
    //    }
    //
    //    public function produceReportGenerationEvent(string $reportId): void
    //    {
    //        $message = json_encode(['reportId' => $reportId, 'status' => 'started']);
    //        $this->topic->produce(RD_KAFKA_PARTITION_UA, 0, $message);
    //        $this->producer->flush(1000);
    //    }
    //
    //    public function produceReportGeneratedEvent(string $reportId): void
    //    {
    //        $message = json_encode(['reportId' => $reportId, 'status' => 'completed']);
    //        $this->topic->produce(RD_KAFKA_PARTITION_UA, 0, $message);
    //        $this->producer->flush(1000);
    //    }
}
