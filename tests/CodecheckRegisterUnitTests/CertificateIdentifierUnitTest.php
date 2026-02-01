<?php

namespace APP\plugins\generic\codecheck\tests;

use APP\plugins\generic\codecheck\classes\CodecheckRegister\CertificateIdentifierList;
use APP\plugins\generic\codecheck\classes\CodecheckRegister\CodecheckGithubRegisterApiClient;
use APP\plugins\generic\codecheck\classes\CodecheckRegister\CertificateIdentifier;
use PKP\tests\PKPTestCase;

/**
 * @file APP/plugins/generic/codecheck/tests/unittests/CertificateIdentifierUnitTests.php
 *
 * @class CertificateIdentifierUnitTest
 *
 * @brief Tests for the CertificateIdentifier class
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
        $year = (int) date('Y');
        // Create a mock of the API parser
        $apiParser = $this->createMock(CodecheckGithubRegisterApiClient::class);

        $apiParser->expects($this->once())
                    ->method('fetchIssues');
        $apiParser->method('getIssues')
              ->willReturn([
                    ['title' => "Example Authors et al. | $year-001/$year-003"],
              ]);

        $identifierList = CertificateIdentifierList::fromApi($apiParser);

        $newUniqueIdentifier = CertificateIdentifier::newUniqueIdentifier($identifierList);

        $this->assertSame("$year-004", $newUniqueIdentifier->toStr());
    }

    public function testIdentifierNewUniqueIdentifierFromIdentifierListBrandNewYear()
    {
        // Create a mock of the API parser
        $apiParser = $this->createMock(CodecheckGithubRegisterApiClient::class);

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