<?php

namespace Xenokore\Config;

interface ConfigInterface extends \ArrayAccess
{
    public function get(string $var, $default = null);

    public function set(string $var, $value): void;

    // public function loadDefaultConfig(string $namespace, array $config): void;
}
