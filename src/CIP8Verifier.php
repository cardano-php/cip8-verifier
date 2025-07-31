<?php

namespace CardanoPhp;

use CBOR\Decoder;
use CBOR\OtherObject\OtherObjectManager;
use CBOR\Tag\TagManager;
use CBOR\StringStream;
use Exception;
use SodiumException;

class CIP8Verifier
{
    /**
     * Extract public key from signature key CBOR
     */
    private static function sigKeyToPublicKey($sigKey)
    {
        $otherObjectManager = new OtherObjectManager();
        $tagObjectManager = new TagManager();
        $decoder = new Decoder($tagObjectManager, $otherObjectManager);

        $stream = new StringStream(hex2bin($sigKey));
        $decoded = $decoder->decode($stream);

        // Get the -2 key from the CBOR map (public key x coordinate)
        $publicKeyBytes = $decoded->get(-2);

        // Convert CBOR object to binary data if needed
        if (method_exists($publicKeyBytes, 'getValue')) {
            $publicKeyBytes = $publicKeyBytes->getValue();
        }

        return $publicKeyBytes;
    }

    /**
     * Convert public key to stake key address
     * @throws SodiumException
     */
    private static function publicKeyToStakeKey($publicKey, $networkMode): string
    {
        // Hash the public key using Blake2b 224-bit (28 bytes)
        $publicKeyHash = self::blake2b($publicKey, 28);

        // Create stake address: 0xe + network_mode + hash
        return chr(0xe0 | $networkMode) . $publicKeyHash;
    }

    /**
     * Blake2b hash function
     * @throws SodiumException
     */
    private static function blake2b($data, $length = 32): string
    {
        // Using sodium_crypto_generichash for Blake2b
        return sodium_crypto_generichash($data, '', $length);
    }

    /**
     * Convert stake key to bech32 address
     */
    private static function stakeToBech32($stakeKey, $prefix): string
    {
        return self::bech32Encode($prefix, self::convertBits(str_split($stakeKey), 8, 5));
    }

    /**
     * Bech32 encoding implementation
     */
    private static function bech32Encode($hrp, $data): string
    {
        $combined = array_merge($data, self::bech32CreateChecksum($hrp, $data));
        $ret = $hrp . '1';
        foreach ($combined as $d) {
            $ret .= 'qpzry9x8gf2tvdw0s3jn54khce6mua7l'[$d];
        }
        return $ret;
    }

    /**
     * Convert bits for bech32
     */
    private static function convertBits($data, $fromBits, $toBits, $pad = true): array|null
    {
        $acc = 0;
        $bits = 0;
        $ret = [];
        $maxv = (1 << $toBits) - 1;
        $maxAcc = (1 << ($fromBits + $toBits - 1)) - 1;

        foreach ($data as $value) {
            if (is_string($value)) {
                $value = ord($value);
            }
            if ($value < 0 || ($value >> $fromBits)) {
                return null;
            }
            $acc = (($acc << $fromBits) | $value) & $maxAcc;
            $bits += $fromBits;
            while ($bits >= $toBits) {
                $bits -= $toBits;
                $ret[] = (($acc >> $bits) & $maxv);
            }
        }

        if ($pad) {
            if ($bits) {
                $ret[] = ($acc << ($toBits - $bits)) & $maxv;
            }
        } elseif ($bits >= $fromBits || ((($acc << ($toBits - $bits)) & $maxv))) {
            return null;
        }

        return $ret;
    }

    /**
     * Create bech32 checksum
     */
    private static function bech32CreateChecksum($hrp, $data): array
    {
        $values = array_merge(self::bech32HrpExpand($hrp), $data);
        $polymod = self::bech32Polymod(array_merge($values, [0, 0, 0, 0, 0, 0])) ^ 1;
        $ret = [];
        for ($i = 0; $i < 6; $i++) {
            $ret[] = ($polymod >> 5 * (5 - $i)) & 31;
        }
        return $ret;
    }

    /**
     * Expand HRP for bech32
     */
    private static function bech32HrpExpand($hrp): array
    {
        $ret = [];
        $hrpLen = strlen($hrp);
        for ($i = 0; $i < $hrpLen; $i++) {
            $ret[] = ord($hrp[$i]) >> 5;
        }
        $ret[] = 0;
        for ($i = 0; $i < $hrpLen; $i++) {
            $ret[] = ord($hrp[$i]) & 31;
        }
        return $ret;
    }

    /**
     * Bech32 polymod
     */
    private static function bech32Polymod($values): int
    {
        $generator = [0x3b6a57b2, 0x26508e6d, 0x1ea119fa, 0x3d4233dd, 0x2a1462b3];
        $chk = 1;
        foreach ($values as $value) {
            $top = $chk >> 25;
            $chk = ($chk & 0x1ffffff) << 5 ^ $value;
            for ($i = 0; $i < 5; $i++) {
                $chk ^= (($top >> $i) & 1) ? $generator[$i] : 0;
            }
        }
        return $chk;
    }

    /**
     * Parse COSE_Sign1 structure
     */
    private static function parseCoseSign1($cborData): array
    {
        $otherObjectManager = new OtherObjectManager();
        $tagObjectManager = new TagManager();
        $decoder = new Decoder($tagObjectManager, $otherObjectManager);

        $stream = new StringStream($cborData);
        $decoded = $decoder->decode($stream);

        // COSE_Sign1 structure: [protected, unprotected, payload, signature]
        // Convert CBORObject values to actual data
        $protected = $decoded->get(0);
        $unprotected = $decoded->get(1);
        $payload = $decoded->get(2);
        $signature = $decoded->get(3);

        // Convert CBOR objects to binary data
        if (method_exists($protected, 'getValue')) {
            $protected = $protected->getValue();
        }
        if (method_exists($payload, 'getValue')) {
            $payload = $payload->getValue();
        }
        if (method_exists($signature, 'getValue')) {
            $signature = $signature->getValue();
        }

        return [
            'protected' => $protected,
            'unprotected' => $unprotected,
            'payload' => $payload,
            'signature' => $signature
        ];
    }

    /**
     * Create Sig_structure for verification according to RFC 9052
     */
    private static function createSigStructure($protected, $payload): string
    {
        // According to RFC 9052, Sig_structure = [
        //     context : "Signature1",
        //     body_protected : empty_or_serialized_map,
        //     external_aad : bstr (empty for our case),
        //     payload : bstr
        // ]

        // Create CBOR array with 4 elements
        $sigStructure = '';

        // Array header for 4 elements
        $sigStructure .= chr(0x84);

        // 1. Context: "Signature1" (text string)
        $context = "Signature1";
        $sigStructure .= chr(0x60 + strlen($context)) . $context;

        // 2. body_protected (already serialized CBOR)
        $protectedLen = strlen($protected);
        if ($protectedLen < 24) {
            $sigStructure .= chr(0x40 + $protectedLen) . $protected;
        } elseif ($protectedLen < 256) {
            $sigStructure .= chr(0x58) . chr($protectedLen) . $protected;
        } else {
            $sigStructure .= chr(0x59) . pack('n', $protectedLen) . $protected;
        }

        // 3. external_aad (empty byte string)
        $sigStructure .= chr(0x40); // empty byte string

        // 4. payload (byte string)
        $payloadLen = strlen($payload);
        if ($payloadLen < 24) {
            $sigStructure .= chr(0x40 + $payloadLen) . $payload;
        } elseif ($payloadLen < 256) {
            $sigStructure .= chr(0x58) . chr($payloadLen) . $payload;
        } else {
            $sigStructure .= chr(0x59) . pack('n', $payloadLen) . $payload;
        }

        return $sigStructure;
    }

    /**
     * Proper CBOR array encoder
     */
    private static function encodeCBORArray($array): string
    {
        $result = chr(0x80 + count($array)); // Array header

        foreach ($array as $item) {
            if (is_string($item) && strlen($item) > 0 && !ctype_print($item)) {
                // Binary data - encode as byte string
                $len = strlen($item);
                if ($len < 24) {
                    $result .= chr(0x40 + $len) . $item;
                } elseif ($len < 256) {
                    $result .= chr(0x58) . chr($len) . $item;
                } else {
                    $result .= chr(0x59) . pack('n', $len) . $item;
                }
            } else {
                // Regular string or other data
                $result .= self::encodeCBOR($item);
            }
        }

        return $result;
    }

    /**
     * Simple CBOR encoder for basic types
     */
    private static function encodeCBOR($data): string
    {
        if (is_array($data)) {
            return self::encodeCBORArray($data);
        } elseif (is_string($data)) {
            $len = strlen($data);
            if ($len < 24) {
                return chr(0x60 + $len) . $data;
            } elseif ($len < 256) {
                return chr(0x78) . chr($len) . $data;
            } else {
                return chr(0x79) . pack('n', $len) . $data;
            }
        } elseif (is_int($data)) {
            if ($data >= 0) {
                if ($data < 24) {
                    return chr($data);
                } elseif ($data < 256) {
                    return chr(0x18) . chr($data);
                }
            }
        }
        return '';
    }

    /**
     * Main verification function
     */
    public static function verifySignature($event): array
    {
        $isValid = false;
        $walletMatches = false;
        $payloadMatches = false;
        $signatureValidates = false;
        $error = null;

        try {
            $signatureCbor = $event['signatureCbor'];
            $signatureKey = $event['signatureKey'];
            $walletAuthChallengeHex = $event['walletAuthChallengeHex'];
            $stakeKeyAddress = $event['stakeKeyAddress'];
            $networkMode = $event['networkMode'];

            // Extract public key from a signature key
            $publicKey = self::sigKeyToPublicKey($signatureKey);

            // Generate stake address from a public key
            $stakeAddr = self::publicKeyToStakeKey($publicKey, $networkMode);
            $stakePrefix = $networkMode === 1 ? 'stake' : 'stake_test';
            $generatedStakeAddress = self::stakeToBech32($stakeAddr, $stakePrefix);

            // Check if wallet matches
            $walletMatches = $generatedStakeAddress === $stakeKeyAddress;

            // Parse COSE_Sign1 structure
            $coseData = self::parseCoseSign1(hex2bin($signatureCbor));

            // Verify the signature is the correct length (64 bytes for Ed25519)
            if (strlen($coseData['signature']) !== SODIUM_CRYPTO_SIGN_BYTES) {
                throw new Exception("Invalid signature length: " . strlen($coseData['signature']) . " bytes, expected " . SODIUM_CRYPTO_SIGN_BYTES);
            }

            // Verify the public key is the correct length (32 bytes for Ed25519)
            if (strlen($publicKey) !== SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES) {
                throw new Exception("Invalid public key length: " . strlen($publicKey) . " bytes, expected " . SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES);
            }

            // Create signature structure for verification
            $sigStructure = self::createSigStructure($coseData['protected'], $coseData['payload']);

            // Verify signature using Ed25519
            $signatureValidates = sodium_crypto_sign_verify_detached(
                $coseData['signature'],
                $sigStructure,
                $publicKey
            );

            // Check payload matches
            $signedPayloadHex = bin2hex($coseData['payload']);

            $payloadMatches = (
                // Signed by lite wallet
                $signedPayloadHex === $walletAuthChallengeHex ||
                // Signed by hardware wallet (Blake2b hash)
                $signedPayloadHex === bin2hex(self::blake2b(hex2bin($walletAuthChallengeHex), 28))
            );

            $isValid = $walletMatches && $payloadMatches && $signatureValidates;

        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        return [
            'isValid' => $isValid,
            'walletMatches' => $walletMatches,
            'payloadMatches' => $payloadMatches,
            'signatureValidates' => $signatureValidates,
            'error' => $error
        ];
    }
}
