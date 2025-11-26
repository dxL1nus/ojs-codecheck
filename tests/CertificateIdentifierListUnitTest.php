<?php

namespace APP\plugins\generic\codecheck\tests;

use APP\plugins\generic\codecheck\classes\RetrieveReserveIdentifiers\CertificateIdentifierList;
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

    public function testGetRawIdentifierTitleExpectedFormat()
    {
        $title = 'Daniel Nüst | 2025-012';
        $expectedRawIdentifier = '2025-012';
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
}