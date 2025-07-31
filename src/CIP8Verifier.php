<?php

namespace CardanoPhp\CIP8Verifier;

use CardanoPhp\CIP8Verifier\DTO\VerificationRequest;
use CardanoPhp\CIP8Verifier\DTO\VerificationResult;
use CardanoPhp\CIP8Verifier\Service\Bech32Encoder;
use CardanoPhp\CIP8Verifier\Service\CoseParser;
use CardanoPhp\CIP8Verifier\Service\PublicKeyExtractor;
use CardanoPhp\CIP8Verifier\Service\SignatureVerifier;
use CardanoPhp\CIP8Verifier\Service\StakeAddressGenerator;

readonly class CIP8Verifier
{
    public function __construct(
        private PublicKeyExtractor $publicKeyExtractor,
        private StakeAddressGenerator $stakeAddressGenerator,
        private CoseParser $coseParser,
        private SignatureVerifier $signatureVerifier
    ) {}

    public static function create(): self
    {
        $bech32Encoder = new Bech32Encoder();
        $stakeAddressGenerator = new StakeAddressGenerator($bech32Encoder);
        $publicKeyExtractor = new PublicKeyExtractor();
        $coseParser = new CoseParser();
        $signatureVerifier = new SignatureVerifier();

        return new self(
            $publicKeyExtractor,
            $stakeAddressGenerator,
            $coseParser,
            $signatureVerifier
        );
    }

    public function verify(VerificationRequest $request): VerificationResult
    {
        $publicKey = $this->publicKeyExtractor->extractFromSignatureKey($request->signatureKey);

        $generatedStakeAddress = $this->stakeAddressGenerator->generateStakeAddress(
            $publicKey,
            $request->networkMode
        );

        $stakeAddressMatches = $generatedStakeAddress === $request->expectedSignerStakeAddress;

        $coseData = $this->coseParser->parseCoseSign1(hex2bin($request->signatureCbor));

        $signatureValidates = $this->signatureVerifier->verifySignature($coseData, $publicKey);

        $challengeMatches = $this->signatureVerifier->verifyPayload($coseData, $request->challengeHex);

        return VerificationResult::createValid($stakeAddressMatches, $challengeMatches, $signatureValidates);
    }
}
