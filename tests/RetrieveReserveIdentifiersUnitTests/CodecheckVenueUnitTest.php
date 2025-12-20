<?php

namespace APP\plugins\generic\codecheck\tests;

use APP\plugins\generic\codecheck\classes\RetrieveReserveIdentifiers\CodecheckVenue;
use PKP\tests\PKPTestCase;

/**
 * @file APP/plugins/generic/codecheck/tests/RetrieveReserveIdentifiersUnitTests/CodecheckVenueUnitTest.php
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
        $venueName = 'check-nl';
        $venue = new CodecheckVenue();
        $venue->setVenueName($venueName);
        $this->assertSame($venue->getVenueName(), $venueName);
    }

    public function testSetAndGetVenueType()
    {
        $venueType = 'community';
        $venue = new CodecheckVenue();
        $venue->setVenueType($venueType);
        $this->assertSame($venue->getVenueType(), $venueType);
    }
}