<?php

use CardanoPhp\CIP8Verifier\Exception\CIP8VerificationException;

describe('CIP8VerificationException', function () {
    test('extends Exception', function () {
        $exception = new CIP8VerificationException('Test message');

        expect($exception)->toBeInstanceOf(Exception::class);
    });

    test('can be instantiated with custom message', function () {
        $message = 'Custom error message';
        $exception = new CIP8VerificationException($message);

        expect($exception->getMessage())->toBe($message);
    });

    test('can be instantiated without message', function () {
        $exception = new CIP8VerificationException();

        expect($exception->getMessage())->toBe('');
    });

    test('can be thrown and caught', function () {
        expect(function () {
            throw new CIP8VerificationException('Test error');
        })->toThrow(CIP8VerificationException::class, 'Test error');
    });
});