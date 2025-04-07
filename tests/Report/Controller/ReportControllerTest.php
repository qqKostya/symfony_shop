<?php

declare(strict_types=1);

namespace App\Tests\Report\Controller;

use App\Tests\BaseWebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

final class ReportControllerTest extends BaseWebTestCase
{
    public function testGenerateReport(): void
    {
        // Создаем аутентифицированного клиента
        $client = $this->createAuthenticatedClient(true);

        // Отправляем GET-запрос на создание отчета
        $client->jsonRequest('GET', '/api/admin/generate-report');

        // Проверка статуса ответа (должен быть 200 OK)
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // Получаем ответ
        $response = $client->getResponse();

        // Проверка, что ответ содержит файл
        self::assertInstanceOf(BinaryFileResponse::class, $response);

        // Проверка, что файл существует в файловой системе
        $filePath = $response->getFile()->getPathname();
        self::assertFileExists($filePath);

        // Очистка (удаляем файл после теста)
        $filesystem = new Filesystem();
        if ($filesystem->exists($filePath)) {
            $filesystem->remove($filePath);
        }
    }
}
