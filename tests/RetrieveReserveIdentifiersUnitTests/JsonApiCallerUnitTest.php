<?php

namespace APP\plugins\generic\codecheck\tests;

use APP\plugins\generic\codecheck\classes\RetrieveReserveIdentifiers\JsonApiCaller;
use APP\plugins\generic\codecheck\classes\Exceptions\ApiFetchException;
use PKP\tests\PKPTestCase;

/**
 * @file APP/plugins/generic/codecheck/tests/unittests/JsonApiCallerUnitTest.php
 *
 * @class JsonApiCallerUnitTest
 *
 * @brief Tests for the JsonApiCaller class
 */
class JsonApiCallerUnitTest extends PKPTestCase
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