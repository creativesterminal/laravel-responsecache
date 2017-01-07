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
     *
     * @return string
     */
    public function getHashFor(Request $request)
    {
        return config('cache.prefix').md5(
            sprintf('%s/%s', $request->getRequestUri(), $this->cacheProfile->cacheNameSuffix($request))
        );
    }
}
