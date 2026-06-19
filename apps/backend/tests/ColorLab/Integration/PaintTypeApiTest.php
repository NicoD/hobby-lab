<?php

declare(strict_types=1);

namespace Test\ColorLab\Integration;

use App\ColorLab\Domain\Event\PaintTypeCreatedEvent;
use Test\ApiTestCase;

class PaintTypeApiTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setCurrentUserId('019661b9-a000-7000-8000-000000000001');
    }

    public function testCreate(): void
    {
        $this->request('POST', '/color-lab/paint-types', ['name' => 'Standard']);

        self::assertResponseStatusCodeSame(201);
        $this->assertEventDispatched(PaintTypeCreatedEvent::class, static function (PaintTypeCreatedEvent $event): void {
            self::assertSame('Standard', $event->name);
            self::assertSame('019661b9-a000-7000-8000-000000000001', (string) $event->ownedBy);
        });
    }

    public function testList(): void
    {
        $this->request('POST', '/color-lab/paint-types', ['name' => 'Standard']);
        $this->request('POST', '/color-lab/paint-types', ['name' => 'Wash']);

        $this->request('GET', '/color-lab/paint-types');

        self::assertResponseIsSuccessful();
        $types = $this->responseJson();
        self::assertCount(2, $types);
        self::assertContains('Standard', array_column($types, 'name'));
        self::assertContains('Wash', array_column($types, 'name'));
    }

    public function testGet(): void
    {
        $this->request('POST', '/color-lab/paint-types', ['name' => 'Metallic']);

        $this->request('GET', '/color-lab/paint-types');
        $list = $this->responseJson();
        $handle = $list[0]['handle'];

        $this->request('GET', '/color-lab/paint-types/'.$handle);

        self::assertResponseIsSuccessful();
        $type = $this->responseJson();
        self::assertSame('Metallic', $type['name']);
        self::assertSame($handle, $type['handle']);
    }

    public function testGetNotFound(): void
    {
        $this->request('GET', '/color-lab/paint-types/nonexistent-handle');

        self::assertResponseStatusCodeSame(404);
    }
}
