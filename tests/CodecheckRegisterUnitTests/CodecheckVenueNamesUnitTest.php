<?php

namespace APP\plugins\generic\codecheck\tests;

use APP\plugins\generic\codecheck\classes\CodecheckRegister\CodecheckVenueNames;
use APP\plugins\generic\codecheck\classes\CodecheckRegister\CodecheckApiClient;
use APP\plugins\generic\codecheck\classes\Exceptions\CurlExceptions\CurlReadException;
use APP\plugins\generic\codecheck\classes\CodecheckRegister\CodecheckVenueTypes;
use APP\plugins\generic\codecheck\classes\Exceptions\CurlExceptions\CurlInitException;
use PKP\tests\PKPTestCase;

/**
 * @file APP/plugins/generic/codecheck/tests/unittests/CodecheckVenueNamesUnitTest.php
 *
 * @class CodecheckVenueNamesUnitTest
 *
 * @brief Tests for the CodecheckVenueNames class
 */
class CodecheckVenueNamesUnitTest extends PKPTestCase
{
    /**
     * Set up the test environment
     */
    protected function setUp(): void
	{
		parent::setUp();
	}

    public function testVenueNames()
    {
        // Mock JsonApiCaller used inside CodecheckVenueTypes
        $jsonApiMockVenueTypes = $this->createMock(CodecheckApiClient::class);

        $jsonApiMockVenueTypes->expects($this->once())
                                ->method('fetch')
                                ->with('https://codecheck.org.uk/register/venues/index.json');

        // Mocked "venue types" data returned from API
        $jsonApiMockVenueTypes->method('getData')->willReturn([
            ['Venue type' => 'journal'],
            ['Venue type' => 'community'],
        ]);

        // Create CodecheckVenueTypes using the mocked jonApiCaller
        $venueTypes = new CodecheckVenueTypes($jsonApiMockVenueTypes);

        // Mock GitHub API parser for CodecheckVenueNames
        $jsonApiMockVenueNames = $this->createMock(CodecheckApiClient::class);

        $jsonApiMockVenueNames->expects($this->once())
                                ->method('fetch')
                                ->with('https://codecheck.org.uk/register/venues/index.json');
                                
        // Provide labels (some are venue types, some are venue names)
        $jsonApiMockVenueNames->method('getData')->willReturn([
            ["Issue label" => 'journal'],
            ["Issue label" => 'lifecycle journal'],
            ["Issue label" => 'community'],
            ["Issue label" => 'conference'],
            ["Issue label" => 'check-nl'],
            ["Issue label" => 'preprint'],
            ["Issue label" => 'development'],
        ]);

        // Create the tested CodecheckVenueNames class with both mocked dependencies
        $venueNames = new CodecheckVenueNames($jsonApiMockVenueNames, $venueTypes);

        $result = $venueNames->get()->toArray();

        $this->assertEquals(
            ['lifecycle journal', 'conference', 'check-nl', 'preprint'],
            $result
        );
    }

    public function testVenueNamesCurlInitException()
    {
        // Create a mock of the CodecheckApiClient
        $clientMock = $this->createMock(CodecheckApiClient::class);

        // Mock fetchLabels() so it does nothing
        $clientMock->method('fetch')
                        ->will($this->throwException(new CurlInitException('Curl initialization failed', 500)));

        $this->expectException(CurlInitException::class);
        $this->expectExceptionMessage('Curl initialization failed');
        $this->expectExceptionCode(500);

        // Inject the mock into the constructor
        new CodecheckVenueNames($clientMock);
    }

    public function testVenueNamesCurlReadException()
    {
        $testCurlHandle = curl_init();

        // Create a mock of the CodecheckApiClient
        $clientMock = $this->createMock(CodecheckApiClient::class);

        // Mock fetchLabels() so it does nothing
        $clientMock->method('fetch')
                        ->will($this->throwException(new CurlReadException($testCurlHandle)));

        $this->expectException(CurlReadException::class);
        $this->expectExceptionMessage(curl_error($testCurlHandle));
        $this->expectExceptionCode(curl_errno($testCurlHandle));

        // Inject the mock into the constructor
        new CodecheckVenueNames($clientMock);
    }
}