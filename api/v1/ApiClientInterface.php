<?php

namespace APP\plugins\generic\codecheck\api\v1;

interface ApiClientInterface
{
    public function get(string $url): string|bool;
}