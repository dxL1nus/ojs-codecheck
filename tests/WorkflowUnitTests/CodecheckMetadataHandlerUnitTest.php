<?php

namespace APP\plugins\generic\codecheck\tests;

use APP\plugins\generic\codecheck\classes\Workflow\CodecheckMetadataHandler;
use PKP\tests\PKPTestCase;
use \APP\core\Request;

/**
 * @file APP/plugins/generic/codecheck/tests/WorkflowUnitTests/CodecheckMetadataHandlerUnitTest.php
 *
 * @class CodecheckMetadataHandlerUnitTest
 *
 * @brief Tests for the CodecheckMetadataHandler class
 */
class CodecheckMetadataHandlerUnitTest extends PKPTestCase
{
    private CodecheckMetadataHandler $codecheckMetadataHandler;
    /**
     * Set up the test environment
     */
    protected function setUp(): void
	{
		parent::setUp();

        /** mock GitHub client */
        $client = $this->createMock(\Github\Client::class);


        $request = new Request();

        $this->codecheckMetadataHandler = new CodecheckMetadataHandler($request, $client);
	}

    public function testImportMetadataFromGithub()
    {
        /** mock contents API */
        $contentsApi = $this->createMock(\Github\Api\Repository\Contents::class);
        $contentsApi->method('show')
            ->willReturnOnConsecutiveCalls(
                // 1st call: folder contents
                [
                    [
                        'type' => 'file',
                        'name' => 'codecheck.yml',
                        'path' => 'codecheck.yml'
                    ]
                ],

                // 2nd call: file contents
                [
                    'content' => base64_encode("test: yaml")
                ]
            );

        /** mock Repo API */
        $repoApi = $this->createMock(\Github\Api\Repo::class);

        // mock show() for default branch
        $repoApi->method('show')
            ->willReturn(['default_branch' => 'root']);

        // mock contents()
        $repoApi->method('contents')
            ->willReturn($contentsApi);

        /** mock GitHub client */
        $client = $this->createMock(\Github\Client::class);

        // client->api('repo') must return Github\Api\Repo because of return types
        $client->method('api')->willReturn($repoApi);


        $request = new Request();

        $this->codecheckMetadataHandler = new CodecheckMetadataHandler($request, $client);

        $owner = 'codecheckers';
        $repo = 'testing-dev-register';
        $repositoryUrl = 'https://github.com/' . $owner . '/' . $repo . '/';
        $actualMetadataReturnArray = $this->codecheckMetadataHandler->importMetadataFromGitHub($repositoryUrl);
        $this->assertTrue($actualMetadataReturnArray["success"]);
        $this->assertEquals($actualMetadataReturnArray["repository"], $repositoryUrl);
        $this->assertEquals($actualMetadataReturnArray["metadata"], ["test" => "yaml"]);
    }

    public function testImportMetadataFromZenodo()
    {
        $repository = 'https://zenodo.org/records/14900193';
        $actualMetadataReturnArray = $this->codecheckMetadataHandler->importMetadataFromZenodo($repository);
        $this->assertCount(3, $actualMetadataReturnArray);
    }
}