<?php

declare(strict_types=1);

namespace Test;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class ApiTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected string $currentUserId = 'anonymous';

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->resetDatabase();
    }

    protected function setCurrentUserId(string $id): void
    {
        $this->currentUserId = $id;
    }

    protected function request(
        string $method,
        string $uri,
        array $body = [],
    ): void {
        $this->client->request(
            $method,
            $uri,
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_USER_ID' => $this->currentUserId,
            ],
            content: [] !== $body ? (string) json_encode($body) : null,
        );
    }

    /** @return array<mixed> */
    protected function responseJson(): array
    {
        return (array) json_decode((string) $this->client->getResponse()->getContent(), true);
    }

    private function resetDatabase(): void
    {
        $em = self::getContainer()->get('doctrine')->getManager();
        $conn = $em->getConnection();

        foreach ($em->getMetadataFactory()->getAllMetadata() as $metadata) {
            $conn->executeStatement('DELETE FROM '.$metadata->getTableName());
        }
    }
}
