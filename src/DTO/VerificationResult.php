<?php

namespace CardanoPhp\CIP8Verifier\DTO;

readonly class VerificationResult
{
    public function __construct(
        public bool $isValid,
        public bool $walletMatches,
        public bool $payloadMatches,
        public bool $signatureValidates,
        public string|null $error = null
    ) {}

    public function toArray(): array
    {
        return [
            'isValid' => $this->isValid,
            'walletMatches' => $this->walletMatches,
            'payloadMatches' => $this->payloadMatches,
            'signatureValidates' => $this->signatureValidates,
            'error' => $this->error
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

    public static function createInvalid(string $error): self
    {
        return new self(false, false, false, false, $error);
    }
}
