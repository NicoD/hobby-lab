<?php

declare(strict_types=1);

namespace Test\Integration\ColorLab;

use App\ColorLab\Domain\Event\PaintCreatedEvent;
use Test\Integration\ApiTestCase;

class PaintApiTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setCurrentUserId('019661b9-a000-7000-8000-000000000001');
    }

    private function createPaintReference(): string
    {
        $this->request('POST', '/color-lab/paint-types', ['name' => 'Standard']);
        $this->request('POST', '/color-lab/brands', ['name' => 'Vallejo']);

        $this->request('GET', '/color-lab/brands');
        $brands = $this->responseJson();
        $brandHandle = $brands[0]['handle'];

        $this->request('GET', '/color-lab/paint-types');
        $types = $this->responseJson();
        $paintTypeHandle = $types[0]['handle'];

        $this->request('POST', '/color-lab/paint-references', [
            'name' => 'Crimson',
            'brandHandle' => $brandHandle,
            'rangeHandle' => 'game-color',
            'paintTypeHandle' => $paintTypeHandle,
        ]);

        $this->request('GET', '/color-lab/paint-references');
        $refs = $this->responseJson();

        return $refs[0]['id'];
    }

    public function testCreate(): void
    {
        $paintReferenceId = $this->createPaintReference();

        $this->request('POST', '/color-lab/paints', [
            'paintReferenceId' => $paintReferenceId,
            'purchasedAt' => '2026-02-15',
        ]);

        self::assertResponseStatusCodeSame(201);
        $this->assertEventDispatched(PaintCreatedEvent::class, static function (PaintCreatedEvent $event) use ($paintReferenceId): void {
            self::assertSame($paintReferenceId, (string) $event->paintReferenceId);
            self::assertSame('019661b9-a000-7000-8000-000000000001', (string) $event->ownedBy);
            self::assertSame('2026-02-15', $event->purchasedAt?->format('Y-m-d'));
        });
    }

    public function testCreateWithoutPurchasedAt(): void
    {
        $paintReferenceId = $this->createPaintReference();

        $this->request('POST', '/color-lab/paints', [
            'paintReferenceId' => $paintReferenceId,
        ]);

        self::assertResponseStatusCodeSame(201);
        $this->assertEventDispatched(PaintCreatedEvent::class, static function (PaintCreatedEvent $event): void {
            self::assertNull($event->purchasedAt);
        });
    }

    public function testList(): void
    {
        $paintReferenceId = $this->createPaintReference();

        $this->request('POST', '/color-lab/paints', ['paintReferenceId' => $paintReferenceId]);
        $this->request('POST', '/color-lab/paints', ['paintReferenceId' => $paintReferenceId]);

        $this->request('GET', '/color-lab/paints');

        self::assertResponseIsSuccessful();
        $paints = $this->responseJson();
        self::assertCount(2, $paints);
    }

    public function testGet(): void
    {
        $paintReferenceId = $this->createPaintReference();

        $this->request('POST', '/color-lab/paints', [
            'paintReferenceId' => $paintReferenceId,
            'purchasedAt' => '2026-02-15',
        ]);

        $this->request('GET', '/color-lab/paints');
        $list = $this->responseJson();
        $id = $list[0]['id'];

        $this->request('GET', '/color-lab/paints/'.$id);

        self::assertResponseIsSuccessful();
        $paint = $this->responseJson();
        self::assertSame($id, $paint['id']);
        self::assertSame($paintReferenceId, $paint['paintReferenceId']);
        self::assertSame('2026-02-15', $paint['purchasedAt']);
    }

    public function testGetNotFound(): void
    {
        $this->request('GET', '/color-lab/paints/00000000-0000-0000-0000-000000000000');

        self::assertResponseStatusCodeSame(404);
    }
}
