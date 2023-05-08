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

use Domnikl\Statsd\Client;
use Grphp\Client\Interceptors\Base;
use Grphp\Client\Response;

class Interceptor extends Base
{
    private Client $client;

    /**
     * @param callable(): Response $callback
     */
    public function call(callable $callback): Response
    {
        $start = microtime(true);
        $response = $callback();
        $elapsed = microtime(true) - $start;
        $elapsed = round($elapsed * 1000.00, 4);

        $this->measure($elapsed, $response->isSuccess());
        return $response;
    }

    /**
     * Measure the response in statsd
     *
     * @param float $elapsed time elapsed in ms
     * @param bool $success if the response is a successful one or not
     */
    private function measure(float $elapsed, bool $success = true)
    {
        $k = $this->key();
        $cl = $this->client();
        $cl->timing($k, $elapsed);
        $cl->increment($k);
        if ($this->option('gauge', false)) {
            $cl->gauge($k, $elapsed);
        }
        if ($success) {
            $cl->increment($k . '.success');
        } else {
            $cl->increment($k . '.failure');
        }
    }

    private function key(): string
    {
        return $this->serviceKey() . '.' . $this->methodKey();
    }

    private function methodKey(): mixed
    {
        return str_replace('\\', '.', strtolower($this->getMethod()));
    }

    private function serviceKey(): string
    {
        $s = get_class($this->getStub());
        $s = str_replace('\\', '.', $s);
        $s = explode('.', $s);
        foreach ($s as &$c) {
            $c = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $c));
        }
        return implode('.', $s);
    }

    private function client(): Client
    {
        if (empty($this->client)) {
            $this->client = $this->option('client') ?? ClientFactory::build($this->getOptions());
        }

        return $this->client;
    }

    private function option(string $key, mixed $default = null): mixed
    {
        $config = $this->getOptions();
        return array_key_exists($key, $config) ? $config[$key] : $default;
    }
}
