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

use Grphp\Client\Interceptors\Base;

class Interceptor extends Base
{
    /** @var \Domnikl\Statsd\Client $client */
    private $client;

    /**
     * @param callable $callback
     * @return \Grphp\Client\Response
     */
    public function call(callable $callback)
    {
        $cl = $this->client();
        $k = $this->key();

        $startTime = gettimeofday(true);
        /** @var \Grphp\Client\Response $response */
        $response = $callback();
        $elapsed = gettimeofday(true) - $startTime;

        $cl->timing($k, $elapsed);
        $cl->increment($k);
        if ($this->option('gauge', false)) {
            $cl->gauge($k, $elapsed);
        }
        if ($response->isSuccess()) {
            $cl->increment($k . '.success');
        } else {
            $cl->increment($k . '.failure');
        }
        return $response;
    }

    /**
     * @return string
     */
    public function key()
    {
        return $this->serviceKey() . '.' . $this->methodKey();
    }

    /**
     * @return mixed
     */
    private function methodKey()
    {
        return str_replace('\\', '.', strtolower($this->getMethod()));
    }

    /**
     * @return string
     */
    private function serviceKey()
    {
        $s = strtolower(get_class($this->getStub()));
        $s = explode('.', str_replace('\\', '.', $s));
        array_pop($s);
        return implode('.', $s);
    }

    /**
     * @return \Domnikl\Statsd\Client
     */
    private function client()
    {
        if (empty($this->client)) {
            $this->client = $this->option('client');
            if (empty($this->client)) {
                $this->client = ClientFactory::build($this->getOptions());
            }
        }
        return $this->client;
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    private function option($key, $default = null)
    {
        $config = $this->getOptions();
        return array_key_exists($key, $config) ? $config[$key] : $default;
    }
}
