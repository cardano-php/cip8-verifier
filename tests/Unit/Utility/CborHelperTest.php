<?php

use CardanoPhp\CIP8Verifier\Utility\CborHelper;

describe('CborHelper', function () {
    test('createSigStructure creates valid signature structure', function () {
        $protected = 'test_protected';
        $payload = 'test_payload';
        
        $sigStructure = CborHelper::createSigStructure($protected, $payload);
        
        expect($sigStructure)->toBeString();
        expect(strlen($sigStructure))->toBeGreaterThan(0);
        
        // Verify it starts with the array marker for 4 elements (0x84)
        expect(ord($sigStructure[0]))->toBe(0x84);
    });

    test('createSigStructure includes Signature1 context', function () {
        $protected = 'test';
        $payload = 'test';
        
        $sigStructure = CborHelper::createSigStructure($protected, $payload);
        
        // Should contain "Signature1" string
        expect(strpos($sigStructure, 'Signature1'))->not->toBeFalse();
    });

    test('createSigStructure handles different protected lengths', function () {
        // Test with short protected data (< 24 bytes)
        $shortProtected = 'short';
        $payload = 'test';
        $shortSig = CborHelper::createSigStructure($shortProtected, $payload);
        
        // Test with longer protected data (>= 24 bytes)
        $longProtected = str_repeat('a', 30);
        $longSig = CborHelper::createSigStructure($longProtected, $payload);
        
        expect($shortSig)->toBeString();
        expect($longSig)->toBeString();
        expect($shortSig)->not->toBe($longSig);
    });

    test('createSigStructure handles different payload lengths', function () {
        $protected = 'test';
        
        // Test with short payload (< 24 bytes)
        $shortPayload = 'short';
        $shortSig = CborHelper::createSigStructure($protected, $shortPayload);
        
        // Test with longer payload (>= 24 bytes)
        $longPayload = str_repeat('b', 30);
        $longSig = CborHelper::createSigStructure($protected, $longPayload);
        
        expect($shortSig)->toBeString();
        expect($longSig)->toBeString();
        expect($shortSig)->not->toBe($longSig);
    });

    test('encodeArray creates valid CBOR array encoding', function () {
        $array = ['test1', 'test2'];
        $encoded = CborHelper::encodeArray($array);
        
        expect($encoded)->toBeString();
        expect(strlen($encoded))->toBeGreaterThan(0);
        
        // Should start with array marker for 2 elements (0x82)
        expect(ord($encoded[0]))->toBe(0x82);
    });

    test('encodeArray handles empty array', function () {
        $array = [];
        $encoded = CborHelper::encodeArray($array);
        
        expect($encoded)->toBeString();
        expect(strlen($encoded))->toBe(1);
        
        // Should be array marker for 0 elements (0x80)
        expect(ord($encoded[0]))->toBe(0x80);
    });

    test('encode handles string values correctly', function () {
        $shortString = 'test';
        $encoded = CborHelper::encode($shortString);
        
        expect($encoded)->toBeString();
        expect(strlen($encoded))->toBeGreaterThan(strlen($shortString));
        
        // Short strings should start with 0x60 + length
        expect(ord($encoded[0]))->toBe(0x60 + strlen($shortString));
    });

    test('encode handles integer values correctly', function () {
        $smallInt = 5;
        $encoded = CborHelper::encode($smallInt);
        
        expect($encoded)->toBeString();
        expect(strlen($encoded))->toBe(1);
        
        // Small positive integers are encoded directly
        expect(ord($encoded[0]))->toBe($smallInt);
    });

    test('encode handles array values correctly', function () {
        $array = ['a', 'b'];
        $encoded = CborHelper::encode($array);
        
        expect($encoded)->toBeString();
        expect(strlen($encoded))->toBeGreaterThan(0);
        
        // Should start with array marker for 2 elements (0x82)
        expect(ord($encoded[0]))->toBe(0x82);
    });

    test('decode can parse CBOR data', function () {
        // This test uses the actual CBOR library to verify decode functionality
        // We'll test with simple data that we know the structure of
        
        // Create a simple CBOR array with two strings
        $simpleArray = CborHelper::encodeArray(['hello', 'world']);
        $decoded = CborHelper::decode($simpleArray);
        
        expect($decoded)->not->toBeNull();
        // The decoded object should be a CBOR object that we can get elements from
        expect(method_exists($decoded, 'get'))->toBeTrue();
    });

    test('createSigStructure is deterministic', function () {
        $protected = 'test_protected';
        $payload = 'test_payload';
        
        $sig1 = CborHelper::createSigStructure($protected, $payload);
        $sig2 = CborHelper::createSigStructure($protected, $payload);
        
        expect($sig1)->toBe($sig2);
    });
});
