<?php

// Load composer
// Note: This is not required if package was installed via the 'composer require cardano-php/cip8-verifier'
require 'vendor/autoload.php';

// Imports
use CardanoPhp\CIP8Verifier\CIP8Verifier;
use CardanoPhp\CIP8Verifier\DTO\VerificationRequest;
use CardanoPhp\CIP8Verifier\Exception\CIP8VerificationException;

// Test payload
$signatureCbor = "84582aa201276761646472657373581de07a9647d2048870a0726f78621863e03797dc17b946473a35ded45f75a166686173686564f4582431633364353630312d386563632d343264662d623162302d3061323934643061346564355840d40e65ebb258bd48d04092f485b845a6c0c9b1728e896c8364e51e1b6d67cd2c36dc17ad52409671a8ac8e2376e3bf138869621d03c28841a50cd68bc34fa108";
$signatureKey = "a4010103272006215820eb59d52fbd257d3f8f8f51dd59b2013092763fc9cbc109d32d837920be5e62be";
$walletAuthChallengeHex = "31633364353630312d386563632d343264662d623162302d306132393464306134656435";
$stakeKeyAddress = "stake_test1upafv37jqjy8pgrjdauxyxrruqme0hqhh9ryww34mm297agc0f3vc";
$networkMode = 0;

// Perform verification
try {
    $verifier = CIP8Verifier::create();
    $request = new VerificationRequest(
        $signatureCbor,
        $signatureKey,
        $walletAuthChallengeHex,
        $stakeKeyAddress,
        $networkMode,
    );
    $result = $verifier->verify($request);

    var_dump($result->toArray());
    // returns:
    // array(4) {
    //   ["isValid"]=>
    //   bool(true)
    //   ["walletMatches"]=>
    //   bool(true)
    //   ["payloadMatches"]=>
    //   bool(true)
    //   ["signatureValidates"]=>
    //   bool(true)
    // }

    var_dump($result->isValid);
    // returns:
    // bool(true)

    var_dump($result->walletMatches);
    // returns:
    // bool(true)

    var_dump($result->payloadMatches);
    // returns:
    // bool(true)

    var_dump($result->signatureValidates);
    // returns:
    // bool(true)

} catch (CIP8VerificationException $e) {
    echo "Verification error: " . $e->getMessage() . "\n";
} catch (Throwable $e) {
    echo "Unexpected error: " . $e->getMessage() . "\n";
}
