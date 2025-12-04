<?php

namespace APP\plugins\generic\codecheck\classes\RetrieveReserveIdentifiers;

use APP\plugins\generic\codecheck\classes\Exceptions\ApiFetchException;

class JsonApiCaller
{
    private string $url;
    private array $jsonData = [];
    private $fetcher;

    function __construct(string $url, ?callable $fetcher = null)
    {
        $this->url = $url;

        $this->fetcher = $fetcher ?? fn ($url) => file_get_contents($url);
    }

    public function fetch()
    {
        // Fetch JSON from API
        $response = call_user_func($this->fetcher, $this->url);

        // throw error if no data was fetched from API
        if ($response === FALSE) {
            throw new ApiFetchException("Error fetching the Codecheck API data");
        }

        // Decode JSON into PHP array
        $this->jsonData = json_decode($response, true);
    }

    public function getData(): array
    {
        return $this->jsonData;
    }
}