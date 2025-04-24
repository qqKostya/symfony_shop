<?php

declare(strict_types=1);

use App\Report\Kafka\KafkaConsumer;
use App\Report\Kafka\KafkaProducer;
use App\Report\Service\ReportService;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->set(KafkaConsumer::class)
        ->arg('$reportService', new Reference(ReportService::class))
        ->arg('$broker', '%env(KAFKA_BROKER)%')
        ->arg('$topicName', '%env(KAFKA_TOPIC)%')
        ->arg('$logger', new Reference('logger'));

    $services
        ->set(KafkaProducer::class)
        ->arg('$broker', '%env(KAFKA_BROKER)%')
        ->arg('$topic', '%env(KAFKA_TOPIC)%');
};
