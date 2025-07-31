<?php

use CardanoPhp\CIP8Verifier\Service\Bech32Encoder;

describe('Bech32Encoder', function () {
    test('can be instantiated', function () {
        $encoder = new Bech32Encoder();
        expect($encoder)->toBeInstanceOf(Bech32Encoder::class);
    });

    test('stakeToBech32 creates valid bech32 address with testnet prefix', function () {
        $encoder = new Bech32Encoder();
        
        // Create a test stake key (29 bytes: 1 byte header + 28 bytes hash)
        $stakeKey = chr(0xe0) . str_repeat('a', 28);
        
        $result = $encoder->stakeToBech32($stakeKey, 'stake_test');
        
        expect($result)->toBeString();
        expect($result)->toStartWith('stake_test1');
        expect(strlen($result))->toBeGreaterThan(strlen('stake_test1'));
    });

    test('stakeToBech32 creates valid bech32 address with mainnet prefix', function () {
        $encoder = new Bech32Encoder();
        
        $stakeKey = chr(0xe1) . str_repeat('b', 28);
        
        $result = $encoder->stakeToBech32($stakeKey, 'stake');
        
        expect($result)->toBeString();
        expect($result)->toStartWith('stake1');
        expect(strlen($result))->toBeGreaterThan(strlen('stake1'));
    });

    test('stakeToBech32 produces consistent results for same input', function () {
        $encoder = new Bech32Encoder();
        
        $stakeKey = chr(0xe0) . str_repeat('c', 28);
        
        $result1 = $encoder->stakeToBech32($stakeKey, 'stake_test');
        $result2 = $encoder->stakeToBech32($stakeKey, 'stake_test');
        
        expect($result1)->toBe($result2);
    });

    test('stakeToBech32 produces different results for different stake keys', function () {
        $encoder = new Bech32Encoder();
        
        $stakeKey1 = chr(0xe0) . str_repeat('a', 28);
        $stakeKey2 = chr(0xe0) . str_repeat('b', 28);
        
        $result1 = $encoder->stakeToBech32($stakeKey1, 'stake_test');
        $result2 = $encoder->stakeToBech32($stakeKey2, 'stake_test');
        
        expect($result1)->not->toBe($result2);
        expect($result1)->toStartWith('stake_test1');
        expect($result2)->toStartWith('stake_test1');
    });

    test('stakeToBech32 produces different results for different prefixes', function () {
        $encoder = new Bech32Encoder();
        
        $stakeKey = chr(0xe0) . str_repeat('a', 28);
        
        $testnetResult = $encoder->stakeToBech32($stakeKey, 'stake_test');
        $mainnetResult = $encoder->stakeToBech32($stakeKey, 'stake');
        
        expect($testnetResult)->not->toBe($mainnetResult);
        expect($testnetResult)->toStartWith('stake_test1');
        expect($mainnetResult)->toStartWith('stake1');
    });

    test('stakeToBech32 creates addresses with valid bech32 character set', function () {
        $encoder = new Bech32Encoder();
        
        $stakeKey = chr(0xe0) . str_repeat('d', 28);
        
        $result = $encoder->stakeToBech32($stakeKey, 'stake_test');
        
        // Valid bech32 characters: qpzry9x8gf2tvdw0s3jn54khce6mua7l
        expect($result)->toMatch('/^stake_test1[qpzry9x8gf2tvdw0s3jn54khce6mua7l]+$/');
    });

    test('stakeToBech32 handles different stake key data correctly', function () {
        $encoder = new Bech32Encoder();
        
        // Test with all zeros
        $zeroStakeKey = chr(0xe0) . str_repeat("\x00", 28);
        $zeroResult = $encoder->stakeToBech32($zeroStakeKey, 'stake_test');
        
        // Test with all 0xFF
        $maxStakeKey = chr(0xe1) . str_repeat("\xFF", 28);
        $maxResult = $encoder->stakeToBech32($maxStakeKey, 'stake');
        
        expect($zeroResult)->toBeString();
        expect($maxResult)->toBeString();
        expect($zeroResult)->not->toBe($maxResult);
        expect($zeroResult)->toStartWith('stake_test1');
        expect($maxResult)->toStartWith('stake1');
    });

    test('stakeToBech32 handles binary stake key data', function () {
        $encoder = new Bech32Encoder();
        
        // Create stake key with mixed binary data
        $binaryData = '';
        for ($i = 0; $i < 28; $i++) {
            $binaryData .= chr($i % 256);
        }
        $stakeKey = chr(0xe0) . $binaryData;
        
        $result = $encoder->stakeToBech32($stakeKey, 'stake_test');
        
        expect($result)->toBeString();
        expect($result)->toStartWith('stake_test1');
        expect(strlen($result))->toBeGreaterThan(20); // Should be a reasonable length
    });

    test('stakeToBech32 works with demo data pattern', function () {
        $encoder = new Bech32Encoder();
        
        // Create a stake key that would match the pattern from the demo
        // (This is more of an integration test to ensure the encoder works with real-world data patterns)
        $testStakeKey = chr(0xe0) . hash('sha256', 'test_public_key_data', true);
        $testStakeKey = substr($testStakeKey, 0, 29); // Ensure it's exactly 29 bytes
        
        $result = $encoder->stakeToBech32($testStakeKey, 'stake_test');
        
        expect($result)->toBeString();
        expect($result)->toStartWith('stake_test1');
        expect($result)->toMatch('/^stake_test1[qpzry9x8gf2tvdw0s3jn54khce6mua7l]+$/');
    });
});