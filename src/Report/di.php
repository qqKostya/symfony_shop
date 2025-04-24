<?php

declare(strict_types=1);

use App\Report\Kafka\KafkaConsumer;
use App\Report\Kafka\KafkaProducer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->set(KafkaConsumer::class)
        ->args([
            '$broker' => param('env(KAFKA_BROKER)'),
            '$topicName' => param('env(KAFKA_TOPIC)'),
        ]);

    $services
        ->set(KafkaProducer::class)
        ->args([
            '$broker' => param('env(KAFKA_BROKER)'),
            '$topic' => param('env(KAFKA_TOPIC)'),
        ]);
};
