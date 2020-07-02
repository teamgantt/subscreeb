<?php

namespace TeamGantt\Subscreeb\Models\AddOn;

class AddOnCollection
{
    /**
     * @var array|AddOn[]
     */
    protected array $addons;

    /**
     * AddOnCollection constructor.
     * @param AddOn ...$addons
     */
    public function __construct(AddOn ...$addons)
    {
        $this->addons = $addons;
    }

    /**
     * @param AddOn $addon
     */
    public function addAddon(AddOn $addon): void
    {
        $this->addons[] = $addon;
    }

    /**
     * @return array|AddOn[]
     */
    public function getAddons(): array
    {
        return $this->addons;
    }
}
