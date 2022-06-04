<?php

namespace JagdishJP\Billdesk\Traits;

trait Encryption
{
    // to encrypt the given string with sha256 method
    public function encrypt($str)
    {
        return strtoupper(hash_hmac('sha256', $str, $this->checksumKey, false));
    }
}
