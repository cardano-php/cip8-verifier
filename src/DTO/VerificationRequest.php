<?php

namespace CardanoPhp\CIP8Verifier\DTO;

readonly class VerificationRequest
{
    public function __construct(
        public string $signatureCbor,
        public string $signatureKey,
        public string $challengeHex,
        public string $expectedSignerStakeAddress,
        public int $networkMode
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['signatureCbor'],
            $data['signatureKey'],
            $data['challengeHex'],
            $data['expectedSignerStakeAddress'],
            $data['networkMode']
        );
    }
}
