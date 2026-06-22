<?php

declare(strict_types=1);

namespace Test\Integration\ColorLab;

use App\ColorLab\Domain\Event\PaintReferenceCreatedEvent;
use Test\Integration\ApiTestCase;

class PaintReferenceApiTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setCurrentUserId('019661b9-a000-7000-8000-000000000001');
    }

    public function testCreate(): void
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

        self::assertResponseStatusCodeSame(201);
        $this->assertEventDispatched(PaintReferenceCreatedEvent::class, static function (PaintReferenceCreatedEvent $event): void {
            self::assertSame('Crimson', $event->name);
            self::assertSame('019661b9-a000-7000-8000-000000000001', (string) $event->ownedBy);
        });
    }

    public function testList(): void
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
        $this->request('POST', '/color-lab/paint-references', [
            'name' => 'Black',
            'brandHandle' => $brandHandle,
            'rangeHandle' => 'game-color',
            'paintTypeHandle' => $paintTypeHandle,
        ]);

        $this->request('GET', '/color-lab/paint-references');

        self::assertResponseIsSuccessful();
        $refs = $this->responseJson();
        self::assertCount(2, $refs);
        self::assertContains('Crimson', array_column($refs, 'name'));
        self::assertContains('Black', array_column($refs, 'name'));
    }

    public function testGet(): void
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
        $list = $this->responseJson();
        $handle = $list[0]['handle'];

        $this->request('GET', '/color-lab/paint-references/'.$handle);

        self::assertResponseIsSuccessful();
        $ref = $this->responseJson();
        self::assertSame('Crimson', $ref['name']);
        self::assertSame($handle, $ref['handle']);
        self::assertNull($ref['colorHandle']);
    }

    public function testGetNotFound(): void
    {
        $this->request('GET', '/color-lab/paint-references/nonexistent-handle');

        self::assertResponseStatusCodeSame(404);
    }
}
