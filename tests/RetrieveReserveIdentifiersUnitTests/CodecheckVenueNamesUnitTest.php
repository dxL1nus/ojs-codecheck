<?php

namespace APP\plugins\generic\codecheck\tests;

use APP\plugins\generic\codecheck\classes\RetrieveReserveIdentifiers\CodecheckRegisterGithubIssuesApiParser;
use APP\plugins\generic\codecheck\classes\RetrieveReserveIdentifiers\CodecheckVenueNames;
use APP\plugins\generic\codecheck\classes\RetrieveReserveIdentifiers\UniqueArray;
use APP\plugins\generic\codecheck\classes\Exceptions\ApiFetchException;
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
        // Create a mock of the API parser
        $apiParserMock = $this->createMock(CodecheckRegisterGithubIssuesApiParser::class);

        // Mock fetchLabels() so it does nothing
        $apiParserMock->method('fetchLabels');

        // Mock getLabels() to return a UniqueArray with some mock labels
        $mockLabels = UniqueArray::from(['check-nl', 'lifecycle journal']);
        $apiParserMock->method('getLabels')->willReturn($mockLabels);

        // Inject the mock into the constructor
        $venueNames = new CodecheckVenueNames($apiParserMock);

        $this->assertSame($venueNames->get()->toArray(), ['check-nl', 'lifecycle journal']);
    }

    public function testVenueNamesApiException()
    {
        // Create a mock of the API parser
        $apiParserMock = $this->createMock(CodecheckRegisterGithubIssuesApiParser::class);

        // Mock fetchLabels() so it does nothing
        $apiParserMock->method('fetchLabels')
                        ->will($this->throwException(new ApiFetchException('API failed')));

        $this->expectException(ApiFetchException::class);
        $this->expectExceptionMessage('API failed');

        // Inject the mock into the constructor
        $venueNames = new CodecheckVenueNames($apiParserMock);
    }
}