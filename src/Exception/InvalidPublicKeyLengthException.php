<?php

namespace CardanoPhp\CIP8Verifier\Exception;

class InvalidPublicKeyLengthException extends CIP8VerificationException
{
    public function __construct(int $actualLength, int $expectedLength)
    {
        parent::__construct("Invalid public key length: {$actualLength} bytes, expected {$expectedLength}");
    }
}
