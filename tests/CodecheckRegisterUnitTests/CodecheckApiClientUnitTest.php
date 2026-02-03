<?php

namespace APP\plugins\generic\codecheck\tests;

use APP\plugins\generic\codecheck\classes\CodecheckRegister\CodecheckApiClient;
use APP\plugins\generic\codecheck\classes\Exceptions\CurlExceptions\CurlInitException;
use APP\plugins\generic\codecheck\classes\Exceptions\CurlExceptions\CurlReadException;
use PKP\tests\PKPTestCase;

/**
 * @file APP/plugins/generic/codecheck/tests/ApiUnitTests/CodecheckApiClientUnitTest.php
 *
 * @class CodecheckApiClientUnitTest
 *
 * @brief Tests for the CodecheckApiClient class
 */
class CodecheckApiClientUnitTest extends PKPTestCase
{
    /**
     * Set up the test environment
     */
    protected function setUp(): void
	{
		parent::setUp();
	}

    public function testCodecheckApiClientFetchRawResponse()
    {
        $expectedResponse = json_encode([
            "some" => "data",
            'count' => 2,
        ]);

        $exampleUrl = 'https://example.com/api';

        // Create a partial mock
        $client = $this->getMockBuilder(CodecheckApiClient::class)
            ->onlyMethods(['fetch'])
            ->disableOriginalConstructor()
            ->getMock();

        // Mock the parent fetch() call
        $client->expects($this->once())
            ->method('fetch')
            ->with($exampleUrl)
            ->willReturn($expectedResponse);

        // Call fetch()
        $actualResponse = $client->fetch($exampleUrl);

        // Assert raw response
        $this->assertSame($expectedResponse, $actualResponse);
    }

    public function testCodecheckApiClientFetchDecodedResponse()
    {
        $expectedResponse = json_encode([
            "some" => "data",
            'count' => 2,
        ]);

        // Create a partial mock
        $client = $this->createMock(CodecheckApiClient::class);

        // Mock the parent fetch() call
        $client->expects($this->once())
            ->method('getData')
            ->willReturn(json_decode($expectedResponse, 1));

        // Assert decoded data
        $this->assertSame(
            json_decode($expectedResponse, 1),
            $client->getData()
        );
    }

    public function testFetchCurlInitFails()
    {
        $client = $this->createMock(CodecheckApiClient::class);
        $client->method('fetch')
            ->will($this->throwException(new CurlInitException('')));;

        $this->expectException(CurlInitException::class);

        $client->fetch('https://example.com/api');
    }

    public function testFetchCurlReadFails()
    {
        $testCurlHandle = curl_init();

        $client = $this->createMock(CodecheckApiClient::class);
        $client->method('fetch')
            ->will($this->throwException(new CurlReadException($testCurlHandle)));;

        $this->expectException(CurlReadException::class);
        $this->expectExceptionMessage(curl_error($testCurlHandle));
        $this->expectExceptionCode(curl_errno($testCurlHandle));

        $client->fetch('https://example.com/api');
    }
}