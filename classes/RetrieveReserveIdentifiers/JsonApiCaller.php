<?php

namespace APP\plugins\generic\codecheck\classes\RetrieveReserveIdentifiers;

use APP\plugins\generic\codecheck\classes\Exceptions\ApiFetchException;

class JsonApiCaller
{
    private string $url;
    private array $jsonData = [];
    private $fetcher;

    /**
     * Initializes the Caller for the CODECHECK JSON Api
     * 
     * @param string $url The URL the Api Caller should fetch from
     */
    function __construct(string $url, ?callable $fetcher = null)
    {
        $this->url = $url;

        $this->fetcher = $fetcher ?? fn ($url) => file_get_contents($url);
    }

    /**
     * This function fetches all the data from the given URL
     */
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

    /**
     * Gets the fetched JSON Data
     * 
     * @return array Returns the fetched and json decoded data from the API
     */
    public function getData(): array
    {
        return $this->jsonData;
    }
}