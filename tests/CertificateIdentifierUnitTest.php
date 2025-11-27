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
class CertificateIdentifierUnitTest extends PKPTestCase
{
    /**
     * Set up the test environment
     */
    protected function setUp(): void
	{
		parent::setUp();
	}

    public function testIdentifierWithYearAndRunningNumber()
    {
        $year = 2025;
        $number = 1;
        $identifier = new CertificateIdentifier($year, $number);
        $this->assertSame($identifier->toStr(), "2025-001");
    }

    public function testIdentifierFromStr()
    {
        $identifier_str = '2025-001';
        $identifier = CertificateIdentifier::fromStr($identifier_str);
        $this->assertSame($identifier->toStr(), "2025-001");
    }

    public function testIdentifierSetYear()
    {
        $year = 2025;
        $number = 1;
        $identifier = new CertificateIdentifier($year, $number);
        $identifier->setYear(2024);
        $this->assertSame($identifier->toStr(), "2024-001");
    }

    public function testIdentifierSetRunningNumber()
    {
        $year = 2025;
        $number = 1;
        $identifier = new CertificateIdentifier($year, $number);
        $identifier->setNumber(2624);
        $this->assertSame($identifier->toStr(), "2025-2624");
    }

    public function testIdentifierNewUniqueIdentifierFromIdentifierList()
    {
        // Create a mock of the API parser
        $apiParser = $this->createMock(CodecheckRegisterGithubIssuesApiParser::class);

        $apiParser->method('getIssues')
              ->willReturn([
                    ['title' => 'Example Authors et al. | 2025-001/2025-003'],
              ]);

        $identifierList = CertificateIdentifierList::fromApi($apiParser);

        $newUniqueIdentifier = CertificateIdentifier::newUniqueIdentifier($identifierList);

        $this->assertSame($newUniqueIdentifier->toStr(), "2025-004");
    }

    public function testIdentifierNewUniqueIdentifierFromIdentifierListBrandNewYear()
    {
        // Create a mock of the API parser
        $apiParser = $this->createMock(CodecheckRegisterGithubIssuesApiParser::class);

        $apiParser->method('getIssues')
              ->willReturn([
                    ['title' => 'Example Authors et al. | 2024-001/2024-003'],
              ]);

        $identifierList = CertificateIdentifierList::fromApi($apiParser);

        $newUniqueIdentifier = CertificateIdentifier::newUniqueIdentifier($identifierList);

        $expectedIdentifier = (string) date("Y") . '-001';

        $this->assertSame($newUniqueIdentifier->toStr(), $expectedIdentifier);
    }
}