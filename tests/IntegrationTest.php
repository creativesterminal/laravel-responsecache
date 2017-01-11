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

    /**
     * @test
     */
    public function it_will_serve_304_if_not_modified__and_response_not_yet_on_cache_and_cache_full_response()
    {
        $expectedEtag = "\"478fa8c0ee80fa1fe3946c71510a35f5df93f6fa\"";

        $firstResponse = $this->call('GET', '/fixed', [], [], [], ['HTTP_IF_NONE_MATCH' => $expectedEtag]);
        $secondResponse = $this->call('GET', '/fixed', [], [], [], ['HTTP_IF_NONE_MATCH' => $expectedEtag]);
        $thirdResponse = $this->call('GET', '/fixed');

        $this->assertRegularResponse($firstResponse);
        $this->assertEquals(304, $firstResponse->getStatusCode());
        $this->assertEquals($expectedEtag, $firstResponse->getEtag());

        $this->assertCachedResponse($secondResponse);
        $this->assertEquals(304, $secondResponse->getStatusCode());
        $this->assertEquals($expectedEtag, $secondResponse->getEtag());

        $this->assertCachedResponse($thirdResponse);
        $this->assertEquals(200, $thirdResponse->getStatusCode());
        $this->assertEquals($expectedEtag, $thirdResponse->getEtag());
    }
}
