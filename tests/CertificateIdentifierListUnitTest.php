<?php

namespace APP\plugins\generic\codecheck\tests;

use APP\plugins\generic\codecheck\classes\RetrieveReserveIdentifiers\CertificateIdentifierList;
use APP\plugins\generic\codecheck\classes\RetrieveReserveIdentifiers\CodecheckRegisterGithubIssuesApiParser;
use APP\plugins\generic\codecheck\classes\RetrieveReserveIdentifiers\CertificateIdentifier;
use APP\plugins\generic\codecheck\classes\Exceptions\ApiFetchException;
use APP\plugins\generic\codecheck\classes\Exceptions\NoMatchingIssuesFoundException;
use PKP\tests\PKPTestCase;

/**
 * @file APP/plugins/generic/codecheck/tests/unittests/CertificateIdentifierListUnitTests.php
 *
 * @class CertificateIdentifierListUnitTest
 *
 * @brief Tests for the CertificateIdentifierList class
 */
class CertificateIdentifierListUnitTest extends PKPTestCase
{
    private CertificateIdentifierList $certificateIdentifierList;

    /**
     * Set up the test environment
     */
    protected function setUp(): void
	{
		parent::setUp();
        $this->certificateIdentifierList = new CertificateIdentifierList();
	}


    // Test CertificateIdentifierList::getRawIdentifier();
    public function testGetRawIdentifierTitleIsEmpty()
    {
        $title = '';
        $rawIdentifier = CertificateIdentifierList::getRawIdentifier($title);
        // The rawIdentifier for an empty string should be null
        $this->assertNull($rawIdentifier);
    }

    public function testGetRawIdentifierTitleIsOnlyWhitespace()
    {
        $title = '     ';
        $rawIdentifier = CertificateIdentifierList::getRawIdentifier($title);
        // The rawIdentifier for an empty string should be null
        $this->assertNull($rawIdentifier);
    }

    public function testGetRawIdentifierTitleHasNoSplitter()
    {
        $title = 'Daniel Nüst 2025-034';
        $rawIdentifier = CertificateIdentifierList::getRawIdentifier($title);
        // The rawIdentifier for an empty string should be null
        $this->assertNull($rawIdentifier);
    }

    public function testGetRawIdentifierTitleExpectedFormat()
    {
        $title = 'Daniel Nüst | 2025-012';
        $expectedRawIdentifier = '2025-012';
        $rawIdentifier = CertificateIdentifierList::getRawIdentifier($title);
        // The rawIdentifier should match the expected Raw Identifier
        $this->assertSame($rawIdentifier, $expectedRawIdentifier);
    }

    public function testGetRawIdentifierTitleExpectedFormatLongRunningNumber()
    {
        $title = 'Daniel Nüst | 2025-123456789';
        $expectedRawIdentifier = '2025-123456789';
        $rawIdentifier = CertificateIdentifierList::getRawIdentifier($title);
        // The rawIdentifier should match the expected Raw Identifier
        $this->assertSame($rawIdentifier, $expectedRawIdentifier);
    }

    public function testGetRawIdentifierTitleExpectedFormatWithWhitespaces()
    {
        $title = 'Daniel Nüst       |              2025-012                                               ';
        $expectedRawIdentifier = '2025-012';
        $rawIdentifier = CertificateIdentifierList::getRawIdentifier($title);
        // The rawIdentifier should match the expected Raw Identifier
        $this->assertSame($rawIdentifier, $expectedRawIdentifier);
    }

    public function testGetRawIdentifierTitleExpectedSecondSplitter()
    {
        $title = 'Daniel Nüst | 2025-012 |';
        $rawIdentifier = CertificateIdentifierList::getRawIdentifier($title);
        // The rawIdentifier should match the expected Raw Identifier
        $this->assertNull($rawIdentifier);
    }

    public function testGetRawIdentifierTitleExpectedSecondSplitterBeforeIdentifier()
    {
        $title = 'Author | abc | 2025-999';
        $expectedRawIdentifier = '2025-999';
        $rawIdentifier = CertificateIdentifierList::getRawIdentifier($title);
        // The rawIdentifier should match the expected Raw Identifier
        $this->assertSame($rawIdentifier, $expectedRawIdentifier);
    }

    public function testGetRawIdentifierTitleExpectedMultipleIdentifiers()
    {
        $title = 'Author - 2025-001 | 2026-001 - 2026-002';
        $rawIdentifier = CertificateIdentifierList::getRawIdentifier($title);
        // The rawIdentifier should match the expected Raw Identifier
        $this->assertNull($rawIdentifier);
    }

    public function testGetRawIdentifierTitleLongExpected()
    {
        $title = 'Daniel Nüst | 2025-012/2025-017';
        $expectedRawIdentifier = '2025-012/2025-017';
        $rawIdentifier = CertificateIdentifierList::getRawIdentifier($title);
        // The rawIdentifier should match the expected Raw Identifier
        $this->assertSame($rawIdentifier, $expectedRawIdentifier);
    }

    public function testFromApiWithMockApiParserWithEmptyFetchedIssues()
    {
        // Create a mock of the API parser
        $apiParserMock = $this->createMock(CodecheckRegisterGithubIssuesApiParser::class);

        // Configure fetchIssues() to do nothing (simulate successful fetch)
        $apiParserMock->method('fetchIssues');

        $identifierList = CertificateIdentifierList::fromApi($apiParserMock);
        $actualIdentifierListString = $identifierList->toStr();
        $expectedIdentifierListString = "Certificate Identifiers:\n";
        $this->assertSame($actualIdentifierListString, $expectedIdentifierListString);
    }

    public function testFromApiWithMockApiParserThrowingExceptionsForFetchedIssues()
    {
        // Create a mock of the API parser
        $apiParserMock = $this->createMock(CodecheckRegisterGithubIssuesApiParser::class);

        // Configure fetchIssues() to do nothing (simulate successful fetch)
        $apiParserMock->method('fetchIssues')
                        ->will($this->throwException(new ApiFetchException('API failed')));;

        try {
            $identifierList = CertificateIdentifierList::fromApi($apiParserMock);
            $this->fail('Expected ApiFetchException was not thrown');
        } catch (ApiFetchException $e) {
            $this->assertSame('API failed', $e->getMessage());
        }

        // Configure fetchIssues() to do nothing (simulate successful fetch)
        $apiParserMock->method('fetchIssues')
                        ->will($this->throwException(new NoMatchingIssuesFoundException('API failed')));;

        try {
            $identifierList = CertificateIdentifierList::fromApi($apiParserMock);
            $this->fail('Expected NoMatchingIssuesFoundException was not thrown');
        } catch (NoMatchingIssuesFoundException $e) {
            $this->assertSame('API failed', $e->getMessage());
        }
    }

    public function testFromApiWithMockApiParserWithSomeFetchedIssues()
    {
        // Create a mock of the API parser
        $apiParser = $this->createMock(CodecheckRegisterGithubIssuesApiParser::class);

        $apiParser->method('getIssues')
              ->willReturn([
                    ['title' => 'Daniel Nüst | 2024-012'],
                    ['title' => 'Example Authors et al. | 2024-012/2024-013'],
                    ['title' => 'Daniel Nüst | 2024-012 | ']
              ]);

        $identifierList = CertificateIdentifierList::fromApi($apiParser);
        $actualIdentifierListString = $identifierList->toStr();
        $expectedIdentifierListString = "Certificate Identifiers:\n2024-012\n2024-013\n";
        $this->assertSame($actualIdentifierListString, $expectedIdentifierListString);
    }

    public function testFilledCertificateIdentifierListCount()
    {
        $identifierList = new CertificateIdentifierList();
        $identifierList->appendToCertificateIdList('2024-012');
        $actualIdentifierListCount = $identifierList->getNumberOfIdentifiers();
        $expectedIdentifierListCount = 1;
        $this->assertSame($expectedIdentifierListCount, $actualIdentifierListCount);
    }

    public function testFilledCertificateIdentifierListToStr()
    {
        $identifierList = new CertificateIdentifierList();
        $identifierList->appendToCertificateIdList('2022-012');
        $actualIdentifierListString = $identifierList->toStr();
        $expectedIdentifierListString = "Certificate Identifiers:\n2022-012\n";
        $this->assertSame($expectedIdentifierListString, $actualIdentifierListString);
    }

    public function testFilledCertificateIdentifierListGetNewestIdentifier()
    {
        $identifierList = new CertificateIdentifierList();
        $identifierList->appendToCertificateIdList('2022-012/2022-014');
        $actualIdentifier = $identifierList->getNewestIdentifier();
        $expectedIdentifier = new CertificateIdentifier(2022, 14);
        $this->assertSame($expectedIdentifier->toStr(), $actualIdentifier->toStr());
    }

    public function testFilledCertificateIdentifierListSortDesc()
    {
        $identifierList = new CertificateIdentifierList();
        $identifierList->appendToCertificateIdList('2022-012/2022-014');
        $identifierList->appendToCertificateIdList('2025-014');
        $identifierList->sortDesc();
        $actualIdentifierString = $identifierList->toStr();
        $expectedIdentifierString = "Certificate Identifiers:\n2025-014\n2022-014\n2022-013\n2022-012\n";
        $this->assertSame($expectedIdentifierString, $actualIdentifierString);
    }

    public function testFilledCertificateIdentifierListSortAsc()
    {
        $identifierList = new CertificateIdentifierList();
        $identifierList->appendToCertificateIdList('2022-012/2022-014');
        $identifierList->appendToCertificateIdList('2025-014');
        $identifierList->sortAsc();
        $actualIdentifierString = $identifierList->toStr();
        $expectedIdentifierString = "Certificate Identifiers:\n2022-012\n2022-013\n2022-014\n2025-014\n";
        $this->assertSame($expectedIdentifierString, $actualIdentifierString);
    }
}