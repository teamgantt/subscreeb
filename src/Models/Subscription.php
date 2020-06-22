<?php

namespace TeamGantt\Subscreeb\Models;

class Subscription
{
    /**
     * @var string
     */
    private string $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }
}
