<?php

namespace TeamGantt\Subscreeb\Models;

class Plan
{
    /**
     * @var string
     */
    protected string $id;

    /**
     * Plan constructor.
     * @param string $id
     */
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