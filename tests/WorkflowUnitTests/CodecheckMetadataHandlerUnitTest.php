<?php

namespace APP\plugins\generic\codecheck\tests;

use APP\plugins\generic\codecheck\classes\Workflow\CodecheckMetadataHandler;
use PKP\tests\PKPTestCase;
use \APP\core\Request;
use APP\plugins\generic\codecheck\api\v1\CurlApiClient;
use APP\plugins\generic\codecheck\classes\Exceptions\CurlExceptions\CurlInitException;
use APP\plugins\generic\codecheck\classes\Exceptions\CurlExceptions\CurlReadException;
use CurlHandle;

/**
 * @file APP/plugins/generic/codecheck/tests/unittests/CodecheckMetadataHandlerUnitTests.php
 *
 * @class CodecheckMetadataHandlerUnitTest
 *
 * @brief Tests for the CodecheckMetadataHandler class
 */
class CodecheckMetadataHandlerUnitTest extends PKPTestCase
{
    private CodecheckMetadataHandler $codecheckMetadataHandler;
    private CurlApiClient $curlApiClient;
    /**
     * Set up the test environment
     */
    protected function setUp(): void
	{
		parent::setUp();

        /** mock GitHub client */
        $client = $this->createMock(\Github\Client::class);
        $request = new Request();
        $this->curlApiClient = new CurlApiClient();

        $this->codecheckMetadataHandler = new CodecheckMetadataHandler($request, $client, $this->curlApiClient);
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

        $this->codecheckMetadataHandler = new CodecheckMetadataHandler($request, $client, $this->curlApiClient);

        $owner = 'codecheckers';
        $repo = 'testing-dev-register';
        $repositoryUrl = 'https://github.com/' . $owner . '/' . $repo . '/';
        $response = $this->codecheckMetadataHandler->importMetadataFromGitHub($repositoryUrl);
        $actualMetadataReturnArray = json_decode($response->getPayload(), true);
        $this->assertEquals($response->getHttpResponseCode(), 200);
        $this->assertCount(3, $actualMetadataReturnArray);
        $this->assertTrue($actualMetadataReturnArray["success"]);
        $this->assertEquals($repositoryUrl, $actualMetadataReturnArray["repository"]);
        $this->assertEquals(["test" => "yaml"], $actualMetadataReturnArray["metadata"]);
    }

    public function testImportMetadataFromGithubDefaultBranchMain()
    {
        // mock contents API
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

        // mock Repo API
        $repoApi = $this->createMock(\Github\Api\Repo::class);

        // mock show() for default branch main
        $repoApi->method('show')
            ->will($this->throwException(new \Exception('No default branch found.')));

        $repoApi->expects($this->once())->method('show');

        // mock contents()
        $repoApi->method('contents')
            ->willReturn($contentsApi);

        // mock GitHub client
        $client = $this->createMock(\Github\Client::class);

        // client->api('repo') must return Github\Api\Repo because of return types
        $client->method('api')->willReturn($repoApi);


        $request = new Request();

        $this->codecheckMetadataHandler = new CodecheckMetadataHandler($request, $client, $this->curlApiClient);

        $owner = 'codecheckers';
        $repo = 'testing-dev-register';
        $repositoryUrl = 'https://github.com/' . $owner . '/' . $repo . '/';
        $this->codecheckMetadataHandler->importMetadataFromGitHub($repositoryUrl);
    }

    public function testImportMetadataFromGithubContentsShowException()
    {
        // mock contents API
        $contentsApi = $this->createMock(\Github\Api\Repository\Contents::class);
        // mock show() for the GitHub Repo contents
        $contentsApi->method('show')
            ->will($this->throwException(new \Exception('Failed to load the repository data.')));

        $contentsApi->expects($this->once())->method('show');

        // mock Repo API
        $repoApi = $this->createMock(\Github\Api\Repo::class);

        // mock show() for default branch
        $repoApi->method('show')
            ->willReturn(['default_branch' => 'root']);

        // mock contents()
        $repoApi->method('contents')
            ->willReturn($contentsApi);

        // mock GitHub client
        $client = $this->createMock(\Github\Client::class);

        // client->api('repo') must return Github\Api\Repo because of return types
        $client->method('api')->willReturn($repoApi);


        $request = new Request();

        $this->codecheckMetadataHandler = new CodecheckMetadataHandler($request, $client, $this->curlApiClient);

        $owner = 'codecheckers';
        $repo = 'testing-dev-register';
        $repositoryUrl = 'https://github.com/' . $owner . '/' . $repo . '/';
        $response = $this->codecheckMetadataHandler->importMetadataFromGitHub($repositoryUrl);
        $actualMetadataReturnArray = json_decode($response->getPayload(), true);
        $this->assertEquals($response->getHttpResponseCode(), 404);
        $this->assertCount(2, $actualMetadataReturnArray);
        $this->assertFalse($actualMetadataReturnArray["success"]);
        $this->assertEquals($repositoryUrl, $actualMetadataReturnArray["repository"]);
    }

    public function testImportMetadataFromGithubNoCodecheckYamlFound()
    {
        // mock contents API
        $contentsApi = $this->createMock(\Github\Api\Repository\Contents::class);
        $contentsApi->expects($this->once())->method('show');

        // mock Repo API
        $repoApi = $this->createMock(\Github\Api\Repo::class);

        // mock show() for default branch main
        $repoApi->method('show')
            ->will($this->throwException(new \Exception('No default branch found.')));

        $repoApi->expects($this->once())->method('show');

        // mock contents()
        $repoApi->method('contents')
            ->willReturn($contentsApi);

        // mock GitHub client
        $client = $this->createMock(\Github\Client::class);

        // client->api('repo') must return Github\Api\Repo because of return types
        $client->method('api')->willReturn($repoApi);


        $request = new Request();

        $this->codecheckMetadataHandler = new CodecheckMetadataHandler($request, $client, $this->curlApiClient);

        $owner = 'codecheckers';
        $repo = 'testing-dev-register';
        $repositoryUrl = 'https://github.com/' . $owner . '/' . $repo . '/';
        $response = $this->codecheckMetadataHandler->importMetadataFromGitHub($repositoryUrl);
        $actualMetadataReturnArray = json_decode($response->getPayload(), true);
        $this->assertEquals($response->getHttpResponseCode(), 404);
        $this->assertCount(3, $actualMetadataReturnArray);
        $this->assertFalse($actualMetadataReturnArray["success"]);
        $this->assertEquals($repositoryUrl, $actualMetadataReturnArray["repository"]);
        $this->assertEquals('codecheck.yml not found', $actualMetadataReturnArray["error"]);
    }

    public function testImportMetadataFromZenodo()
    {
        $repository = 'https://zenodo.org/records/14900193';
        $client = $this->createMock(\Github\Client::class);
        $request = new Request();
        $curlApiClient = $this->createMock(CurlApiClient::class);
        $curlApiClient->method('get')->willReturn("test: yaml");
        $this->codecheckMetadataHandler = new CodecheckMetadataHandler($request, $client, $curlApiClient);
        $response = $this->codecheckMetadataHandler->importMetadataFromZenodo($repository);
        $actualMetadataReturnArray = json_decode($response->getPayload(), true);
        $this->assertEquals($response->getHttpResponseCode(), 200);
        $this->assertCount(3, $actualMetadataReturnArray);
        $this->assertTrue($actualMetadataReturnArray["success"]);
        $this->assertEquals($repository, $actualMetadataReturnArray["repository"]);
        $this->assertEquals(["test" => "yaml"], $actualMetadataReturnArray["metadata"]);
    }

    public function testImportMetadataFromOsf()
    {
        $osfNodeId = 'ymc3t';
        $repository = "https://osf.io/$osfNodeId/";
        $client = $this->createMock(\Github\Client::class);
        $request = new Request();
        $curlApiClient = $this->createMock(CurlApiClient::class);
        $curlApiClient->method('get')
                        ->willReturnOnConsecutiveCalls(
                            json_encode([
                                "data" => [
                                    [
                                        "attributes" => [
                                            "name" => "README.md",
                                            "guid" => "4co4h"
                                        ],
                                        "attributes" => [
                                            "name" => "codecheck.yml",
                                            "guid" => "5zu8b"
                                        ]
                                    ]
                                ]
                            ]),
                            "test: yaml"
                        );
        $this->codecheckMetadataHandler = new CodecheckMetadataHandler($request, $client, $curlApiClient);
        $response = $this->codecheckMetadataHandler->importMetadataFromOsf($osfNodeId);
        $actualMetadataReturnArray = json_decode($response->getPayload(), true);
        $this->assertEquals($response->getHttpResponseCode(), 200);
        $this->assertCount(3, $actualMetadataReturnArray);
        $this->assertTrue($actualMetadataReturnArray["success"]);
        $this->assertEquals($repository, $actualMetadataReturnArray["repository"]);
        $this->assertEquals(["test" => "yaml"], $actualMetadataReturnArray["metadata"]);
    }

    public function testImportMetadataFromOsfNoDataFromOsfFilestorage()
    {
        $osfNodeId = 'ymc3t';
        $repository = "https://osf.io/$osfNodeId/";
        $client = $this->createMock(\Github\Client::class);
        $request = new Request();
        $curlApiClient = $this->createMock(CurlApiClient::class);
        $curlApiClient->method('get')
                        ->willReturnOnConsecutiveCalls(
                            json_encode([
                                "data" => NULL
                            ]),
                            "test: yaml"
                        );
        $this->codecheckMetadataHandler = new CodecheckMetadataHandler($request, $client, $curlApiClient);
        $response = $this->codecheckMetadataHandler->importMetadataFromOsf($osfNodeId);
        $actualMetadataReturnArray = json_decode($response->getPayload(), true);
        $this->assertEquals($response->getHttpResponseCode(), 500);
        $this->assertCount(3, $actualMetadataReturnArray);
        $this->assertFalse($actualMetadataReturnArray["success"]);
        $this->assertEquals($repository, $actualMetadataReturnArray["repository"]);
        $this->assertEquals('Invalid OSF API response', $actualMetadataReturnArray["error"]);
    }

    public function testImportMetadataFromOsfCodecheckYamlHasNoGuid()
    {
        $osfNodeId = 'ymc3t';
        $repository = "https://osf.io/$osfNodeId/";
        $client = $this->createMock(\Github\Client::class);
        $request = new Request();
        $curlApiClient = $this->createMock(CurlApiClient::class);
        $curlApiClient->method('get')
                        ->willReturnOnConsecutiveCalls(
                            json_encode([
                                "data" => [
                                    [
                                        "attributes" => [
                                            "name" => "codecheck.yml",
                                            "guid" => NULL
                                        ]
                                    ]
                                ]
                            ]),
                            "test: yaml"
                        );
        $this->codecheckMetadataHandler = new CodecheckMetadataHandler($request, $client, $curlApiClient);
        $response = $this->codecheckMetadataHandler->importMetadataFromOsf($osfNodeId);
        $actualMetadataReturnArray = json_decode($response->getPayload(), true);
        $this->assertEquals($response->getHttpResponseCode(), 404);
        $this->assertCount(3, $actualMetadataReturnArray);
        $this->assertFalse($actualMetadataReturnArray["success"]);
        $this->assertEquals($repository, $actualMetadataReturnArray["repository"]);
        $this->assertEquals('codecheck.yml not found', $actualMetadataReturnArray["error"]);
    }

    public function testImportMetadataFromOsfCurlInitException()
    {
        $errorCode = 500;
        $errorMessage = "Error initializing the cURL API";
        $osfNodeId = 'ymc3t';
        $repository = "https://osf.io/$osfNodeId/";
        $client = $this->createMock(\Github\Client::class);
        $request = new Request();
        $curlApiClient = $this->createMock(CurlApiClient::class);
        $curlApiClient->method('get')
                        ->will($this->throwException(new CurlInitException($errorMessage, $errorCode)));

        $this->codecheckMetadataHandler = new CodecheckMetadataHandler($request, $client, $curlApiClient);
        $response = $this->codecheckMetadataHandler->importMetadataFromOsf($osfNodeId);
        $actualMetadataReturnArray = json_decode($response->getPayload(), true);
        $this->assertEquals($response->getHttpResponseCode(), $errorCode);
        $this->assertCount(3, $actualMetadataReturnArray);
        $this->assertFalse($actualMetadataReturnArray["success"]);
        $this->assertEquals($repository, $actualMetadataReturnArray["repository"]);
        $this->assertEquals($errorMessage, $actualMetadataReturnArray["error"]);
    }

    public function testImportMetadataFromOsfCurlReadException()
    {
        $curlHandle = curl_init();
        $errorCode = curl_errno($curlHandle);
        $errorMessage = curl_error($curlHandle);
        $osfNodeId = 'ymc3t';
        $repository = "https://osf.io/$osfNodeId/";
        $client = $this->createMock(\Github\Client::class);
        $request = new Request();
        $curlApiClient = $this->createMock(CurlApiClient::class);
        $curlApiClient->method('get')
                        ->will($this->throwException(new CurlReadException($curlHandle)));

        $this->codecheckMetadataHandler = new CodecheckMetadataHandler($request, $client, $curlApiClient);
        $response = $this->codecheckMetadataHandler->importMetadataFromOsf($osfNodeId);
        $actualMetadataReturnArray = json_decode($response->getPayload(), true);
        $this->assertEquals($response->getHttpResponseCode(), $errorCode);
        $this->assertCount(3, $actualMetadataReturnArray);
        $this->assertFalse($actualMetadataReturnArray["success"]);
        $this->assertEquals($repository, $actualMetadataReturnArray["repository"]);
        $this->assertEquals($errorMessage, $actualMetadataReturnArray["error"]);
    }
}