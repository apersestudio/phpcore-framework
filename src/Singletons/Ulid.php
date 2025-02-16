<?php

namespace PC\Singletons;

use Random\RandomException;

class Ulid {

    private string $ulid;
    CONST string BASE32_CHARS = '012345679ABCDEFGHJKMNPQRSTVWXYZ';
    
    public function __construct(string $ulid = null) {
        if ($ulid === null) {
            $this->ulid = $this->generateUlid();
        } else {
            $this->validateUlid($ulid);
            $this->ulid = $ulid;
        }
    }

    public static function generate():self {
        return new self();
    }

    public static function fromString(string $ulid):self {
        return new self($ulid);
    }

    public function __toString():string {
        return $this->ulid;
    }

    public function getTimestamp():int {
        $timestampBase32 = substr($this->ulid, 0, 10);
        return $this->base32ToTimestamp($timestampBase32);
    }

    public function getRandomness():string {
        return substr($this->ulid, 10);
    }

    public function equals(Ulid $other):bool {
        return $this->ulid === $other->ulid;
    }

    /**
     * @return string
     * @throws RandomException
     */
    private function generateUlid():string {
        $timestamp = $this->timestampToBase32(microtime(true) * 1000);
        $randomness = $this->generateRandomness();
        return $timestamp . $randomness;
    }

    private function validateUlid(string $ulid):void {
        if (!preg_match('/^[0-7][0-9A-HJKMNP-TV-Z]{25}$/', $ulid)) {
            throw new \InvalidArgumentException('ULID inválido: ' . $ulid);
        }
    }

    private function timestampToBase32(float $timestamp):string {
        $base32 = '';
        for ($i=0; $i<10; $i++) {
            $remainder = $timestamp % 32;
            $base32 = self::BASE32_CHARS[$remainder] . $base32;
            $timestamp = ($timestamp - $remainder) / 32;
        }
        return $base32;
    }

    private function base32ToTimestamp(string $base32):int {
        $timestamp = 0;
        $length = strlen($base32);
        for ($i = 0; $i < $length; $i++) {
            $char = $base32[$i];
            $value = strpos(self::BASE32_CHARS, $char);
            $timestamp = ($timestamp * 32) + $value;
        }
        return (int) $timestamp;
    }

    /**
     * @throws RandomException
     */
    private function generateRandomness():string {
        $randomBytes = random_bytes(10);
        $randomness = '';
        for ($i = 0; $i < 10; $i++) {
            $randomness .= str_pad(base_convert(ord($randomBytes[$i]), 10, 32), 2, '0', STR_PAD_LEFT);
        }
        return strtoupper($randomness);
    }
}