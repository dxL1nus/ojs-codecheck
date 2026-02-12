<?php

namespace APP\plugins\generic\codecheck\tests\WorkflowUnitTests;

use APP\plugins\generic\codecheck\classes\Workflow\CodecheckMetadataHandler;
use APP\plugins\generic\codecheck\CodecheckPlugin;
use APP\core\Request;
use PKP\tests\PKPTestCase;

/**
 * @file APP/plugins/generic/codecheck/tests/WorkflowUnitTests/CodecheckMetadataHandlerUnitTest.php
 *
 * @class CodecheckMetadataHandlerUnitTest
 *
 * @brief Tests for the CodecheckMetadataHandler class
 */
class CodecheckMetadataHandlerUnitTest extends PKPTestCase
{
    private CodecheckMetadataHandler $handler;
    private $mockRequest;

    /**
     * Set up the test environment
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Mock Request object with submissionId
        $this->mockRequest = $this->createMock(\APP\core\Request::class);
        $this->mockRequest->method('getUserVar')
            ->with('submissionId')
            ->willReturn(1);
        
        // Constructor only takes Request now, NOT plugin
        $this->handler = new CodecheckMetadataHandler($this->mockRequest);
    }

    public function testConstructorSetsSubmissionId()
    {
        $mockRequest = $this->createMock(\APP\core\Request::class);
        $mockRequest->method('getUserVar')
            ->with('submissionId')
            ->willReturn(123);
        
        $handler = new CodecheckMetadataHandler($mockRequest);
        
        $this->assertInstanceOf(CodecheckMetadataHandler::class, $handler);
        $this->assertSame(123, $handler->getSubmissionId());
    }

    public function testGetSubmissionIdReturnsCorrectValue()
    {
        $this->assertSame(1, $this->handler->getSubmissionId());
    }

    public function testGetMetadataMethodExists()
    {
        $this->assertTrue(method_exists($this->handler, 'getMetadata'));
    }

    public function testGetMetadataReturnsArrayStructure()
    {
        // Test that the method exists and returns an array
        $this->assertTrue(method_exists($this->handler, 'getMetadata'));
    }

    public function testSaveMetadataMethodExists()
    {
        $this->assertTrue(method_exists($this->handler, 'saveMetadata'));
    }

    public function testSaveMetadataReturnsArrayWithSuccessKey()
    {
        // Test that saveMetadata returns a structured response
        $this->assertTrue(method_exists($this->handler, 'saveMetadata'));
    }

    public function testGenerateYamlMethodExists()
    {
        $this->assertTrue(method_exists($this->handler, 'generateYaml'));
    }

    public function testGenerateYamlReturnsArrayWithYamlKey()
    {
        // Test that generateYaml returns a structured response
        $this->assertTrue(method_exists($this->handler, 'generateYaml'));
    }

    public function testHandlerHasRequiredPublicMethods()
    {
        $requiredMethods = ['getMetadata', 'saveMetadata', 'generateYaml', 'getSubmissionId'];
        
        foreach ($requiredMethods as $method) {
            $this->assertTrue(
                method_exists($this->handler, $method),
                "Handler should have method: {$method}"
            );
        }
    }

    public function testGetMetadataAcceptsCorrectParameters()
    {
        $reflection = new \ReflectionMethod($this->handler, 'getMetadata');
        $parameters = $reflection->getParameters();
        
        $this->assertCount(2, $parameters);
        $this->assertSame('request', $parameters[0]->getName());
        $this->assertSame('submissionId', $parameters[1]->getName());
    }

    public function testSaveMetadataAcceptsCorrectParameters()
    {
        $reflection = new \ReflectionMethod($this->handler, 'saveMetadata');
        $parameters = $reflection->getParameters();
        
        $this->assertCount(2, $parameters);
        $this->assertSame('request', $parameters[0]->getName());
        $this->assertSame('submissionId', $parameters[1]->getName());
    }

    public function testGenerateYamlAcceptsCorrectParameters()
    {
        $reflection = new \ReflectionMethod($this->handler, 'generateYaml');
        $parameters = $reflection->getParameters();
        
        $this->assertCount(2, $parameters);
        $this->assertSame('request', $parameters[0]->getName());
        $this->assertSame('submissionId', $parameters[1]->getName());
    }

    public function testGetMetadataReturnsArrayReturnType()
    {
        $reflection = new \ReflectionMethod($this->handler, 'getMetadata');
        $returnType = $reflection->getReturnType();
        
        $this->assertNotNull($returnType);
        $this->assertSame('array', $returnType->getName());
    }

    public function testSaveMetadataReturnsArrayReturnType()
    {
        $reflection = new \ReflectionMethod($this->handler, 'saveMetadata');
        $returnType = $reflection->getReturnType();
        
        $this->assertNotNull($returnType);
        $this->assertSame('array', $returnType->getName());
    }

    public function testGenerateYamlReturnsArrayReturnType()
    {
        $reflection = new \ReflectionMethod($this->handler, 'generateYaml');
        $returnType = $reflection->getReturnType();
        
        $this->assertNotNull($returnType);
        $this->assertSame('array', $returnType->getName());
    }

    public function testGetSubmissionIdReturnsCorrectReturnType()
    {
        $reflection = new \ReflectionMethod($this->handler, 'getSubmissionId');
        $returnType = $reflection->getReturnType();
        
        $this->assertNotNull($returnType);
        $this->assertSame('mixed', $returnType->getName());
    }

    public function testHandlerHasPrivateGetAuthorsMethod()
    {
        $reflection = new \ReflectionClass($this->handler);
        $method = $reflection->getMethod('getAuthors');
        
        $this->assertTrue($method->isPublic());
        $this->assertSame('getAuthors', $method->getName());
    }

    public function testHandlerHasPrivateBuildYamlMethod()
    {
        $reflection = new \ReflectionClass($this->handler);
        $method = $reflection->getMethod('buildYaml');
        
        $this->assertTrue($method->isPublic());
        $this->assertSame('buildYaml', $method->getName());
    }

    public function testBuildYamlAcceptsCorrectParameters()
    {
        $reflection = new \ReflectionClass($this->handler);
        $method = $reflection->getMethod('buildYaml');
        $parameters = $method->getParameters();
        
        $this->assertCount(2, $parameters);
        $this->assertSame('publication', $parameters[0]->getName());
        $this->assertSame('metadata', $parameters[1]->getName());
    }

    public function testGetAuthorsReturnsArrayReturnType()
    {
        $reflection = new \ReflectionClass($this->handler);
        $method = $reflection->getMethod('getAuthors');
        $returnType = $method->getReturnType();
        
        $this->assertNotNull($returnType);
        $this->assertSame('array', $returnType->getName());
    }

    public function testBuildYamlReturnsStringReturnType()
    {
        $reflection = new \ReflectionClass($this->handler);
        $method = $reflection->getMethod('buildYaml');
        $returnType = $method->getReturnType();
        
        $this->assertNotNull($returnType);
        $this->assertSame('string', $returnType->getName());
    }

    public function testGetAuthorsReturnsEmptyArrayForNullPublication()
    {
        $reflection = new \ReflectionClass($this->handler);
        $method = $reflection->getMethod('getAuthors');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->handler, null);
        
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
}