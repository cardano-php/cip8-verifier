<?php

namespace CardanoPhp\CIP8Verifier\Utility;

use CBOR\Decoder;
use CBOR\OtherObject\OtherObjectManager;
use CBOR\StringStream;
use CBOR\Tag\TagManager;

class CborHelper
{
    public static function decode(string $cborData): mixed
    {
        $otherObjectManager = new OtherObjectManager();
        $tagObjectManager = new TagManager();
        $decoder = new Decoder($tagObjectManager, $otherObjectManager);

        $stream = new StringStream($cborData);
        return $decoder->decode($stream);
    }

    public static function createSigStructure(string $protected, string $payload): string
    {
        $sigStructure = chr(0x84);

        $context = "Signature1";
        $sigStructure .= chr(0x60 + strlen($context)) . $context;

        $protectedLen = strlen($protected);
        if ($protectedLen < 24) {
            $sigStructure .= chr(0x40 + $protectedLen) . $protected;
        } elseif ($protectedLen < 256) {
            $sigStructure .= chr(0x58) . chr($protectedLen) . $protected;
        } else {
            $sigStructure .= chr(0x59) . pack('n', $protectedLen) . $protected;
        }

        $sigStructure .= chr(0x40);

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

    public static function encodeArray(array $array): string
    {
        $result = chr(0x80 + count($array));

        foreach ($array as $item) {
            if (is_string($item) && strlen($item) > 0 && !ctype_print($item)) {
                $len = strlen($item);
                if ($len < 24) {
                    $result .= chr(0x40 + $len) . $item;
                } elseif ($len < 256) {
                    $result .= chr(0x58) . chr($len) . $item;
                } else {
                    $result .= chr(0x59) . pack('n', $len) . $item;
                }
            } else {
                $result .= self::encode($item);
            }
        }

        return $result;
    }

    public static function encode(mixed $data): string
    {
        if (is_array($data)) {
            return self::encodeArray($data);
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
}
