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
        $this->request('POST', '/color-lab/paint-references', ['name' => 'Vallejo Red']);

        self::assertResponseStatusCodeSame(201);
        $this->assertEventDispatched(PaintReferenceCreatedEvent::class, static function (PaintReferenceCreatedEvent $event): void {
            self::assertSame('Vallejo Red', $event->name);
            self::assertSame('019661b9-a000-7000-8000-000000000001', (string) $event->ownedBy);
        });
    }

    public function testCreateWithRelatedAggregates(): void
    {
        $brandHandle = $this->createBrand('Vallejo');
        $colorHandle = $this->createColor('Red');

        $this->request('POST', '/color-lab/paint-references', [
            'name' => 'Vallejo Red',
            'brand' => $brandHandle,
            'color' => $colorHandle,
        ]);

        self::assertResponseStatusCodeSame(201);
        $ref = $this->responseJson();
        self::assertSame($brandHandle, $ref['brandHandle']);
        self::assertSame($colorHandle, $ref['colorHandle']);
    }

    public function testList(): void
    {
        $this->request('POST', '/color-lab/paint-references', ['name' => 'Vallejo Red']);
        $this->request('POST', '/color-lab/paint-references', ['name' => 'Citadel Blue']);

        $this->request('GET', '/color-lab/paint-references');

        self::assertResponseIsSuccessful();
        $response = $this->responseJson();
        self::assertArrayHasKey('items', $response);
        self::assertArrayHasKey('total', $response);
        self::assertArrayHasKey('page', $response);
        self::assertArrayHasKey('limit', $response);
        self::assertSame(2, $response['total']);
        self::assertSame(1, $response['page']);
        self::assertCount(2, $response['items']);
        self::assertContains('Vallejo Red', array_column($response['items'], 'name'));
        self::assertContains('Citadel Blue', array_column($response['items'], 'name'));
    }

    public function testListIncludesDenormalizedLabels(): void
    {
        $brandHandle = $this->createBrand('Vallejo');
        $colorHandle = $this->createColor('Red');

        $this->request('POST', '/color-lab/paint-references', [
            'name' => 'Vallejo Red',
            'brand' => $brandHandle,
            'color' => $colorHandle,
        ]);

        $this->request('GET', '/color-lab/paint-references');

        self::assertResponseIsSuccessful();
        $item = $this->responseJson()['items'][0];
        self::assertSame($brandHandle, $item['brandId']);
        self::assertSame('Vallejo', $item['brandName']);
        self::assertSame($colorHandle, $item['colorId']);
        self::assertSame('Red', $item['colorName']);
        self::assertNull($item['paintTypeId']);
        self::assertNull($item['paintTypeName']);
    }

    public function testListWithoutRelatedAggregatesHasNullLabels(): void
    {
        $this->request('POST', '/color-lab/paint-references', ['name' => 'Unknown']);

        $this->request('GET', '/color-lab/paint-references');

        self::assertResponseIsSuccessful();
        $item = $this->responseJson()['items'][0];
        self::assertNull($item['brandId']);
        self::assertNull($item['brandName']);
        self::assertNull($item['colorId']);
        self::assertNull($item['colorName']);
    }

    public function testListPagination(): void
    {
        foreach (['Alpha', 'Beta', 'Gamma'] as $name) {
            $this->request('POST', '/color-lab/paint-references', ['name' => $name]);
        }

        $this->request('GET', '/color-lab/paint-references?page=1&limit=2');

        self::assertResponseIsSuccessful();
        $response = $this->responseJson();
        self::assertSame(3, $response['total']);
        self::assertSame(2, $response['limit']);
        self::assertCount(2, $response['items']);
    }

    public function testListSearch(): void
    {
        $this->request('POST', '/color-lab/paint-references', ['name' => 'Vallejo Red']);
        $this->request('POST', '/color-lab/paint-references', ['name' => 'Citadel Blue']);

        $this->request('GET', '/color-lab/paint-references?search=vallejo');

        self::assertResponseIsSuccessful();
        $response = $this->responseJson();
        self::assertSame(1, $response['total']);
        self::assertSame('Vallejo Red', $response['items'][0]['name']);
    }

    public function testListSortByName(): void
    {
        $this->request('POST', '/color-lab/paint-references', ['name' => 'Citadel Blue']);
        $this->request('POST', '/color-lab/paint-references', ['name' => 'Vallejo Red']);

        $this->request('GET', '/color-lab/paint-references?sort=name&dir=asc');

        self::assertResponseIsSuccessful();
        $response = $this->responseJson();
        self::assertSame('Citadel Blue', $response['items'][0]['name']);
        self::assertSame('Vallejo Red', $response['items'][1]['name']);
    }

    public function testListIsolatedByUser(): void
    {
        $this->setCurrentUserId('019661b9-a000-7000-8000-000000000001');
        $this->request('POST', '/color-lab/paint-references', ['name' => 'Vallejo Red']);

        $this->setCurrentUserId('019661b9-a000-7000-8000-000000000002');
        $this->request('GET', '/color-lab/paint-references');

        self::assertResponseIsSuccessful();
        $response = $this->responseJson();
        self::assertSame(0, $response['total']);
    }

    public function testGet(): void
    {
        $this->request('POST', '/color-lab/paint-references', ['name' => 'Vallejo Red']);

        $this->request('GET', '/color-lab/paint-references');
        $list = $this->responseJson();
        $handle = $list['items'][0]['handle'];

        $this->request('GET', '/color-lab/paint-references/'.$handle);

        self::assertResponseIsSuccessful();
        $ref = $this->responseJson();
        self::assertSame('Vallejo Red', $ref['name']);
        self::assertSame($handle, $ref['handle']);
        self::assertNull($ref['colorHandle']);
    }

    public function testGetNotFound(): void
    {
        $this->request('GET', '/color-lab/paint-references/nonexistent-handle');

        self::assertResponseStatusCodeSame(404);
    }

    private function createBrand(string $name): string
    {
        $this->request('POST', '/color-lab/brands', ['name' => $name]);
        $this->request('GET', '/color-lab/brands');
        $brands = $this->responseJson()['items'];

        foreach ($brands as $brand) {
            if ($brand['name'] === $name) {
                return $brand['handle'];
            }
        }

        throw new \RuntimeException("Brand '{$name}' not found after creation");
    }

    private function createColor(string $name): string
    {
        $this->request('POST', '/color-lab/colors', ['name' => $name]);
        $this->request('GET', '/color-lab/colors');
        $colors = $this->responseJson()['items'];

        foreach ($colors as $color) {
            if ($color['name'] === $name) {
                return $color['handle'];
            }
        }

        throw new \RuntimeException("Color '{$name}' not found after creation");
    }
}
