<?php

namespace polypay\ecc\math;
// +----------------------------------------------------------------------
// | Title: 
// +----------------------------------------------------------------------
// | Author: 劳谦君子 <laoqianjunzi@qq.com>
// +----------------------------------------------------------------------
// | Date: 2021年09月22日
// +----------------------------------------------------------------------
// | Description：
// +----------------------------------------------------------------------

use polypay\ecc\util\BinaryString;
use polypay\ecc\util\NumberSize;

class GmpMath implements GmpMathInterface
{

    public function cmp(\GMP $first, \GMP $other): int
    {
        return gmp_cmp($first, $other);
    }

    public function equals(\GMP $first, \GMP $other): bool
    {
        return gmp_cmp($first, $other) === 0;
    }
    
    public function mod(\GMP $number, \GMP $modulus): \GMP
    {
        return gmp_mod($number, $modulus);
    }

    public function add(\GMP $augend, \GMP $addend): \GMP
    {
        return gmp_add($augend, $addend);
    }

    public function sub(\GMP $minuend, \GMP $subtrahend): \GMP
    {
        return gmp_sub($minuend, $subtrahend);
    }

    public function mul(\GMP $multiplier, \GMP $multiplicand): \GMP
    {
        return gmp_mul($multiplier, $multiplicand);
    }

    public function div(\GMP $dividend, \GMP $divisor): \GMP
    {
        return gmp_div($dividend, $divisor);
    }

    public function pow(\GMP $base, int $exponent): \GMP
    {
        return gmp_pow($base, $exponent);
    }

    public function bitwiseAnd(\GMP $first, \GMP $other): \GMP
    {
        return gmp_and($first, $other);
    }

    public function rightShift(\GMP $number, int $positions): \GMP
    {
        return gmp_div($number, gmp_pow(gmp_init(2, 10), $positions));
    }

    public function bitwiseXor(\GMP $first, \GMP $other): \GMP
    {
        return gmp_xor($first, $other);
    }

    public function leftShift(\GMP $number, int $positions): \GMP
    {
       
        return gmp_mul($number, gmp_pow(2, $positions));
    }

    public function toString(\GMP $value): string
    {
        return gmp_strval($value);
    }

    public function hexDec(string $hex): string
    {
        return gmp_strval(gmp_init($hex, 16), 10);
    }

    public function decHex(string $dec): string
    {
        $dec = gmp_init($dec, 10);

        if (gmp_cmp($dec, 0) < 0) {
            throw new \InvalidArgumentException('Unable to convert negative integer to string');
        }

        $hex = gmp_strval($dec, 16);

        if (BinaryString::length($hex) % 2 != 0) {
            $hex = '0'.$hex;
        }

        return $hex;
    }

    public function powmod(\GMP $base, \GMP $exponent, \GMP $modulus): \GMP
    {
        if ($this->cmp($exponent, gmp_init(0, 10)) < 0) {
            throw new \InvalidArgumentException("Negative exponents (" . $this->toString($exponent) . ") not allowed.");
        }

        return gmp_powm($base, $exponent, $modulus);
    }

    public function isPrime(\GMP $n): bool
    {
        $prob = gmp_prob_prime($n);

        if ($prob > 0) {
            return true;
        }

        return false;
    }

    public function nextPrime(\GMP $starting_value): \GMP
    {
        return gmp_nextprime($starting_value);
    }

    public function inverseMod(\GMP $a, \GMP $m): \GMP
    {
        return gmp_invert($a, $m);
    }

    public function jacobi(\GMP $a, \GMP $n): int
    {
        return gmp_jacobi($a, $n);
    }

    public function intToFixedSizeString(\GMP $x, int $byteSize): string
    {
        if ($byteSize < 0) {
            throw new \RuntimeException("Byte size cannot be negative");
        }

        if (gmp_cmp($x, 0) < 0) {
            throw new \RuntimeException("x was negative - not yet supported");
        }

        $two = gmp_init(2);
        $range = gmp_pow($two, $byteSize * 8);
        if (NumberSize::bnNumBits($this, $x) >= NumberSize::bnNumBits($this, $range)) {
            throw new \RuntimeException("Number overflows byte size");
        }

        $maskShift = gmp_pow($two, 8);
        $mask = gmp_mul(gmp_init(255), $range);

        $binary = '';
        for ($i = $byteSize - 1; $i >= 0; $i--) {
            $mask = gmp_div($mask, $maskShift);
            $binary .= pack('C', gmp_strval(gmp_div(gmp_and($x, $mask), gmp_pow($two, $i * 8)), 10));
        }

        return $binary;
    }

    public function intToString(\GMP $x): string
    {
        if (gmp_cmp($x, 0) < 0) {
            throw new \InvalidArgumentException('Unable to convert negative integer to string');
        }

        $hex = gmp_strval($x, 16);

        if (BinaryString::length($hex) % 2 != 0) {
            $hex = '0'.$hex;
        }

        return pack('H*', $hex);
    }

   
    public function stringToInt(string $s): \GMP
    {
        $result = gmp_init(0, 10);
        $sLen = BinaryString::length($s);

        for ($c = 0; $c < $sLen; $c ++) {
            $result = gmp_add(gmp_mul(256, $result), gmp_init(ord($s[$c]), 10));
        }

        return $result;
    }

  
    public function digestInteger(\GMP $m): \GMP
    {
        return $this->stringToInt(hash('sha1', $this->intToString($m), true));
    }

    public function gcd2(\GMP $a, \GMP $b): \GMP
    {
        while ($this->cmp($a, gmp_init(0)) > 0) {
            $temp = $a;
            $a = $this->mod($b, $a);
            $b = $temp;
        }

        return $b;
    }

  
    public function baseConvert(string $number, int $from, int $to): string
    {
        return gmp_strval(gmp_init($number, $from), $to);
    }

    public function getNumberTheory(): NumberTheory
    {
        return new NumberTheory($this);
    }

    public function getModularArithmetic(\GMP $modulus): ModularArithmetic
    {
        return new ModularArithmetic($this, $modulus);
    }
}
