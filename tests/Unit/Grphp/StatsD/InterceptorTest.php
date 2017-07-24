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

use Domnikl\Statsd\Connection\UdpSocket;
use Grphp\Client\Response;
use Grphp\StatsD\Interceptor;

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
        $client = $this->getMockBuilder('\Domnikl\Statsd\Client')
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
        $i->call(function()
        {
            $message = new \stdClass();
            $status = new CallStatus();
            $resp = new Response($message, $status);
            return $resp;
        });
    }
    public function providerCall()
    {
        return [
            ['bar', 'grphp.statsd.test.test_client.bar'],
            ['get_thing', 'grphp.statsd.test.test_client.get_thing'],
        ];
    }

}
