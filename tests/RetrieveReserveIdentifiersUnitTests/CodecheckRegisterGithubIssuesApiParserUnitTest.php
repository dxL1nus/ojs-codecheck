<?php

namespace APP\plugins\generic\codecheck\tests;

use APP\plugins\generic\codecheck\classes\RetrieveReserveIdentifiers\CodecheckRegisterGithubIssuesApiParser;
use APP\plugins\generic\codecheck\classes\RetrieveReserveIdentifiers\CertificateIdentifier;
use APP\plugins\generic\codecheck\classes\Exceptions\ApiFetchException;
use APP\plugins\generic\codecheck\classes\Exceptions\ApiCreateException;
use APP\plugins\generic\codecheck\classes\Exceptions\NoMatchingIssuesFoundException;
use PKP\tests\PKPTestCase;

/**
 * @file APP/plugins/generic/codecheck/tests/unittests/CodecheckRegisterGithubIssuesApiParserUnitTest.php
 *
 * @class CodecheckRegisterGithubIssuesApiParserUnitTest
 *
 * @brief Tests for the CodecheckRegisterGithubIssuesApiParser class
 */
class CodecheckRegisterGithubIssuesApiParserUnitTest extends PKPTestCase
{
    /**
     * Set up the test environment
     */
    protected function setUp(): void
	{
		parent::setUp();
	}

    public function testGithubParserGetLabels()
    {
        // Create a mock of the API parser
        $apiParser = new CodecheckRegisterGithubIssuesApiParser();

        $this->assertSame($apiParser->getLabels()->toArray(), []);
    }

    public function testGithubParserGetIssues()
    {
        // Create a mock of the API parser
        $apiParser = new CodecheckRegisterGithubIssuesApiParser();

        $this->assertSame($apiParser->getIssues(), []);
    }

    public function testGithubParserFetchIssues()
    {
        // 1. Mock the "issues API" object  
        $issueApiMock = $this->createMock(\Github\Api\Issue::class);

        $issueApiMock->method('all')
            ->willReturn([
                ['title' => 'Alice | 2025-001'],
                ['title' => 'Issue without a certificate Identifier'],
            ]);

        // 2. Mock the client so `api('issue')` returns $issueApiMock
        $clientMock = $this->createMock(\Github\Client::class);

        $clientMock->method('api')
            ->with('issue')
            ->willReturn($issueApiMock);

        // 3. Inject the mock into your parser
        $apiParser = new CodecheckRegisterGithubIssuesApiParser($clientMock);

        // 4. Run method
        $apiParser->fetchIssues();

        // 5. Assert
        $issues = $apiParser->getIssues();

        $this->assertCount(1, $issues);
        $this->assertEquals('Alice | 2025-001', $issues[0]['title']);
    }

    public function testGithubParserFetchLabels()
    {
        $labelsApiMock = $this->createMock(\Github\Api\Issue\Labels::class);

        $labelsApiMock->method('all')
            ->willReturn([
                ['name' => 'institution'],
                ['name' => 'check-nl'],
            ]);

        $issueApiMock = $this->createMock(\Github\Api\Issue::class);

        $issueApiMock->method('labels')
            ->willReturn($labelsApiMock);

        $clientMock = $this->createMock(\Github\Client::class);

        $clientMock->method('api')
            ->with('issue')
            ->willReturn($issueApiMock);

        // --- 4. Inject mock client into the parser ---
        $parser = new CodecheckRegisterGithubIssuesApiParser($clientMock);

        // --- 5. Execute ---
        $parser->fetchLabels();

        // --- 6. Assert ---
        $labels = $parser->getLabels()->toArray();

        $this->assertCount(2, $labels);
        $this->assertContains('institution', $labels);
        $this->assertContains('check-nl', $labels);
    }

    public function testGithubParserFetchLabelsThrowsException()
    {
        $labelsApiMock = $this->createMock(\Github\Api\Issue\Labels::class);

        $labelsApiMock->method('all')
            ->will($this->throwException(new ApiFetchException('')));;

        $issueApiMock = $this->createMock(\Github\Api\Issue::class);

        $issueApiMock->method('labels')
            ->willReturn($labelsApiMock);

        $clientMock = $this->createMock(\Github\Client::class);

        $clientMock->method('api')
            ->with('issue')
            ->willReturn($issueApiMock);

        // Inject mock client into the parser
        $parser = new CodecheckRegisterGithubIssuesApiParser($clientMock);

        $this->expectException(ApiFetchException::class);
        $this->expectExceptionMessage("Failed fetching the GitHub Issue Labels for the Venue Names\n");

        // Execute
        $parser->fetchLabels();
    }

    public function testAddIssueCreatesIssueAndReturnsUrl()
    {
        // Setup environment token
        $_ENV['CODECHECK_REGISTER_GITHUB_TOKEN'] = 'testtoken123';

        // Mock CertificateIdentifier
        $certMock = $this->createMock(CertificateIdentifier::class);
        $certMock->method('toStr')
            ->willReturn('2025-001');

        // Mock the Issue API
        $issueApiMock = $this->createMock(\Github\Api\Issue::class);

        // Expect "create" to be called with these specific parameters
        $issueApiMock->expects($this->once())
            ->method('create')
            ->with(
                'codecheckers',
                'testing-dev-register',
                [
                    'title'  => 'Daniel Nüst et al. | 2025-001',
                    'body'   => '',
                    'labels' => ['id assigned', 'institution', 'check-nl']
                ]
            )
            ->willReturn([
                'html_url' => 'https://github.com/codecheckers/testing-dev-register/issues/123'
            ]);

        // Mock Client
        $clientMock = $this->createMock(\Github\Client::class);

        // Expect authentication with the correct token
        $clientMock->expects($this->once())
            ->method('authenticate')
            ->with('testtoken123', null, \Github\Client::AUTH_ACCESS_TOKEN);

        // When "api('issue')" is called, return our mocked Issue API
        $clientMock->method('api')
            ->with('issue')
            ->willReturn($issueApiMock);

        // Inject mock client into parser
        $parser = new CodecheckRegisterGithubIssuesApiParser($clientMock);

        // Run method
        $url = $parser->addIssue(
            $certMock,
            'institution',
            'check-nl',
            'Daniel Nüst et al.'
        );

        // Assert returned URL
        $this->assertEquals(
            'https://github.com/codecheckers/testing-dev-register/issues/123',
            $url
        );
    }

    public function testAddIssueCreatesIssueAndThrowsException()
    {
        // Setup environment token
        $_ENV['CODECHECK_REGISTER_GITHUB_TOKEN'] = 'testtoken123';

        // Mock CertificateIdentifier
        $certMock = $this->createMock(CertificateIdentifier::class);
        $certMock->method('toStr')
            ->willReturn('2025-001');

        // Mock the Issue API
        $issueApiMock = $this->createMock(\Github\Api\Issue::class);

        // Expect "create" to be called with these specific parameters
        $issueApiMock->expects($this->once())
            ->method('create')
            ->will($this->throwException(new ApiFetchException('')));;

        // Mock Client
        $clientMock = $this->createMock(\Github\Client::class);

        // Expect authentication with the correct token
        $clientMock->expects($this->once())
            ->method('authenticate')
            ->with('testtoken123', null, \Github\Client::AUTH_ACCESS_TOKEN);

        // When "api('issue')" is called, return our mocked Issue API
        $clientMock->method('api')
            ->with('issue')
            ->willReturn($issueApiMock);

        // Inject mock client into parser
        $parser = new CodecheckRegisterGithubIssuesApiParser($clientMock);

        $this->expectException(ApiCreateException::class);
        $this->expectExceptionMessage("Error while adding the new GitHub issue with the new Certificate Identifier\n");

        // Run method
        $url = $parser->addIssue(
            $certMock,
            'institution',
            'check-nl',
            'Daniel Nüst et al.'
        );
    }

    public function testFetchIssuesThrowsApiFetchException()
    {
        // Mock Client
        $clientMock = $this->createMock(\Github\Client::class);

        // When "api('issue')" is called, return our mocked Issue API
        $clientMock->method('api')
            ->with('issue')
            ->will($this->throwException(new ApiFetchException('')));;

        // Inject mock client into parser
        $parser = new CodecheckRegisterGithubIssuesApiParser($clientMock);

        $this->expectException(ApiFetchException::class);
        $this->expectExceptionMessage("Failed fetching the GitHub Issues\n");

        // Run method
        $parser->fetchIssues();
    }

    public function testFetchIssuesThrowsNoMatchingIssuesFoundException()
    {
        // Mock the Issue API
        $issueApiMock = $this->createMock(\Github\Api\Issue::class);

        // Make ->all() return NO ISSUES  
        // This forces the "empty(allissues)" check to trigger the exception
        $issueApiMock->method('all')->willReturn([]);

        // Mock Client
        $clientMock = $this->createMock(\Github\Client::class);

        // Make ->api('issue') return our issue mock
        $clientMock
            ->method('api')
            ->with('issue')
            ->willReturn($issueApiMock);

        // Inject mock client into parser
        $parser = new CodecheckRegisterGithubIssuesApiParser($clientMock);

        $this->expectException(NoMatchingIssuesFoundException::class);
        $this->expectExceptionMessage("There was no Issue found with a '|' inside the GitHub Codecheck Register.");

        // Run method
        $parser->fetchIssues();
    }
}