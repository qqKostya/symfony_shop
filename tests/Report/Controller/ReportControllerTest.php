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
        $client = $this->createAuthenticatedClient(true);

        $client->jsonRequest('GET', '/api/admin/generate-report');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $response = $client->getResponse();

        self::assertInstanceOf(BinaryFileResponse::class, $response);

        $filePath = $response->getFile()->getPathname();
        self::assertFileExists($filePath);

        $filesystem = new Filesystem();
        if ($filesystem->exists($filePath)) {
            $filesystem->remove($filePath);
        }
    }
}
