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
declare(strict_types=1);

namespace Unit\Grphp\StatsD;

use Domnikl\Statsd\Client;
use Domnikl\Statsd\Connection\TcpSocket;
use Domnikl\Statsd\Connection\UdpSocket;
use Grphp\StatsD\ClientFactory;
use PHPUnit\Framework\TestCase;

class ClientFactoryTest extends TestCase
{
    public function testBuildsWithTcpSocketIfTcpConfigValueIsFalse(): void
    {
        $expectedClient = new Client(new TcpSocket());
        $actualClient = ClientFactory::build([
            'tcp' => true,
            'host' => 'localhost',
        ]);

        $this->assertEquals($expectedClient, $actualClient);
    }

    public function testBuildWithUdpSocketIfTcpConfigValueIsFalse(): void
    {
        $expectedClient = new Client(new UdpSocket());
        $actualClient = ClientFactory::build([
            'tcp' => false,
            'host' => 'localhost',
        ]);

        $this->assertEquals($expectedClient, $actualClient);
    }

    public function testBuildWithUdpSocketIfNoTcpConfigValueGiven(): void
    {
        $expectedClient = new Client(new UdpSocket());
        $actualClient = ClientFactory::build([
            'host' => 'localhost',
        ]);

        $this->assertEquals($expectedClient, $actualClient);
    }

    public function testBuildIfPortValueIsGiven(): void
    {
        $expectedClient = new Client(new UdpSocket('127.0.0.1', 1234));
        $actualClient = ClientFactory::build([
            'port' => 1234,
        ]);

        $this->assertEquals($expectedClient, $actualClient);
    }

    public function testBuildWhenNoPortValueIsGiven(): void
    {
        $expectedClient = new Client(new UdpSocket('127.0.0.1', 8125));
        $actualClient = ClientFactory::build([]);

        $this->assertEquals($expectedClient, $actualClient);
    }

    public function testBuildWhenHostValueIsGiven(): void
    {
        $expectedClient = new Client(new UdpSocket('test_host'));
        $actualClient = ClientFactory::build([
            'host' => 'test_host',
        ]);

        $this->assertEquals($expectedClient, $actualClient);
    }

    public function testBuildWhenNoHostValueIsGiven(): void
    {
        $expectedClient = new Client(new UdpSocket('127.0.0.1'));
        $actualClient = ClientFactory::build([]);

        $this->assertEquals($expectedClient, $actualClient);
    }

    public function testBuildWhenNamespaceValueIsGiven(): void
    {
        $expectedClient = new Client(new UdpSocket('127.0.0.1'), 'test_namespace');
        $actualClient = ClientFactory::build([
            'namespace' => 'test_namespace',
        ]);

        $this->assertEquals($expectedClient, $actualClient);
    }

    public function testBuildWhenNoNamespaceValueIsGiven(): void
    {
        $expectedClient = new Client(new UdpSocket('127.0.0.1'), '');
        $actualClient = ClientFactory::build([]);

        $this->assertEquals($expectedClient, $actualClient);
    }

    public function testBuildWhenTimeoutValueIsGiven(): void
    {
        $expectedClient = new Client(new UdpSocket('127.0.0.1', 8125, 10));
        $actualClient = ClientFactory::build([
            'timeout' => 10,
        ]);

        $this->assertEquals($expectedClient, $actualClient);
    }

    public function testBuildWhenNoTimeoutValueIsGiven(): void
    {
        $expectedClient = new Client(new UdpSocket('127.0.0.1', 8125, null));
        $actualClient = ClientFactory::build([]);

        $this->assertEquals($expectedClient, $actualClient);
    }

    public function testBuildWhenPersistentValueIsGiven(): void
    {
        $expectedClient = new Client(new UdpSocket('127.0.0.1', 8125, null, true));
        $actualClient = ClientFactory::build([
            'persistent' => true,
        ]);

        $this->assertEquals($expectedClient, $actualClient);
    }

    public function testBuildWhenNoPersistentValueIsGiven(): void
    {
        $expectedClient = new Client(new UdpSocket('127.0.0.1', 8125, null, false));
        $actualClient = ClientFactory::build([]);

        $this->assertEquals($expectedClient, $actualClient);
    }

    public function testBuildWhenMtuValueIsGiven(): void
    {
        $expectedClient = new Client(new UdpSocket('127.0.0.1', 8125, null, false, 1234));
        $actualClient = ClientFactory::build([
            'mtu' => 1234,
        ]);

        $this->assertEquals($expectedClient, $actualClient);
    }

    public function testBuildWhenNoMtuValueIsGiven(): void
    {
        $expectedClient = new Client(new UdpSocket('127.0.0.1', 8125, null, false, 1500));
        $actualClient = ClientFactory::build([]);

        $this->assertEquals($expectedClient, $actualClient);
    }

    public function testBuildWhenSampleRateValueIsGiven(): void
    {
        $expectedClient = new Client(new UdpSocket('127.0.0.1'), '', 10.0);
        $actualClient = ClientFactory::build([
                                                 'sample_rate' => 10.0,
                                             ]);

        $this->assertEquals($expectedClient, $actualClient);
    }

    public function testBuildWhenNoSampleRateValueIsGiven(): void
    {
        $expectedClient = new Client(new UdpSocket('127.0.0.1'), '', 1.0);
        $actualClient = ClientFactory::build([]);

        $this->assertEquals($expectedClient, $actualClient);
    }
}
