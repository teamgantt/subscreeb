<?php

namespace TeamGantt\Subscreeb\Models;

class Payment
{
    /**
     * @var string
     */
    protected string $nonce;

    /**
     * Payment constructor.
     * @param string $nonce
     */
    public function __construct(string $nonce)
    {
        $this->nonce = $nonce;
    }

    /**
     * @return string
     */
    public function getNonce(): string
    {
        return $this->nonce;
    }
}
