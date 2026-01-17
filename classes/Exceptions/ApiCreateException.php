<?php

namespace APP\plugins\generic\codecheck\classes\Exceptions;

use APP\plugins\generic\codecheck\classes\CodecheckRegister\CertificateIdentifier;

class ApiCreateException extends \Exception
{
    private CertificateIdentifier $certificateIdentifier;

    public function __construct(
        string $message = "Error while creating data with the API",
        int $code = 0,
        CertificateIdentifier $certificateIdentifier = new CertificateIdentifier(0, 0),
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->certificateIdentifier = $certificateIdentifier;
    }

    public function getCertificateIdentifier() : CertificateIdentifier
    {
        return $this->certificateIdentifier;
    }
}