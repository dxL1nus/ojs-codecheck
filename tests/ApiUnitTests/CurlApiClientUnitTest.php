<?php

namespace APP\plugins\generic\codecheck\tests;

use APP\plugins\generic\codecheck\classes\CodecheckRegister\JsonApiCaller;
use APP\plugins\generic\codecheck\classes\Exceptions\ApiFetchException;
use PKP\tests\PKPTestCase;

/**
 * @file APP/plugins/generic/codecheck/tests/ApiUnitTests/CurlApiClientUnitTest.php
 *
 * @class CurlApiClientUnitTest
 *
 * @brief Tests for the CurlApiClient class
 */
class CurlApiClientUnitTest extends PKPTestCase
{
    /**
     * Set up the test environment
     */
    protected function setUp(): void
	{
		parent::setUp();
	}

    public function testJsonApiCallerFetchSuccess()
    {
        // Mock fetcher (simulates HTTP)
        $fetcher = function ($url) {
            return json_encode([ "some" => "data" ]);
        };

        $caller = new JsonApiCaller("http://example.com", $fetcher);

        $caller->fetch();

        $this->assertEquals(
            ["some" => "data"],
            $caller->getData()
        );
    }

    public function testFetchFails()
    {
        $this->expectException(ApiFetchException::class);

        // Mock failure
        $fetcher = fn ($url) => false;

        $caller = new JsonApiCaller("http://example.com", $fetcher);
        $caller->fetch();
    }
}