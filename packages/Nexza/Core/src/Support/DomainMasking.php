<?php

namespace Nexza\Core\Support;

use Illuminate\Support\Facades\Config;

class DomainMasking
{

    /**
     * Mask the requested domain with the proxy as setup in the configuration.
     *
     * @param string $domain
     * @return string
     */
    public static function maskDomain($domain)
    {
        $domainList = self::getDomainListsToMask();

        if ($domainList === false) {
            return $domain;
        }

        $key = self::getDomainKeyToMask($domain);

        if ($key === false) {
            return $domain;
        }

        $maskedDomain = Config::get('b2cin.proxy.' . $key, false);

        if ($maskedDomain === false) {
            return $domain;
        }

        return self::getUrlScheme() . str_ireplace(['https://', 'http://'], [''], $maskedDomain);
    }

    /**
     * Get the list of domain keys to be masked.
     *
     * @return mixed boolean|array
     */
    protected static function getDomainListsToMask()
    {
        return Config::get('b2cin.proxy', false);
    }

    /**
     * Return the domain key that matches the proxy setup.
     *
     * @param string $domain
     * @return mixed string|boolean (false, if array_search fails)
     */
    protected static function getDomainKeyToMask($domain)
    {
        $domainRequested = str_ireplace(['http://', 'https://'], [''], $domain);

        return array_search($domainRequested, Config::get('b2cin', []));
    }

    /**
     * Get URL scheme (http|https).
     *
     * @param void
     * @return string
     */
    protected static function getUrlScheme()
    {
        return request()->getScheme().'://';
    }
}
