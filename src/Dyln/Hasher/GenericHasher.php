<?php

namespace Dyln\Hasher;

class GenericHasher implements Hasher
{
    private $cost;

    public function __construct($cost = 11)
    {
        $this->cost = $cost;
    }

    /**
     * @param string $value
     * @return bool|string
     * @throws \Exception
     */
    public function hash($value)
    {
        $hash = password_hash($value, PASSWORD_BCRYPT, [
            'cost' => $this->cost,
        ]);
        if ($hash === false) {
            throw new \Exception;
        }

        return $hash;
    }

    /**
     * @param String $value
     * @param String $hashedValue
     * @return bool
     */
    public function check($value, $hashedValue)
    {
        if (!$hashedValue) {
            return false;
        }

        return password_verify($value, $hashedValue);
    }

    /** Checks a password against a sha1 hash, if matches - needs a rehash
     * @param string $hashedValue
     * @return bool
     */
    public function needsRehash($hashedValue)
    {
        return password_needs_rehash($hashedValue, PASSWORD_BCRYPT, [
            'cost' => $this->cost,
        ]);
    }
}
