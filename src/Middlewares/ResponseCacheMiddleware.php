<?php

namespace Spatie\ResponseCache\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Spatie\ResponseCache\ResponseCache;

class ResponseCacheMiddleware
{
    /**
     * @var \Spatie\ResponseCache\ResponseCache
     */
    protected $responseCache;

    public function __construct(ResponseCache $responseCache)
    {
        $this->responseCache = $responseCache;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next)
    {
        if ($this->responseCache->shouldGetCachedResponse($request) && $this->responseCache->hasCached($request)) {
            $response = $this->responseCache->getCachedResponseFor($request);

            if (! $response->headers->has('ETag')) {
                $response->setEtag(sha1($response->getContent()));
            }
            $response->isNotModified($request);

            return $response;
        }

        $response = $next($request);

        if ($this->responseCache->shouldCache($request, $response)) {
            $this->responseCache->cacheResponse($request, $response);
        }

        return $response;
    }
}
