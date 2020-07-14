<?php

namespace TeamGantt\Subscreeb\Models;

class Payment
{
    /**
     * @var string
     */
    protected string $nonce;

    /**
     * @var string
     */
    protected string $token;

    /**
     * Payment constructor.
     * @param string $nonce
     * @param string $token
     */
    public function __construct(string $nonce, string $token = '')
    {
        $this->nonce = $nonce;
        $this->token = $token;
    }

    /**
     * @return string
     */
    public function getNonce(): string
    {
        return $this->nonce;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     * @return Payment
     */
    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }
}
