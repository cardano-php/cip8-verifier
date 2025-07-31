<?php

use CardanoPhp\CIP8Verifier\Service\StakeAddressGenerator;
use CardanoPhp\CIP8Verifier\Service\Bech32Encoder;

describe('StakeAddressGenerator', function () {
    test('can be instantiated with Bech32Encoder', function () {
        $bech32Encoder = new Bech32Encoder();
        $generator = new StakeAddressGenerator($bech32Encoder);
        
        expect($generator)->toBeInstanceOf(StakeAddressGenerator::class);
    });

    test('generateStakeAddress creates correct testnet address from demo data', function () {
        // Use the public key extracted from demo signature key
        $signatureKey = "a4010103272006215820eb59d52fbd257d3f8f8f51dd59b2013092763fc9cbc109d32d837920be5e62be";
        $extractor = new \CardanoPhp\CIP8Verifier\Service\PublicKeyExtractor();
        $publicKey = $extractor->extractFromSignatureKey($signatureKey);
        
        $bech32Encoder = new Bech32Encoder();
        $generator = new StakeAddressGenerator($bech32Encoder);
        
        $stakeAddress = $generator->generateStakeAddress($publicKey, 0); // testnet mode
        
        // Should match the expected stake address from demo.php
        expect($stakeAddress)->toBe("stake_test1upafv37jqjy8pgrjdauxyxrruqme0hqhh9ryww34mm297agc0f3vc");
    });

    test('generateStakeAddress creates different address for mainnet vs testnet', function () {
        $signatureKey = "a4010103272006215820eb59d52fbd257d3f8f8f51dd59b2013092763fc9cbc109d32d837920be5e62be";
        $extractor = new \CardanoPhp\CIP8Verifier\Service\PublicKeyExtractor();
        $publicKey = $extractor->extractFromSignatureKey($signatureKey);
        
        $bech32Encoder = new Bech32Encoder();
        $generator = new StakeAddressGenerator($bech32Encoder);
        
        $testnetAddress = $generator->generateStakeAddress($publicKey, 0);
        $mainnetAddress = $generator->generateStakeAddress($publicKey, 1);
        
        expect($testnetAddress)->not->toBe($mainnetAddress);
        expect($testnetAddress)->toStartWith('stake_test');
        expect($mainnetAddress)->toStartWith('stake');
    });

    test('generateStakeAddress creates consistent addresses for same input', function () {
        $signatureKey = "a4010103272006215820eb59d52fbd257d3f8f8f51dd59b2013092763fc9cbc109d32d837920be5e62be";
        $extractor = new \CardanoPhp\CIP8Verifier\Service\PublicKeyExtractor();
        $publicKey = $extractor->extractFromSignatureKey($signatureKey);
        
        $bech32Encoder = new Bech32Encoder();
        $generator = new StakeAddressGenerator($bech32Encoder);
        
        $address1 = $generator->generateStakeAddress($publicKey, 0);
        $address2 = $generator->generateStakeAddress($publicKey, 0);
        
        expect($address1)->toBe($address2);
    });

    test('generateStakeAddress creates different addresses for different public keys', function () {
        // Use different signature keys
        $signatureKey1 = "a4010103272006215820eb59d52fbd257d3f8f8f51dd59b2013092763fc9cbc109d32d837920be5e62be";
        $signatureKey2 = "a4010103272006215820eb59d52fbd257d3f8f8f51dd59b2013092763fc9cbc109d32d837920be5e62bf"; // Different last byte
        
        $extractor = new \CardanoPhp\CIP8Verifier\Service\PublicKeyExtractor();
        $publicKey1 = $extractor->extractFromSignatureKey($signatureKey1);
        $publicKey2 = $extractor->extractFromSignatureKey($signatureKey2);
        
        $bech32Encoder = new Bech32Encoder();
        $generator = new StakeAddressGenerator($bech32Encoder);
        
        $address1 = $generator->generateStakeAddress($publicKey1, 0);
        $address2 = $generator->generateStakeAddress($publicKey2, 0);
        
        expect($address1)->not->toBe($address2);
        expect($address1)->toStartWith('stake_test');
        expect($address2)->toStartWith('stake_test');
    });

    test('generateStakeAddress produces valid bech32 formatted addresses', function () {
        $signatureKey = "a4010103272006215820eb59d52fbd257d3f8f8f51dd59b2013092763fc9cbc109d32d837920be5e62be";
        $extractor = new \CardanoPhp\CIP8Verifier\Service\PublicKeyExtractor();
        $publicKey = $extractor->extractFromSignatureKey($signatureKey);
        
        $bech32Encoder = new Bech32Encoder();
        $generator = new StakeAddressGenerator($bech32Encoder);
        
        $testnetAddress = $generator->generateStakeAddress($publicKey, 0);
        $mainnetAddress = $generator->generateStakeAddress($publicKey, 1);
        
        // Valid bech32 addresses should contain only valid characters
        expect($testnetAddress)->toMatch('/^stake_test1[02-9ac-hj-np-z]+$/');
        expect($mainnetAddress)->toMatch('/^stake1[02-9ac-hj-np-z]+$/');
    });

    test('generateStakeAddress handles different network modes correctly', function () {
        $signatureKey = "a4010103272006215820eb59d52fbd257d3f8f8f51dd59b2013092763fc9cbc109d32d837920be5e62be";
        $extractor = new \CardanoPhp\CIP8Verifier\Service\PublicKeyExtractor();
        $publicKey = $extractor->extractFromSignatureKey($signatureKey);
        
        $bech32Encoder = new Bech32Encoder();
        $generator = new StakeAddressGenerator($bech32Encoder);
        
        // Test various network modes
        $testnetAddress = $generator->generateStakeAddress($publicKey, 0);
        $mainnetAddress = $generator->generateStakeAddress($publicKey, 1);
        
        expect($testnetAddress)->toStartWith('stake_test');
        expect($mainnetAddress)->toStartWith('stake');
        expect($testnetAddress)->not->toBe($mainnetAddress);
    });
});