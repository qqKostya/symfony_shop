<?php

declare(strict_types=1);

namespace App\Tests\Report\Controller;

use App\Tests\BaseWebTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class ReportControllerTest extends BaseWebTestCase
{
    public function testGenerateReport(): void
    {
        $client = $this->createAuthenticatedClient(true);

        $client->jsonRequest('GET', '/api/admin/generate-report');

        $this->assertResponseStatusCodeSame(Response::HTTP_ACCEPTED);

        $response = $client->getResponse();

        self::assertInstanceOf(JsonResponse::class, $response);

        $content = $response->getContent();
        $data = json_decode($content, true);
        self::assertArrayHasKey('reportId', $data);
    }

    public function testGetReport(): void
    {
        $client = $this->createAuthenticatedClient(true);

        $client->jsonRequest('GET', '/api/admin/generate-report');
        $this->assertResponseStatusCodeSame(Response::HTTP_ACCEPTED);

        $response = $client->getResponse();
        self::assertInstanceOf(JsonResponse::class, $response);

        $content = $response->getContent();
        $data = json_decode($content, true);
        self::assertArrayHasKey('reportId', $data);

        $reportId = $data['reportId'];

        $attempts = 0;
        do {
            $client->jsonRequest('GET', "/api/admin/report/{$reportId}");
            $response = $client->getResponse();

            if ($response->getStatusCode() === Response::HTTP_ACCEPTED) {
                sleep(1);
            }

            ++$attempts;
        } while ($response->getStatusCode() === Response::HTTP_ACCEPTED && $attempts < 10);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $contentType = $response->headers->get('Content-Type');
        self::assertSame('application/jsonl', $contentType);

        $contentDisposition = $response->headers->get('Content-Disposition');
        self::assertStringContainsString("attachment; filename=\"{$reportId}.jsonl\"", $contentDisposition);
    }
}
