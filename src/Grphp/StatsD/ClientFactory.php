<?php
/**
 * Copyright (c) 2017-present, BigCommerce Pty. Ltd. All rights reserved
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
 * OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
namespace Grphp\StatsD;

use Domnikl\Statsd\Client as StatsClient;
use Domnikl\Statsd\Connection\TcpSocket;
use Domnikl\Statsd\Connection\UdpSocket;

/**
 * Generates StatsD clients
 *
 * @package Grphp\StatsD
 */
class ClientFactory
{
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Build a new statsd client
     *
     * @param array $config
     * @return StatsClient
     */
    public static function build(array $config = [])
    {
        $factory = new static($config);
        return $factory->generate();
    }

    /**
     * @return StatsClient
     */
    public function generate()
    {
        $tcp = $this->option('tcp', false);
        $port = $this->option('port', 8125);
        $hostname = $this->option('host', '127.0.0.1');
        $namespace = $this->option('namespace', '');
        $timeout = $this->option('timeout');
        $persistent = $this->option('persistent', false);
        $mtu = $this->option('mtu', 1500);
        $sampleRate = $this->option('sample_rate', 1.0);

        if ($tcp) {
            $connection = new TcpSocket($hostname, $port, $timeout, $persistent, $mtu);
        } else {
            $connection = new UdpSocket($hostname, $port, $timeout, $persistent, $mtu);
        }
        return new StatsClient($connection, $namespace, $sampleRate);
    }

    /**
     * Get a set config option with a default
     *
     * @param string $k
     * @param mixed $default
     * @return mixed
     */
    private function option($k, $default = null)
    {
        return array_key_exists($k, $this->config) ? $this->config[$k] : $default;
    }
}
