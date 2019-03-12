<?php

namespace Xenokore\Config;

use Xenokore\Utility\Helper\FileHelper;
use Xenokore\Utility\Helper\ArrayHelper;
use Xenokore\Utility\Helper\DirectoryHelper;

use Xenokore\Config\Exception\InvalidConfigException;
use Xenokore\Utility\Exception\DirectoryNotAccessibleException;

class Config extends \ArrayObject implements ConfigInterface, \ArrayAccess
{
    // TODO: implement these:
    // public & offsetExists ( mixed $index ) : bool
    // public & offsetUnset ( mixed $index ) : void

    /**
     * The directory where the active configuration files reside
     * @var string $config_dir
     */
    private $config_dir;

    /**
     * The config files that have already been loaded
     * @var array
     */
    private $loaded_config_files = [];

    /**
     * The active configs that have already been loaded
     * @var array
     */
    public $config = [];

    // /**
    //  * The default configs that have already been loaded
    //  * @var array
    //  */
    // private $default_config = [];

    public function __construct(?string $config_dir = null)
    {
        // Check if config directory exists
        if ($config_dir) {
            if (!DirectoryHelper::isAccessible($config_dir)) {
                throw new DirectoryNotAccessibleException(
                    "active config directory '{$config_dir}' is not accessible"
                );
            }

            $this->config_dir = $config_dir;
        }

        // Load the app config by default
        // $this->_loadConfig('app');
    }

    public function get(string $var, $default = null)
    {
        $exp = [];

        // Get config file, which is the first string in dotnotation
        if (strpos($var, '.') === false) {
            $config = $var;
        } else {
            $exp    = explode('.', $var);
            $config = array_shift($exp);
        }

        // If the var is not a global key, we need try and load the config file if it hasn't been loaded before
        if (!isset($this->loaded_config_files[$config])) {
            $this->_loadConfig($config);
        }

        // Check if we must return the whole config
        if (count($exp) === 0) {
            return $this->config[$config] ?? null;
            // return ArrayHelper::mergeRecursiveDistinct(
            //     $this->default_config[$config] ?? [],
            //     $this->config[$config] ?? []
            // );
        }

        // Get variable name without dotnotation
        $key = implode('.', $exp);

        // Try to get the variable from the active config
        if (isset($this->config[$config])) {
            if (is_array($this->config[$config])) {
                return ArrayHelper::get($this->config[$config], $key); // returns null when not found
            }

            return $this->config[$config];
        }

        // Try to return it from the default config
        // if (isset($this->default_config[$config])) {
        //     return ArrayHelper::get($this->default_config[$config], $key);
        // }

        return $default;
    }

    public function & offsetGet($index)
    {
        $val = $this->get($index, null);
        return $val;
    }

    public function set(string $config, $value): void
    {
        $exp = [];

        // IF there is no dot in the var string then we should set a whole 'config' key
        if (strpos($config, '.') === false) {
            if (isset($this->config[$config])) {
                // Replace all values and their children if it is an array: $a[b][c] = $config
                if (is_array($this->config[$config])) {
                    $this->config[$config] = ArrayHelper::mergeRecursiveDistinct(
                        $this->config[$config],
                        $value
                    );

                    return;
                }
            }

            $this->config[$config] = $value;

            return;
        }

        // We're if working in a config subkey
        $exp    = explode('.', $config);
        $config = array_shift($exp);
        $key    = implode('.', $exp);

        if (isset($this->config[$config]) && is_array($this->config[$config])) {
            ArrayHelper::set($this->config[$config], $key, $value);
        } else {
            $this->config[$config] = $value;
        }
    }

    public function offsetSet($config, $value): void
    {
        $this->set($config, $value);
    }

    // public function loadDefaultConfig(string $config, array $config): void
    // {
    //     // Unfold possible dotnotations
    //     $config = array_merge(
    //         $config,
    //         ArrayHelper::convertDotNotationToArray($config)
    //     );

    //     if (isset($this->default_config[$config])) {
    //         // Set variables and also replace all values and their children: $a[b][c] = $var
    //         $this->default_config[$config] = ArrayHelper::mergeRecursiveDistinct(
    //             $this->default_config[$config],
    //             $config
    //         );
    //     } else {
    //         $this->default_config[$config] = $config;
    //     }
    // }

    private function _loadConfig(string $config): void
    {
        $this->loaded_config_files[$config] = false;

        if ($this->config_dir) {
            $config_path = $this->config_dir . '/' . $config . '.conf.php';

            if (FileHelper::isAccessible($config_path)) {
                $config_data = include $config_path;

                if (!is_array($config_data)) {
                    throw new InvalidConfigException("'{$config_path}' does not contain a valid configuration");
                }

                // Unfold possible dotnotations
                $config_data = array_merge(
                    $config_data,
                    ArrayHelper::convertDotNotationToArray($config_data)
                );

                $this->config[$config] = $config_data;

                $this->loaded_config_files[$config] = true;
            }
        }
    }
}
