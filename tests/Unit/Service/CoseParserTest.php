<?php

use CardanoPhp\CIP8Verifier\Service\CoseParser;
use CardanoPhp\CIP8Verifier\DTO\CoseSign1;

describe('CoseParser', function () {
    test('can be instantiated', function () {
        $parser = new CoseParser();
        expect($parser)->toBeInstanceOf(CoseParser::class);
    });

    test('parseCoseSign1 returns CoseSign1 object from valid CBOR data', function () {
        // Use the demo signature CBOR from demo.php
        $signatureCbor = "84582aa201276761646472657373581de07a9647d2048870a0726f78621863e03797dc17b946473a35ded45f75a166686173686564f4582431633364353630312d386563632d343264662d623162302d3061323934643061346564355840d40e65ebb258bd48d04092f485b845a6c0c9b1728e896c8364e51e1b6d67cd2c36dc17ad52409671a8ac8e2376e3bf138869621d03c28841a50cd68bc34fa108";
        $cborData = hex2bin($signatureCbor);
        
        $parser = new CoseParser();
        $coseSign1 = $parser->parseCoseSign1($cborData);
        
        expect($coseSign1)->toBeInstanceOf(CoseSign1::class);
        expect($coseSign1->protected)->toBeString();
        expect($coseSign1->payload)->toBeString();
        expect($coseSign1->signature)->toBeString();
    });

    test('parseCoseSign1 extracts correct payload from demo data', function () {
        $signatureCbor = "84582aa201276761646472657373581de07a9647d2048870a0726f78621863e03797dc17b946473a35ded45f75a166686173686564f4582431633364353630312d386563632d343264662d623162302d3061323934643061346564355840d40e65ebb258bd48d04092f485b845a6c0c9b1728e896c8364e51e1b6d67cd2c36dc17ad52409671a8ac8e2376e3bf138869621d03c28841a50cd68bc34fa108";
        $cborData = hex2bin($signatureCbor);
        
        $parser = new CoseParser();
        $coseSign1 = $parser->parseCoseSign1($cborData);
        
        // The payload should be the wallet auth challenge
        $expectedPayload = "31633364353630312d386563632d343264662d623162302d306132393464306134656435";
        expect(bin2hex($coseSign1->payload))->toBe($expectedPayload);
    });

    test('parseCoseSign1 extracts signature with correct length', function () {
        $signatureCbor = "84582aa201276761646472657373581de07a9647d2048870a0726f78621863e03797dc17b946473a35ded45f75a166686173686564f4582431633364353630312d386563632d343264662d623162302d3061323934643061346564355840d40e65ebb258bd48d04092f485b845a6c0c9b1728e896c8364e51e1b6d67cd2c36dc17ad52409671a8ac8e2376e3bf138869621d03c28841a50cd68bc34fa108";
        $cborData = hex2bin($signatureCbor);
        
        $parser = new CoseParser();
        $coseSign1 = $parser->parseCoseSign1($cborData);
        
        // Ed25519 signature should be 64 bytes
        expect(strlen($coseSign1->signature))->toBe(64);
    });

    test('parseCoseSign1 handles unprotected field correctly', function () {
        $signatureCbor = "84582aa201276761646472657373581de07a9647d2048870a0726f78621863e03797dc17b946473a35ded45f75a166686173686564f4582431633364353630312d386563632d343264662d623162302d3061323934643061346564355840d40e65ebb258bd48d04092f485b845a6c0c9b1728e896c8364e51e1b6d67cd2c36dc17ad52409671a8ac8e2376e3bf138869621d03c28841a50cd68bc34fa108";
        $cborData = hex2bin($signatureCbor);
        
        $parser = new CoseParser();
        $coseSign1 = $parser->parseCoseSign1($cborData);
        
        // Unprotected can be various types, just verify it's set
        expect($coseSign1->unprotected)->not->toBeNull();
    });

    test('parseCoseSign1 returns consistent results', function () {
        $signatureCbor = "84582aa201276761646472657373581de07a9647d2048870a0726f78621863e03797dc17b946473a35ded45f75a166686173686564f4582431633364353630312d386563632d343264662d623162302d3061323934643061346564355840d40e65ebb258bd48d04092f485b845a6c0c9b1728e896c8364e51e1b6d67cd2c36dc17ad52409671a8ac8e2376e3bf138869621d03c28841a50cd68bc34fa108";
        $cborData = hex2bin($signatureCbor);
        
        $parser = new CoseParser();
        $coseSign1_1 = $parser->parseCoseSign1($cborData);
        $coseSign1_2 = $parser->parseCoseSign1($cborData);
        
        expect($coseSign1_1->protected)->toBe($coseSign1_2->protected);
        expect($coseSign1_1->payload)->toBe($coseSign1_2->payload);
        expect($coseSign1_1->signature)->toBe($coseSign1_2->signature);
    });

    test('parseCoseSign1 throws exception for invalid CBOR data', function () {
        $invalidCborData = hex2bin("ff"); // Invalid CBOR data that cannot be decoded
        
        $parser = new CoseParser();
        
        $threwException = false;
        try {
            $parser->parseCoseSign1($invalidCborData);
        } catch (Throwable $e) {
            $threwException = true;
        }
        
        expect($threwException)->toBeTrue();
    });

    test('parseCoseSign1 throws exception for malformed COSE structure', function () {
        // Valid CBOR but not a proper COSE_Sign1 structure (just a simple array)
        $malformedCbor = hex2bin("8301020304"); // CBOR array [1, 2, 3, 4] but not a proper COSE structure
        
        $parser = new CoseParser();
        
        expect(function () use ($parser, $malformedCbor) {
            $parser->parseCoseSign1($malformedCbor);
        })->toThrow(Exception::class);
    });
});