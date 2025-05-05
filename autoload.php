<?php

/*
 * Bear CMS standalone
 * https://github.com/bearcms/standalone
 * Copyright (c) Amplilabs Ltd.
 * Free to use under the MIT license.
 */

$classes = [
    'BearCMS\Standalone' => 'src/Standalone.php',
];

spl_autoload_register(function ($class) use ($classes): void {
    if (isset($classes[$class])) {
        require __DIR__ . '/' . $classes[$class];
    }
}, true);
