<?php

namespace Spatie\ResponseCache\Test;

use ResponseCache;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class IntegrationTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @test
     */
    public function it_will_cache_a_get_request()
    {
        $firstResponse = $this->call('GET', '/random');
        $secondResponse = $this->call('GET', '/random');

        $this->assertRegularResponse($firstResponse);
        $this->assertCachedResponse($secondResponse);

        $this->assertSameResponse($firstResponse, $secondResponse);
    }

    /**
     * @test
     */
    public function it_will_not_cache_errors()
    {
        $firstResponse = $this->call('GET', '/notfound');
        $secondResponse = $this->call('GET', '/notfound');

        $this->assertRegularResponse($firstResponse);
        $this->assertRegularResponse($secondResponse);
    }

    /**
     * @test
     */
    public function it_will_not_cache_a_post_request()
    {
        $firstResponse = $this->call('POST', '/random');
        $secondResponse = $this->call('POST', '/random');

        $this->assertRegularResponse($firstResponse);
        $this->assertRegularResponse($secondResponse);

        $this->assertDifferentResponse($firstResponse, $secondResponse);
    }

    /**
     * @test
     */
    public function it_can_flush_the_cached_requests()
    {
        $firstResponse = $this->call('GET', '/random');
        $this->assertRegularResponse($firstResponse);

        ResponseCache::flush();

        $secondResponse = $this->call('GET', '/random');
        $this->assertRegularResponse($secondResponse);

        $this->assertDifferentResponse($firstResponse, $secondResponse);
    }

    /**
     * @test
     */
    public function it_will_not_cache_routes_with_the_doNotCacheResponse_middleware()
    {
        $firstResponse = $this->call('GET', '/uncacheable');
        $secondResponse = $this->call('GET', '/uncacheable');

        $this->assertRegularResponse($firstResponse);
        $this->assertRegularResponse($secondResponse);

        $this->assertDifferentResponse($firstResponse, $secondResponse);
    }

    /**
     * @test
     */
    public function it_will_not_cache_request_when_the_package_is_not_enable()
    {
        $this->app['config']->set('laravel-responsecache.enabled', false);

        $firstResponse = $this->call('GET', '/random');
        $secondResponse = $this->call('GET', '/random');

        $this->assertRegularResponse($firstResponse);
        $this->assertRegularResponse($secondResponse);

        $this->assertDifferentResponse($firstResponse, $secondResponse);
    }

    /**
     * @test
     */
    public function it_will_not_serve_cached_requests_when_it_is_disabled_in_the_config_file()
    {
        $firstResponse = $this->call('GET', '/random');

        $this->app['config']->set('laravel-responsecache.enabled', false);

        $secondResponse = $this->call('GET', '/random');

        $this->assertRegularResponse($firstResponse);
        $this->assertRegularResponse($secondResponse);

        $this->assertDifferentResponse($firstResponse, $secondResponse);
    }
}
