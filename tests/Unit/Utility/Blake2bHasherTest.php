<?php

use CardanoPhp\CIP8Verifier\Utility\Blake2bHasher;

describe('Blake2bHasher', function () {
    test('hash method returns correct length with default parameters', function () {
        $data = 'test data';
        $hash = Blake2bHasher::hash($data);

        expect(strlen($hash))->toBe(32); // Default length
        expect($hash)->toBeString();
    });

    test('hash method returns correct length with custom length', function () {
        $data = 'test data';
        $length = 16;
        $hash = Blake2bHasher::hash($data, $length);

        expect(strlen($hash))->toBe($length);
        expect($hash)->toBeString();
    });

    test('hash method returns correct length for 28 bytes (used in stake address)', function () {
        $data = 'test data';
        $length = 28;
        $hash = Blake2bHasher::hash($data, $length);

        expect(strlen($hash))->toBe($length);
        expect($hash)->toBeString();
    });

    test('hash method returns different hashes for different inputs', function () {
        $data1 = 'test data 1';
        $data2 = 'test data 2';
        
        $hash1 = Blake2bHasher::hash($data1);
        $hash2 = Blake2bHasher::hash($data2);

        expect($hash1)->not->toBe($hash2);
    });

    test('hash method returns same hash for same input', function () {
        $data = 'consistent test data';
        
        $hash1 = Blake2bHasher::hash($data);
        $hash2 = Blake2bHasher::hash($data);

        expect($hash1)->toBe($hash2);
    });

    test('hash method works with binary data', function () {
        $binaryData = hex2bin('deadbeef');
        $hash = Blake2bHasher::hash($binaryData, 16);

        expect(strlen($hash))->toBe(16);
        expect($hash)->toBeString();
    });

    test('hash method works with empty string', function () {
        $hash = Blake2bHasher::hash('', 16);

        expect(strlen($hash))->toBe(16);
        expect($hash)->toBeString();
    });

    test('hash method with different lengths produces different results', function () {
        $data = 'test data';
        
        $hash16 = Blake2bHasher::hash($data, 16);
        $hash32 = Blake2bHasher::hash($data, 32);

        expect(strlen($hash16))->toBe(16);
        expect(strlen($hash32))->toBe(32);
        expect($hash16)->not->toBe(substr($hash32, 0, 16)); // Different algorithms
    });
});
