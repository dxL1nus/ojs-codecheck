<?php

namespace APP\plugins\generic\codecheck\tests;

use APP\plugins\generic\codecheck\classes\CodecheckRegister\CodecheckVenue;
use PKP\tests\PKPTestCase;

/**
 * @file APP/plugins/generic/codecheck/tests/unittests/CodecheckVenueUnitTest.php
 *
 * @class CodecheckVenueUnitTest
 *
 * @brief Tests for the CodecheckVenue class
 */
class CodecheckVenueUnitTest extends PKPTestCase
{
    /**
     * Set up the test environment
     */
    protected function setUp(): void
	{
		parent::setUp();
	}

    public function testSetAndGetVenueName()
    {
        $oldVenueName = 'testVenueName';
        $venueName = 'check-nl';
        $venue = new CodecheckVenue('testVenueType', $oldVenueName);
        $venue->setVenueName($venueName);
        $this->assertSame($venue->getVenueName(), $venueName);
        $this->assertNotEquals($venue->getVenueName(), $oldVenueName);
    }

    public function testSetAndGetVenueType()
    {
        $oldVenueType = 'testVenueType';
        $venueType = 'community';
        $venue = new CodecheckVenue($oldVenueType, 'testVenueName');
        $venue->setVenueType($venueType);
        $this->assertSame($venue->getVenueType(), $venueType);
        $this->assertNotEquals($venue->getVenueType(), $oldVenueType);
    }
}