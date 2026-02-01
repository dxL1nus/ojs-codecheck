<?php

namespace APP\plugins\generic\codecheck\tests;

use APP\plugins\generic\codecheck\classes\CodecheckRegister\CodecheckApiClient;
use APP\plugins\generic\codecheck\classes\CodecheckRegister\CodecheckVenueTypes;
use APP\plugins\generic\codecheck\classes\Exceptions\ApiFetchException;
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

    public function testVenueTypesExpectException()
    {
        // Create a mock of the API parser
        $apiCallerMock = $this->createMock(CodecheckApiClient::class);

        // Mock fetchLabels() so it does nothing
        $apiCallerMock->method('fetch')
                        ->will($this->throwException(new ApiFetchException('API failed')));;

        $venueTypesArray = [
            ['Venue type' => 'journal'],
            ['Venue type' => 'community'],
            ['Venue type' => 'conference'],
            ['Venue type' => 'institution'],
        ];

        $apiCallerMock->method('getData')->willReturn($venueTypesArray);

        $this->expectException(ApiFetchException::class);
        $this->expectExceptionMessage('API failed');

        // Inject the mock into the constructor
        $venueTypes = new CodecheckVenueTypes($apiCallerMock);
    }
}