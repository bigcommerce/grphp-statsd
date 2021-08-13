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
namespace Grphp\StatsD\Test;

use Domnikl\Statsd\Client;
use Domnikl\Statsd\Connection\InMemory;
use Domnikl\Statsd\Connection\UdpSocket;
use Grphp\Client\Error\Status;
use Grphp\Client\Response;
use Grphp\StatsD\ClientFactory;
use Grphp\StatsD\Interceptor;
use stdClass;

class InterceptorTest extends BaseTest
{

    /**
     * @dataProvider providerCall
     * @param string $method
     * @param string $expectedKey
     */
    public function testCall($method, $expectedKey)
    {
        $connection = new UdpSocket();
        $client = $this->getMockBuilder(Client::class)
            ->setConstructorArgs([
                $connection
            ])->getMock();
        $client->expects($this->once())->method('timing')->with($expectedKey);

        $i = new Interceptor([
            'client' => $client,
        ]);
        $s = new TestClient();
        $i->setStub($s);
        $i->setMethod($method);
        $i->call(function () {
            $message = new stdClass();
            $status = new Status(200, 'OK');
            return new Response($message, $status);
        });
    }
    public function providerCall()
    {
        return [
            ['bar', 'grphp.statsd.test.test_client.bar'],
            ['get_thing', 'grphp.statsd.test.test_client.get_thing'],
        ];
    }

    public function testBaseCallWithSuccessResult(): void
    {
        $stub = new TestClient();
        $method = 'testMethod';
        $connection = new InMemory();
        $client = ClientFactory::build(['connection' => $connection]);
        $interceptor = new Interceptor(['client' => $client]);
        $interceptor->setStub($stub);
        $interceptor->setMethod($method);

        $interceptor->call(function () {
            return new Response(new stdClass(), new Status(0, 'OK'));
        });

        $this->assertCount(3, $connection->getMessages());
        $this->assertStringMatchesFormat('grphp.statsd.test.test_client.testmethod:%f|ms', $connection->getMessages()[0]);
        $this->assertSame('grphp.statsd.test.test_client.testmethod:1|c', $connection->getMessages()[1]);
        $this->assertSame('grphp.statsd.test.test_client.testmethod.success:1|c', $connection->getMessages()[2]);
    }

    public function testBaseCallWithFailResult(): void
    {
        $stub = new TestClient();
        $method = 'testMethod';
        $connection = new InMemory();
        $client = ClientFactory::build(['connection' => $connection]);
        $interceptor = new Interceptor(['client' => $client]);
        $interceptor->setStub($stub);
        $interceptor->setMethod($method);

        $interceptor->call(function () {
            return new Response(new stdClass(), new Status(1, 'Fail'));
        });

        $this->assertCount(3, $connection->getMessages());
        $this->assertStringMatchesFormat('grphp.statsd.test.test_client.testmethod:%f|ms', $connection->getMessages()[0]);
        $this->assertSame('grphp.statsd.test.test_client.testmethod:1|c', $connection->getMessages()[1]);
        $this->assertSame('grphp.statsd.test.test_client.testmethod.failure:1|c', $connection->getMessages()[2]);
    }

    public function testTwoBaseCallsWithFailResult(): void
    {
        $stub = new TestClient();
        $method = 'testMethod';
        $connection = new InMemory();
        $client = ClientFactory::build(['connection' => $connection]);
        $interceptor = new Interceptor(['client' => $client]);
        $interceptor->setStub($stub);
        $interceptor->setMethod($method);

        $interceptor->call(function () {
            return new Response(new stdClass(), new Status(1, 'Fail'));
        });

        $interceptor->call(function () {
            return new Response(new stdClass(), new Status(1, 'Fail'));
        });

        $this->assertCount(6, $connection->getMessages());
        $this->assertStringMatchesFormat('grphp.statsd.test.test_client.testmethod:%f|ms', $connection->getMessages()[0]);
        $this->assertSame('grphp.statsd.test.test_client.testmethod:1|c', $connection->getMessages()[1]);
        $this->assertSame('grphp.statsd.test.test_client.testmethod.failure:1|c', $connection->getMessages()[2]);

        $this->assertStringMatchesFormat('grphp.statsd.test.test_client.testmethod:%f|ms', $connection->getMessages()[3]);
        $this->assertSame('grphp.statsd.test.test_client.testmethod:1|c', $connection->getMessages()[4]);
        $this->assertSame('grphp.statsd.test.test_client.testmethod.failure:1|c', $connection->getMessages()[5]);
    }

    public function testTwoBaseCallsWithOneSuccessOneFailResult(): void
    {
        $stub = new TestClient();
        $method = 'testMethod';
        $connection = new InMemory();
        $client = ClientFactory::build(['connection' => $connection]);
        $interceptor = new Interceptor(['client' => $client]);
        $interceptor->setStub($stub);
        $interceptor->setMethod($method);

        $interceptor->call(function () {
            return new Response(new stdClass(), new Status(0, 'Fail'));
        });

        $interceptor->call(function () {
            return new Response(new stdClass(), new Status(1, 'Fail'));
        });

        $this->assertCount(6, $connection->getMessages());
        $this->assertStringMatchesFormat('grphp.statsd.test.test_client.testmethod:%f|ms', $connection->getMessages()[0]);
        $this->assertSame('grphp.statsd.test.test_client.testmethod:1|c', $connection->getMessages()[1]);
        $this->assertSame('grphp.statsd.test.test_client.testmethod.success:1|c', $connection->getMessages()[2]);

        $this->assertStringMatchesFormat('grphp.statsd.test.test_client.testmethod:%f|ms', $connection->getMessages()[3]);
        $this->assertSame('grphp.statsd.test.test_client.testmethod:1|c', $connection->getMessages()[4]);
        $this->assertSame('grphp.statsd.test.test_client.testmethod.failure:1|c', $connection->getMessages()[5]);
    }

    public function testBaseCallWithGaugeMetric(): void
    {
        $stub = new TestClient();
        $method = 'testMethod';
        $connection = new InMemory();
        $client = ClientFactory::build(['connection' => $connection]);
        $interceptor = new Interceptor(['client' => $client, 'gauge' => true]);
        $interceptor->setStub($stub);
        $interceptor->setMethod($method);

        $interceptor->call(function () {
            return new Response(new stdClass(), new Status(0, 'OK'));
        });

        $this->assertCount(4, $connection->getMessages());
        $this->assertStringMatchesFormat('grphp.statsd.test.test_client.testmethod:%f|ms', $connection->getMessages()[0]);
        $this->assertSame('grphp.statsd.test.test_client.testmethod:1|c', $connection->getMessages()[1]);
        $this->assertStringMatchesFormat('grphp.statsd.test.test_client.testmethod:%f|g', $connection->getMessages()[2]);
        $this->assertSame('grphp.statsd.test.test_client.testmethod.success:1|c', $connection->getMessages()[3]);
    }
}
