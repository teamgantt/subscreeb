<?php

namespace TeamGantt\Subscreeb\Models\Builders;

use ReflectionClass;

abstract class BaseModelBuilder implements ModelBuilderInterface
{
    /**
     * Resets the builder class's properties to their class level defaults.
     *
     * @throws \ReflectionException
     */
    public function reset(): void
    {
        $defaults = (new ReflectionClass(get_class($this)))->getDefaultProperties();

        // This is valid as per the PHP docs: https://www.php.net/manual/en/language.oop5.iterations.php
        // @phpstan-ignore-next-line
        foreach ($this as $key => $value) {
            if (array_key_exists($key, $defaults)) {
                $this->$key = $defaults[$key];
            }
        }
    }
}
