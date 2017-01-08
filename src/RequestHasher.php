<?php

namespace Spatie\ResponseCache;

use Illuminate\Http\Request;
use Spatie\ResponseCache\CacheProfiles\CacheProfile;

class RequestHasher
{
    /**
     * @var \Spatie\ResponseCache\CacheProfiles\CacheProfile
     */
    protected $cacheProfile;

    public function __construct(CacheProfile $cacheProfile)
    {
        $this->cacheProfile = $cacheProfile;
    }

    /**
     * Get a hash value for the given request.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $suffix
     *
     * @return string
     */
    public function getHashFor(Request $request, string $suffix = null)
    {
        if (! $suffix) {
            $suffix = $this->cacheProfile->cacheNameSuffix($request);
        }

        return config('cache.prefix').md5(
            sprintf('%s/%s', $request->getRequestUri(), $suffix)
        );
    }
}
