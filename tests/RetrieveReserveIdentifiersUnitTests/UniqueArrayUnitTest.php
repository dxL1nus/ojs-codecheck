<?php

namespace APP\plugins\generic\codecheck\tests;

use APP\plugins\generic\codecheck\classes\RetrieveReserveIdentifiers\UniqueArray;
use PKP\tests\PKPTestCase;

/**
 * @file APP/plugins/generic/codecheck/tests/unittests/UniqueArrayUnitTests.php
 *
 * @class UniqueArrayUnitTest
 *
 * @brief Tests for the UniqueArray class
 */
class UniqueArrayUnitTest extends PKPTestCase
{
    /**
     * Set up the test environment
     */
    protected function setUp(): void
	{
		parent::setUp();
	}

    public function testUniqueArrayRemove()
    {
        $uniqueArray = new UniqueArray();
        $uniqueArray->add(1);
        $uniqueArray->add(2);
        $this->assertCount(2, $uniqueArray->toArray());
        $uniqueArray->remove(1);
        $this->assertCount(1, $uniqueArray->toArray());
        $this->assertEquals($uniqueArray->toArray(), [1]);
    }
}