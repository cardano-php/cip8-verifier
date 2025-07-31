<?php

namespace CardanoPhp\CIP8Verifier\DTO;

readonly class VerificationResult
{
    public function __construct(
        public bool $isValid,
        public bool $walletMatches,
        public bool $payloadMatches,
        public bool $signatureValidates
    ) {}

    public function toArray(): array
    {
        return [
            'isValid' => $this->isValid,
            'walletMatches' => $this->walletMatches,
            'payloadMatches' => $this->payloadMatches,
            'signatureValidates' => $this->signatureValidates
        ];
    }

    public static function createValid(bool $walletMatches, bool $payloadMatches, bool $signatureValidates): self
    {
        return new self(
            $walletMatches && $payloadMatches && $signatureValidates,
            $walletMatches,
            $payloadMatches,
            $signatureValidates
        );
    }


}
