<?php

namespace CardanoPhp\CIP8Verifier\Service;

class Bech32Encoder
{
    public function stakeToBech32(string $stakeKey, string $prefix): string
    {
        return $this->bech32Encode($prefix, $this->convertBits(str_split($stakeKey), 8, 5));
    }

    private function bech32Encode(string $hrp, array $data): string
    {
        $combined = array_merge($data, $this->bech32CreateChecksum($hrp, $data));
        $ret = $hrp . '1';
        foreach ($combined as $d) {
            $ret .= 'qpzry9x8gf2tvdw0s3jn54khce6mua7l'[$d];
        }
        return $ret;
    }

    private function convertBits(array $data, int $fromBits, int $toBits, bool $pad = true): ?array
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

    private function bech32CreateChecksum(string $hrp, array $data): array
    {
        $values = array_merge($this->bech32HrpExpand($hrp), $data);
        $polymod = $this->bech32Polymod(array_merge($values, [0, 0, 0, 0, 0, 0])) ^ 1;
        $ret = [];
        for ($i = 0; $i < 6; $i++) {
            $ret[] = ($polymod >> 5 * (5 - $i)) & 31;
        }
        return $ret;
    }

    private function bech32HrpExpand(string $hrp): array
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

    private function bech32Polymod(array $values): int
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
}
