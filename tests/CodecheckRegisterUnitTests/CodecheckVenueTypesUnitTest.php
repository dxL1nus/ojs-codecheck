<?php

namespace APP\plugins\generic\codecheck\tests;

use APP\plugins\generic\codecheck\classes\CodecheckRegister\CodecheckApiClient;
use APP\plugins\generic\codecheck\classes\CodecheckRegister\CodecheckVenueTypes;
use APP\plugins\generic\codecheck\classes\Exceptions\CurlExceptions\CurlInitException;
use APP\plugins\generic\codecheck\classes\Exceptions\CurlExceptions\CurlReadException;
use PKP\tests\PKPTestCase;

/**
 * @file APP/plugins/generic/codecheck/tests/unittests/CodecheckVenueTypesUnitTest.php
 *
 * @class CodecheckVenueTypesUnitTest
 *
 * @brief Tests for the CodecheckVenueTypes class
 */
class CodecheckVenueTypesUnitTest extends PKPTestCase
{
    /**
     * Set up the test environment
     */
    protected function setUp(): void
	{
		parent::setUp();
	}

    public function testVenueTypes()
    {
        // Create a mock of the API parser
        $apiCallerMock = $this->createMock(CodecheckApiClient::class);

        // Mock fetchLabels() so it does nothing
        $apiCallerMock->method('fetch');

        $venueTypesArray = [
            ['Venue type' => 'journal'],
            ['Venue type' => 'community'],
            ['Venue type' => 'conference'],
            ['Venue type' => 'institution'],
        ];

        $apiCallerMock->method('getData')->willReturn($venueTypesArray);

        // Inject the mock into the constructor
        $venueTypes = new CodecheckVenueTypes($apiCallerMock);

        $expectedVenueTypesArray = array_column($venueTypesArray, 'Venue type');

        $this->assertSame($venueTypes->get()->toArray(), $expectedVenueTypesArray);
    }

    public function testVenueTypesCurlInitException()
    {
        // Create a mock of the CodecheckApiClient
        $clientMock = $this->createMock(CodecheckApiClient::class);

        // Mock fetchLabels() so it does nothing
        $clientMock->method('fetch')
                        ->will($this->throwException(new CurlInitException('Curl initialization failed', 500)));

        $venueTypesArray = [
            ['Venue type' => 'journal'],
            ['Venue type' => 'community'],
            ['Venue type' => 'conference'],
            ['Venue type' => 'institution'],
        ];

        $clientMock->method('getData')->willReturn($venueTypesArray);

        $this->expectException(CurlInitException::class);
        $this->expectExceptionMessage('Curl initialization failed');
        $this->expectExceptionCode(500);

        // Inject the mock into the constructor
        new CodecheckVenueTypes($clientMock);
    }

    public function testVenueTypessCurlReadException()
    {
        $testCurlHandle = curl_init();

        // Create a mock of the CodecheckApiClient
        $clientMock = $this->createMock(CodecheckApiClient::class);

        // Mock fetchLabels() so it does nothing
        $clientMock->method('fetch')
                        ->will($this->throwException(new CurlReadException($testCurlHandle)));

        $venueTypesArray = [
            ['Venue type' => 'journal'],
            ['Venue type' => 'community'],
            ['Venue type' => 'conference'],
            ['Venue type' => 'institution'],
        ];

        $clientMock->method('getData')->willReturn($venueTypesArray);

        $this->expectException(CurlReadException::class);
        $this->expectExceptionMessage(curl_error($testCurlHandle));
        $this->expectExceptionCode(curl_errno($testCurlHandle));

        // Inject the mock into the constructor
        new CodecheckVenueTypes($clientMock);
    }
}