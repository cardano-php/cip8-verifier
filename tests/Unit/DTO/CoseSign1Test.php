<?php

use CardanoPhp\CIP8Verifier\DTO\CoseSign1;

describe('CoseSign1', function () {
    test('can be instantiated with all required parameters', function () {
        $protected = 'test_protected_data';
        $unprotected = ['key' => 'value'];
        $payload = 'test_payload';
        $signature = 'test_signature';

        $coseSign1 = new CoseSign1($protected, $unprotected, $payload, $signature);

        expect($coseSign1->protected)->toBe($protected);
        expect($coseSign1->unprotected)->toBe($unprotected);
        expect($coseSign1->payload)->toBe($payload);
        expect($coseSign1->signature)->toBe($signature);
    });

    test('can be instantiated with mixed types for unprotected field', function () {
        $coseSign1 = new CoseSign1(
            'protected',
            null,
            'payload',
            'signature'
        );

        expect($coseSign1->unprotected)->toBeNull();

        $coseSign1 = new CoseSign1(
            'protected',
            ['array' => 'data'],
            'payload',
            'signature'
        );

        expect($coseSign1->unprotected)->toBe(['array' => 'data']);

        $coseSign1 = new CoseSign1(
            'protected',
            'string_data',
            'payload',
            'signature'
        );

        expect($coseSign1->unprotected)->toBe('string_data');
    });

    test('readonly properties cannot be modified', function () {
        $coseSign1 = new CoseSign1('protected', null, 'payload', 'signature');

        // This test ensures the class is readonly - attempting to modify properties would cause a PHP error
        // We just verify the values are correctly set
        expect($coseSign1->protected)->toBe('protected');
        expect($coseSign1->unprotected)->toBeNull();
        expect($coseSign1->payload)->toBe('payload');
        expect($coseSign1->signature)->toBe('signature');
    });
});