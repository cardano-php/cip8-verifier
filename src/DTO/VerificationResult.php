<?php

namespace CardanoPhp\CIP8Verifier\DTO;

readonly class VerificationResult
{
    public function __construct(
        public bool $isValid,
        public bool $stakeAddressMatches,
        public bool $challengeMatches,
        public bool $signatureValidates
    ) {}

    public function toArray(): array
    {
        return [
            'isValid' => $this->isValid,
            'stakeAddressMatches' => $this->stakeAddressMatches,
            'challengeMatches' => $this->challengeMatches,
            'signatureValidates' => $this->signatureValidates
        ];
    }

    public static function createValid(bool $stakeAddressMatches, bool $challengeMatches, bool $signatureValidates): self
    {
        return new self(
            $stakeAddressMatches && $challengeMatches && $signatureValidates,
            $stakeAddressMatches,
            $challengeMatches,
            $signatureValidates
        );
    }


}
