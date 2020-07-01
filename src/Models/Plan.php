<?php

namespace TeamGantt\Subscreeb\Models;

class Plan
{
    /**
     * @var string
     */
    protected string $id;

    /**
     * @var string
     */
    protected string $startDate;

    /**
     * Plan constructor.
     * @param string $id
     * @param string $startDate
     */
    public function __construct(string $id, string $startDate)
    {
        $this->id = $id;
        $this->startDate = $startDate;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getStartDate(): string
    {
        return $this->startDate;
    }
}
