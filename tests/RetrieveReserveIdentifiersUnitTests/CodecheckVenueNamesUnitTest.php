<?php

namespace APP\plugins\generic\codecheck\tests;

use APP\plugins\generic\codecheck\classes\RetrieveReserveIdentifiers\CodecheckRegisterGithubIssuesApiParser;
use APP\plugins\generic\codecheck\classes\RetrieveReserveIdentifiers\CodecheckVenueNames;
use APP\plugins\generic\codecheck\classes\RetrieveReserveIdentifiers\UniqueArray;
use APP\plugins\generic\codecheck\classes\RetrieveReserveIdentifiers\JsonApiCaller;
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
        $jsonApiMock = $this->createMock(JsonApiCaller::class);

        $jsonApiMock->expects($this->once())->method('fetch');
        // Mocked "venue types" data returned from API
        $jsonApiMock->method('getData')->willReturn([
            ['Venue type' => 'journal'],
            ['Venue type' => 'community'],
        ]);

        // Create CodecheckVenueTypes using the mocked jonApiCaller
        $venueTypes = new CodecheckVenueTypes($jsonApiMock);

        // Mock GitHub API parser for CodecheckVenueNames
        $githubApiParserMock = $this->createMock(CodecheckRegisterGithubIssuesApiParser::class);

        $githubApiParserMock->expects($this->once())->method('fetchLabels');

        // Provide labels (some are venue types, some are venue names)
        $githubApiParserMock->method('getLabels')->willReturn(
            UniqueArray::from([
                'journal',
                'lifecycle journal',
                'community',
                'check-nl',
                'preprint',
                'development',
            ])
        );

        // Create the tested CodecheckVenueNames class with both mocked dependencies
        $venueNames = new CodecheckVenueNames($githubApiParserMock, $venueTypes);

        $result = $venueNames->get()->toArray();

        $this->assertEquals(
            ['lifecycle journal', 'check-nl', 'preprint'],
            $result
        );
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