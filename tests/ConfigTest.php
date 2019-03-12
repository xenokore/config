<?php

namespace Xenokore\Config\Tests;

use Xenokore\Config\Config;
use Xenokore\Config\Exception\InvalidConfigException;
use Xenokore\Utility\Exception\DirectoryNotAccessibleException;

use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testInvalidGet()
    {
        $config = new Config();

        $this->assertNull($config->get('test.var'));
    }

    public function testDirException()
    {
        $this->expectException(DirectoryNotAccessibleException::class);

        $config = new Config(__DIR__ . '/' . sha1(microtime() . mt_rand()) . '/not_existing_path');
    }

    public function testConfigException()
    {
        $this->expectException(InvalidConfigException::class);

        $config = new Config(__DIR__ . '/data/configs');

        $var = $config->get('test_invalid.abc');
    }

    public function testFileGet()
    {
        $config = new Config(__DIR__ . '/data/configs');

        // Test if the files get loaded as arrays
        $this->assertIsArray($config->get('test'));
        $this->assertIsArray($config->get('test_dot'));

        // Test the normal config keys
        $this->assertIsString($config->get('test.test_string'));
        $this->assertIsArray($config->get('test.test_array'));
        $this->assertIsInt($config->get('test.test_int'));
        $this->assertIsFloat($config->get('test.test_float'));
        $this->assertIsBool($config->get('test.test_bool'));
        $this->assertNull($config->get('test.test_null'));

        // Test the dotnotation key as being an array
        $this->assertIsArray($config->get('test_dot.dot'));

        // Test the dotnotation config keys
        $this->assertIsString($config->get('test_dot.dot.string'));
        $this->assertIsArray($config->get('test_dot.dot.array'));
        $this->assertIsInt($config->get('test_dot.dot.int'));
        $this->assertIsFloat($config->get('test_dot.dot.float'));
        $this->assertIsBool($config->get('test_dot.dot.bool'));
        $this->assertNull($config->get('test_dot.dot.null'));
    }

    public function testSet()
    {
        $config = new Config();

        $this->assertEquals(null, $config->get('test.var'));

        $config->set('test.var', 'abc');

        $this->assertEquals('abc', $config->get('test.var'));
    }

    public function testDefault()
    {
        $config = new Config();

        $this->assertEquals(null, $config->get('test.var'));

        $this->assertEquals('abc', $config->get('test.var', 'abc'));
    }
}
