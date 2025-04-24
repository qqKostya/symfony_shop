<?php

declare(strict_types=1);

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function build(ContainerBuilder $container): void
    {
        // Загружаем общие сервисы и конфигурацию
        parent::build($container);

        // Явно указываем путь к конфигурации для Report
        $loader = new PhpFileLoader($container, new FileLocator(\dirname(__DIR__) . '/src/Report'));
        $loader->load('di.php');
    }
}
