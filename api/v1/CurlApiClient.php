<?php

namespace APP\plugins\generic\codecheck\api\v1;

use CurlHandle;

class CurlApiClient implements ApiClientInterface
{
    private CurlHandle $curl_handle;

    public function initialize(string $url): CurlHandle|bool
    {
        $curl_handle = curl_init($url);
        if($curl_handle === false) {
            return false;
        }
        $this->curl_handle = $curl_handle;
        return $curl_handle;
    }

    public function get($url): string|bool
    {
        curl_setopt($this->curl_handle, CURLOPT_RETURNTRANSFER, true);
        // follow redirects
        curl_setopt($this->curl_handle, CURLOPT_FOLLOWLOCATION, true);
        return curl_exec($this->curl_handle);
    }
}