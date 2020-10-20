<?php

use Kahlan\Filter\Filters;

Filters::apply($this, 'run', function ($next) {
    $scope = $this->suite()->root()->scope(); // The top most describe scope.
    $scope->loadFixture = function ($path) {
        $fixture = __DIR__ . '/spec/' . $path;
        if (! file_exists($fixture)) {
            throw new RuntimeException("Could not find fixture at path $fixture");
        }
        $contents = file_get_contents($fixture);
        return json_decode($contents, true);
    };
    return $next();
});
