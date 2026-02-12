<?php

namespace APP\plugins\generic\codecheck\tests;

use APP\plugins\generic\codecheck\classes\Submission\CodecheckSubmission;
use PKP\tests\PKPTestCase;

class CodecheckSubmissionUnitTest extends PKPTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testGetSubmissionId()
    {
        $submission = new CodecheckSubmission(['submission_id' => 123]);
        $this->assertSame(123, $submission->getSubmissionId());
    }

    public function testGetVersion()
    {
        $submission = new CodecheckSubmission(['submission_id' => 1, 'version' => '1.0']);
        $this->assertSame('1.0', $submission->getVersion());
    }

    public function testGetVersionReturnsDefaultWhenNotSet()
    {
        $submission = new CodecheckSubmission(['submission_id' => 1]);
        $this->assertSame('latest', $submission->getVersion());
    }

    public function testGetPublicationType()
    {
        $submission = new CodecheckSubmission(['submission_id' => 1, 'publication_type' => 'url']);
        $this->assertSame('url', $submission->getPublicationType());
    }

    public function testGetPublicationTypeReturnsDefaultWhenNotSet()
    {
        $submission = new CodecheckSubmission(['submission_id' => 1]);
        $this->assertSame('doi', $submission->getPublicationType());
    }

    public function testGetManifestReturnsArray()
    {
        $manifest = [['file' => 'output.png', 'comment' => 'Main result']];
        $submission = new CodecheckSubmission([
            'submission_id' => 1,
            'manifest' => json_encode($manifest)
        ]);
        $this->assertSame($manifest, $submission->getManifest());
    }

    public function testGetManifestReturnsEmptyArrayWhenNotSet()
    {
        $submission = new CodecheckSubmission(['submission_id' => 1]);
        $this->assertSame([], $submission->getManifest());
    }

    public function testGetRepository()
    {
        $submission = new CodecheckSubmission([
            'submission_id' => 1,
            'repository' => 'https://github.com/test/repo'
        ]);
        $this->assertSame('https://github.com/test/repo', $submission->getRepository());
    }

    public function testGetRepositoryReturnsEmptyStringWhenNotSet()
    {
        $submission = new CodecheckSubmission(['submission_id' => 1]);
        $this->assertSame('', $submission->getRepository());
    }

    public function testGetSource()
    {
        $submission = new CodecheckSubmission([
            'submission_id' => 1,
            'source' => 'https://github.com/codecheckers/register'
        ]);
        $this->assertSame('https://github.com/codecheckers/register', $submission->getSource());
    }

    public function testGetCodecheckers()
    {
        $codecheckers = [['name' => 'John Doe', 'orcid' => '0000-0001-2345-6789']];
        $submission = new CodecheckSubmission([
            'submission_id' => 1,
            'codecheckers' => json_encode($codecheckers)
        ]);
        $this->assertSame($codecheckers, $submission->getCodecheckers());
    }

    public function testGetCodecheckersReturnsEmptyArrayWhenNotSet()
    {
        $submission = new CodecheckSubmission(['submission_id' => 1]);
        $this->assertSame([], $submission->getCodecheckers());
    }

    public function testGetCertificate()
    {
        $submission = new CodecheckSubmission([
            'submission_id' => 1,
            'certificate' => 'CODECHECK-2025-001'
        ]);
        $this->assertSame('CODECHECK-2025-001', $submission->getCertificate());
    }

    public function testGetCheckTime()
    {
        $submission = new CodecheckSubmission([
            'submission_id' => 1,
            'check_time' => '2025-01-15'
        ]);
        $this->assertSame('2025-01-15', $submission->getCheckTime());
    }

    public function testGetCheckTimeReturnsNullWhenNotSet()
    {
        $submission = new CodecheckSubmission(['submission_id' => 1]);
        $this->assertNull($submission->getCheckTime());
    }

    public function testGetSummary()
    {
        $submission = new CodecheckSubmission([
            'submission_id' => 1,
            'summary' => 'All outputs reproduced successfully'
        ]);
        $this->assertSame('All outputs reproduced successfully', $submission->getSummary());
    }

    public function testGetReport()
    {
        $submission = new CodecheckSubmission([
            'submission_id' => 1,
            'report' => 'https://zenodo.org/record/12345'
        ]);
        $this->assertSame('https://zenodo.org/record/12345', $submission->getReport());
    }

    public function testGetAdditionalContent()
    {
        $submission = new CodecheckSubmission([
            'submission_id' => 1,
            'additional_content' => 'custom_field: value'
        ]);
        $this->assertSame('custom_field: value', $submission->getAdditionalContent());
    }

    public function testHasCompletedCheckReturnsTrueWithCertificate()
    {
        $submission = new CodecheckSubmission([
            'submission_id' => 1,
            'certificate' => 'CODECHECK-2025-001'
        ]);
        $this->assertTrue($submission->hasCompletedCheck());
    }

    public function testHasCompletedCheckReturnsFalseWithoutCertificate()
    {
        $submission = new CodecheckSubmission(['submission_id' => 1]);
        $this->assertFalse($submission->hasCompletedCheck());
    }

    public function testHasAssignedCheckerReturnsTrueWithCodecheckers()
    {
        $submission = new CodecheckSubmission([
            'submission_id' => 1,
            'codecheckers' => json_encode([['name' => 'John Doe']])
        ]);
        $this->assertTrue($submission->hasAssignedChecker());
    }

    public function testHasAssignedCheckerReturnsFalseWithoutCodecheckers()
    {
        $submission = new CodecheckSubmission(['submission_id' => 1]);
        $this->assertFalse($submission->hasAssignedChecker());
    }

    public function testGetCertificateLinkWithValidUrl()
    {
        $submission = new CodecheckSubmission([
            'submission_id' => 1,
            'certificate' => 'https://codecheck.org.uk/certificate/CODECHECK-2025-001'
        ]);
        $this->assertSame(
            'https://codecheck.org.uk/certificate/CODECHECK-2025-001',
            $submission->getCertificateLink()
        );
    }

    public function testGetCertificateLinkReturnsEmptyStringWhenNotSet()
    {
        $submission = new CodecheckSubmission(['submission_id' => 1]);
        $this->assertSame('', $submission->getCertificateLink());
    }

    public function testGetDoiLinkWithValidDoi()
    {
        $submission = new CodecheckSubmission([
            'submission_id' => 1,
            'report' => '10.5281/zenodo.123456'
        ]);
        $this->assertSame('https://doi.org/10.5281/zenodo.123456', $submission->getDoiLink());
    }

    public function testGetDoiLinkReturnsEmptyStringWhenNotSet()
    {
        $submission = new CodecheckSubmission(['submission_id' => 1]);
        $this->assertSame('', $submission->getDoiLink());
    }

    public function testGetCodecheckerNamesReturnsCommaSeparated()
    {
        $submission = new CodecheckSubmission([
            'submission_id' => 1,
            'codecheckers' => json_encode([
                ['name' => 'John Doe'],
                ['name' => 'Jane Smith']
            ])
        ]);
        $this->assertSame('John Doe, Jane Smith', $submission->getCodecheckerNames());
    }

    public function testGetCodecheckerNamesReturnsEmptyStringWhenNotSet()
    {
        $submission = new CodecheckSubmission(['submission_id' => 1]);
        $this->assertSame('', $submission->getCodecheckerNames());
    }

    public function testGetCertificateDateReturnsCheckTime()
    {
        $submission = new CodecheckSubmission([
            'submission_id' => 1,
            'check_time' => '2025-01-15'
        ]);
        $this->assertSame('2025-01-15', $submission->getCertificateDate());
    }

    public function testGetCertificateDateReturnsNullWhenNotSet()
    {
        $submission = new CodecheckSubmission(['submission_id' => 1]);
        $this->assertNull($submission->getCertificateDate());
    }

    public function testGetCodeRepositoryLegacyMethod()
    {
        $submission = new CodecheckSubmission([
            'submission_id' => 1,
            'repository' => 'https://github.com/test/repo'
        ]);
        $this->assertSame('https://github.com/test/repo', $submission->getCodeRepository());
    }

    public function testGetDataRepositoryLegacyMethodReturnsEmpty()
    {
        $submission = new CodecheckSubmission(['submission_id' => 1]);
        $this->assertSame('', $submission->getDataRepository());
    }
}