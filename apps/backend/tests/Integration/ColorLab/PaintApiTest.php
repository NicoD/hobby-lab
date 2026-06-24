<?php

declare(strict_types=1);

namespace Test\Integration\ColorLab;

use App\ColorLab\Stash\Domain\Paint\Event\PaintCreatedEvent;
use Test\Integration\ApiTestCase;

class PaintApiTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setCurrentUserId('019661b9-a000-7000-8000-000000000001');
    }

    private function createCatalogPaint(): string
    {
        $this->request('POST', '/color-lab/catalog/paint-types', ['name' => 'Standard']);
        $this->request('POST', '/color-lab/catalog/brands', ['name' => 'Vallejo']);

        $this->request('GET', '/color-lab/catalog/brands');
        $brands = $this->responseJson();
        $brandHandle = $brands['items'][0]['handle'];

        $this->request('GET', '/color-lab/catalog/paint-types');
        $types = $this->responseJson();
        $paintTypeHandle = $types[0]['handle'];

        $this->request('POST', '/color-lab/catalog/paints', [
            'name' => 'Crimson',
            'brand' => $brandHandle,
            'paintType' => $paintTypeHandle,
        ]);

        $this->request('GET', '/color-lab/catalog/paints');
        $paints = $this->responseJson();

        return $paints['items'][0]['handle'];
    }

    public function testCreate(): void
    {
        $paintHandle = $this->createCatalogPaint();

        $this->request('POST', '/color-lab/stash/paints', [
            'paintHandle' => $paintHandle,
            'purchasedAt' => '2026-02-15',
        ]);

        self::assertResponseStatusCodeSame(201);
        $this->assertEventDispatched(PaintCreatedEvent::class, static function (PaintCreatedEvent $event) use ($paintHandle): void {
            self::assertSame($paintHandle, (string) $event->paintHandle);
            self::assertSame('019661b9-a000-7000-8000-000000000001', (string) $event->ownedBy);
            self::assertSame('2026-02-15', $event->purchasedAt?->format('Y-m-d'));
        });
    }

    public function testCreateWithoutPurchasedAt(): void
    {
        $paintHandle = $this->createCatalogPaint();

        $this->request('POST', '/color-lab/stash/paints', [
            'paintHandle' => $paintHandle,
        ]);

        self::assertResponseStatusCodeSame(201);
        $this->assertEventDispatched(PaintCreatedEvent::class, static function (PaintCreatedEvent $event): void {
            self::assertNull($event->purchasedAt);
        });
    }

    public function testList(): void
    {
        $paintHandle = $this->createCatalogPaint();

        $this->request('POST', '/color-lab/stash/paints', ['paintHandle' => $paintHandle]);
        $this->request('POST', '/color-lab/stash/paints', ['paintHandle' => $paintHandle]);

        $this->request('GET', '/color-lab/stash/paints');

        self::assertResponseIsSuccessful();
        $paints = $this->responseJson();
        self::assertCount(2, $paints);
    }

    public function testGet(): void
    {
        $paintHandle = $this->createCatalogPaint();

        $this->request('POST', '/color-lab/stash/paints', [
            'paintHandle' => $paintHandle,
            'purchasedAt' => '2026-02-15',
        ]);

        $this->request('GET', '/color-lab/stash/paints');
        $list = $this->responseJson();
        $id = $list[0]['id'];

        $this->request('GET', '/color-lab/stash/paints/'.$id);

        self::assertResponseIsSuccessful();
        $paint = $this->responseJson();
        self::assertSame($id, $paint['id']);
        self::assertSame($paintHandle, $paint['paintHandle']);
        self::assertSame('2026-02-15', $paint['purchasedAt']);
    }

    public function testGetNotFound(): void
    {
        $this->request('GET', '/color-lab/stash/paints/00000000-0000-0000-0000-000000000000');

        self::assertResponseStatusCodeSame(404);
    }
}
