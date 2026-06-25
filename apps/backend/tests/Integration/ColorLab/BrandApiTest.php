<?php

declare(strict_types=1);

namespace Test\Integration\ColorLab;

use App\ColorLab\Catalog\Domain\Brand\Event\BrandCreatedEvent;
use Test\Integration\ApiTestCase;

class BrandApiTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setCurrentUserId('019661b9-a000-7000-8000-000000000001');
    }

    public function testCreate(): void
    {
        $this->request('POST', '/color-lab/catalog/brands', ['name' => 'Vallejo']);

        self::assertResponseStatusCodeSame(201);
        $this->assertEventDispatched(BrandCreatedEvent::class, static function (BrandCreatedEvent $event): void {
            self::assertSame('Vallejo', $event->name);
            self::assertSame('019661b9-a000-7000-8000-000000000001', (string) $event->ownedBy);
        });
    }

    public function testList(): void
    {
        $this->request('POST', '/color-lab/catalog/brands', ['name' => 'Vallejo']);
        $this->request('POST', '/color-lab/catalog/brands', ['name' => 'Citadel']);

        $this->request('GET', '/color-lab/catalog/brands');

        self::assertResponseIsSuccessful();
        $response = $this->responseJson();
        self::assertArrayHasKey('items', $response);
        self::assertArrayHasKey('total', $response);
        self::assertArrayHasKey('page', $response);
        self::assertArrayHasKey('limit', $response);
        self::assertSame(2, $response['total']);
        self::assertSame(1, $response['page']);
        self::assertSame(30, $response['limit']);
        self::assertCount(2, $response['items']);
        self::assertContains('Vallejo', array_column($response['items'], 'name'));
        self::assertContains('Citadel', array_column($response['items'], 'name'));
    }

    public function testListPagination(): void
    {
        foreach (['Alpha', 'Beta', 'Gamma'] as $name) {
            $this->request('POST', '/color-lab/catalog/brands', ['name' => $name]);
        }

        $this->request('GET', '/color-lab/catalog/brands?page=1&limit=2');

        self::assertResponseIsSuccessful();
        $response = $this->responseJson();
        self::assertSame(3, $response['total']);
        self::assertSame(1, $response['page']);
        self::assertSame(2, $response['limit']);
        self::assertCount(2, $response['items']);
    }

    public function testListSearch(): void
    {
        $this->request('POST', '/color-lab/catalog/brands', ['name' => 'Vallejo']);
        $this->request('POST', '/color-lab/catalog/brands', ['name' => 'Citadel']);

        $this->request('GET', '/color-lab/catalog/brands?search=val');

        self::assertResponseIsSuccessful();
        $response = $this->responseJson();
        self::assertSame(1, $response['total']);
        self::assertSame('Vallejo', $response['items'][0]['name']);
    }

    public function testListSortByName(): void
    {
        $this->request('POST', '/color-lab/catalog/brands', ['name' => 'Citadel']);
        $this->request('POST', '/color-lab/catalog/brands', ['name' => 'Vallejo']);

        $this->request('GET', '/color-lab/catalog/brands?sort=name&dir=asc');

        self::assertResponseIsSuccessful();
        $response = $this->responseJson();
        self::assertSame('Citadel', $response['items'][0]['name']);
        self::assertSame('Vallejo', $response['items'][1]['name']);
    }

    public function testListIsolatedByUser(): void
    {
        $this->setCurrentUserId('019661b9-a000-7000-8000-000000000001');
        $this->request('POST', '/color-lab/catalog/brands', ['name' => 'Vallejo']);

        $this->setCurrentUserId('019661b9-a000-7000-8000-000000000002');
        $this->request('GET', '/color-lab/catalog/brands');

        self::assertResponseIsSuccessful();
        $response = $this->responseJson();
        self::assertSame(0, $response['total']);
    }

    public function testGet(): void
    {
        $this->request('POST', '/color-lab/catalog/brands', ['name' => 'Vallejo']);

        $this->request('GET', '/color-lab/catalog/brands');
        $list = $this->responseJson();
        $handle = $list['items'][0]['handle'];

        $this->request('GET', '/color-lab/catalog/brands/'.$handle);

        self::assertResponseIsSuccessful();
        $brand = $this->responseJson();
        self::assertSame('Vallejo', $brand['name']);
        self::assertSame($handle, $brand['handle']);
        self::assertSame([], $brand['ranges']);
    }

    public function testGetNotFound(): void
    {
        $this->request('GET', '/color-lab/catalog/brands/nonexistent-handle');

        self::assertResponseStatusCodeSame(404);
    }
}
