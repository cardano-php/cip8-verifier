<?php

namespace CardanoPhp\CIP8Verifier\DTO;

readonly class CoseSign1
{
    public function __construct(
        public string $protected,
        public mixed $unprotected,
        public string $payload,
        public string $signature
    ) {}
}
