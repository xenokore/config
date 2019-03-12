<?php

namespace Xenokore\Config;

use function DI\create;

return [
    Config::class => create(),

    'config' => function ($container) {
        return $container->get(Config::class);
    }
];
