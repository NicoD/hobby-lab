<?php

declare(strict_types=1);

namespace Test\Integration\ColorLab;

use App\ColorLab\Domain\Event\ColorCreatedEvent;
use Test\Integration\ApiTestCase;

class ColorApiTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setCurrentUserId('019661b9-a000-7000-8000-000000000001');
    }

    public function testCreate(): void
    {
        $this->request('POST', '/color-lab/colors', ['name' => 'Red']);

        self::assertResponseStatusCodeSame(201);
        $this->assertEventDispatched(ColorCreatedEvent::class, static function (ColorCreatedEvent $event): void {
            self::assertSame('Red', $event->name);
            self::assertSame('019661b9-a000-7000-8000-000000000001', (string) $event->ownedBy);
        });
    }

    public function testList(): void
    {
        $this->request('POST', '/color-lab/colors', ['name' => 'Red']);
        $this->request('POST', '/color-lab/colors', ['name' => 'Green']);

        $this->request('GET', '/color-lab/colors');

        self::assertResponseIsSuccessful();
        $colors = $this->responseJson();
        self::assertCount(2, $colors);
        self::assertContains('Red', array_column($colors, 'name'));
        self::assertContains('Green', array_column($colors, 'name'));
    }

    public function testGet(): void
    {
        $this->request('POST', '/color-lab/colors', ['name' => 'Red']);

        $this->request('GET', '/color-lab/colors');
        $list = $this->responseJson();
        $handle = $list[0]['handle'];

        $this->request('GET', '/color-lab/colors/'.$handle);

        self::assertResponseIsSuccessful();
        $color = $this->responseJson();
        self::assertSame('Red', $color['name']);
        self::assertSame($handle, $color['handle']);
    }

    public function testGetNotFound(): void
    {
        $this->request('GET', '/color-lab/colors/nonexistent-handle');

        self::assertResponseStatusCodeSame(404);
    }
}
