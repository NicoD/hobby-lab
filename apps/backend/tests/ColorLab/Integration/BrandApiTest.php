<?php

declare(strict_types=1);

namespace Test\ColorLab\Integration;

use Test\ApiTestCase;

class BrandApiTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setCurrentUserId('019661b9-a000-7000-8000-000000000001');
    }

    public function testCreate(): void
    {
        $this->request('POST', '/color-lab/brands', ['name' => 'Vallejo']);

        self::assertResponseStatusCodeSame(201);
    }

    public function testList(): void
    {
        $this->request('POST', '/color-lab/brands', ['name' => 'Vallejo']);
        $this->request('POST', '/color-lab/brands', ['name' => 'Citadel']);

        $this->request('GET', '/color-lab/brands');

        self::assertResponseIsSuccessful();
        $brands = $this->responseJson();
        self::assertCount(2, $brands);
        self::assertContains('Vallejo', array_column($brands, 'name'));
        self::assertContains('Citadel', array_column($brands, 'name'));
    }

    public function testGet(): void
    {
        $this->request('POST', '/color-lab/brands', ['name' => 'Vallejo']);

        $this->request('GET', '/color-lab/brands');
        $list = $this->responseJson();
        $handle = $list[0]['handle'];

        $this->request('GET', '/color-lab/brands/'.$handle);

        self::assertResponseIsSuccessful();
        $brand = $this->responseJson();
        self::assertSame('Vallejo', $brand['name']);
        self::assertSame($handle, $brand['handle']);
        self::assertSame([], $brand['ranges']);
    }

    public function testGetNotFound(): void
    {
        $this->request('GET', '/color-lab/brands/nonexistent-handle');

        self::assertResponseStatusCodeSame(404);
    }
}
