<?php

namespace CardanoPhp\CIP8Verifier\DTO;

readonly class VerificationRequest
{
    public function __construct(
        public string $signatureCbor,
        public string $signatureKey,
        public string $walletAuthChallengeHex,
        public string $stakeKeyAddress,
        public int $networkMode
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['signatureCbor'],
            $data['signatureKey'],
            $data['walletAuthChallengeHex'],
            $data['stakeKeyAddress'],
            $data['networkMode']
        );
    }
}
