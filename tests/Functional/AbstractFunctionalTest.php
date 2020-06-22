<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use Liip\TestFixturesBundle\Test\FixturesTrait;

abstract class AbstractFunctionalTest extends ApiTestCase
{
    use FixturesTrait;

    /** @var Client */
    protected $client;

    public function setUp()
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->loadFixtures([
            'App\DataFixtures\CategoryFixtures',
            'App\DataFixtures\AttributeFixtures',
            'App\DataFixtures\SongFixtures',
            'App\DataFixtures\FilmFixtures',
            'App\DataFixtures\NumberFixtures',
        ]);
    }
}