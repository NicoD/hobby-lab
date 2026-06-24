<?php

declare(strict_types=1);

namespace Test\Integration\ColorLab;

use App\ColorLab\Catalog\Domain\PaintType\Event\PaintTypeCreatedEvent;
use Test\Integration\ApiTestCase;

class PaintTypeApiTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setCurrentUserId('019661b9-a000-7000-8000-000000000001');
    }

    public function testCreate(): void
    {
        $this->request('POST', '/color-lab/catalog/paint-types', ['name' => 'Standard']);

        self::assertResponseStatusCodeSame(201);
        $this->assertEventDispatched(PaintTypeCreatedEvent::class, static function (PaintTypeCreatedEvent $event): void {
            self::assertSame('Standard', $event->name);
            self::assertSame('019661b9-a000-7000-8000-000000000001', (string) $event->ownedBy);
        });
    }

    public function testList(): void
    {
        $this->request('POST', '/color-lab/catalog/paint-types', ['name' => 'Standard']);
        $this->request('POST', '/color-lab/catalog/paint-types', ['name' => 'Wash']);

        $this->request('GET', '/color-lab/catalog/paint-types');

        self::assertResponseIsSuccessful();
        $types = $this->responseJson();
        self::assertCount(2, $types);
        self::assertContains('Standard', array_column($types, 'name'));
        self::assertContains('Wash', array_column($types, 'name'));
    }

    public function testGet(): void
    {
        $this->request('POST', '/color-lab/catalog/paint-types', ['name' => 'Metallic']);

        $this->request('GET', '/color-lab/catalog/paint-types');
        $list = $this->responseJson();
        $handle = $list[0]['handle'];

        $this->request('GET', '/color-lab/catalog/paint-types/'.$handle);

        self::assertResponseIsSuccessful();
        $type = $this->responseJson();
        self::assertSame('Metallic', $type['name']);
        self::assertSame($handle, $type['handle']);
    }

    public function testGetNotFound(): void
    {
        $this->request('GET', '/color-lab/catalog/paint-types/nonexistent-handle');

        self::assertResponseStatusCodeSame(404);
    }
}
