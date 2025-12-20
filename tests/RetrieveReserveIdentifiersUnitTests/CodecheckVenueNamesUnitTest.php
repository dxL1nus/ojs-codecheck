<?php

namespace APP\plugins\generic\codecheck\tests;

use APP\plugins\generic\codecheck\classes\RetrieveReserveIdentifiers\JsonApiCaller;
use APP\plugins\generic\codecheck\classes\RetrieveReserveIdentifiers\CodecheckVenueNames;
use APP\plugins\generic\codecheck\classes\Exceptions\ApiFetchException;
use APP\plugins\generic\codecheck\classes\RetrieveReserveIdentifiers\CodecheckVenueTypes;
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
        $jsonApiMockVenueTypes = $this->createMock(JsonApiCaller::class);

        $jsonApiMockVenueTypes->expects($this->once())->method('fetch');
        // Mocked "venue types" data returned from API
        $jsonApiMockVenueTypes->method('getData')->willReturn([
            ['Venue type' => 'journal'],
            ['Venue type' => 'community'],
        ]);

        // Create CodecheckVenueTypes using the mocked jonApiCaller
        $venueTypes = new CodecheckVenueTypes($jsonApiMockVenueTypes);

        // Mock GitHub API parser for CodecheckVenueNames
        $jsonApiMockVenueNames = $this->createMock(JsonApiCaller::class);

        $jsonApiMockVenueNames->expects($this->once())->method('fetch');

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
            ['lifecycle journal', 'conference', 'check-nl', 'preprint',],
            $result
        );
    }

    public function testVenueNamesApiException()
    {
        // Create a mock of the API parser
        $apiParserMock = $this->createMock(JsonApiCaller::class);

        // Mock fetchLabels() so it does nothing
        $apiParserMock->method('fetch')
                        ->will($this->throwException(new ApiFetchException('API failed')));

        $this->expectException(ApiFetchException::class);
        $this->expectExceptionMessage('API failed');

        // Inject the mock into the constructor
        $venueNames = new CodecheckVenueNames($apiParserMock);
    }
}