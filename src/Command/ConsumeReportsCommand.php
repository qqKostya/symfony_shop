<?php

declare(strict_types=1);

namespace App\Command;

use App\Report\Kafka\KafkaConsumer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:consume-reports',
    description: 'Consumes Kafka messages to generate reports',
)]
final class ConsumeReportsCommand extends Command
{
    public function __construct(
        private readonly KafkaConsumer $kafkaConsumer,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Listening for report generation events...</info>');

        $this->kafkaConsumer->consume();

        return Command::SUCCESS;
    }
}
