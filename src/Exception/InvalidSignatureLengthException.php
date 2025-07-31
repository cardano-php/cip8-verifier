<?php

namespace CardanoPhp\CIP8Verifier\Exception;

class InvalidSignatureLengthException extends CIP8VerificationException
{
    public function __construct(int $actualLength, int $expectedLength)
    {
        parent::__construct("Invalid signature length: {$actualLength} bytes, expected {$expectedLength}");
    }
}
