<?php
declare(strict_types=1);

namespace PcComponentes\ElasticAPM\GuzzleHttp;

use GuzzleHttp\Psr7\Request;
use ZoiloMora\ElasticAPM\ElasticApmTracer;
use ZoiloMora\ElasticAPM\Events\Span\Context;
use ZoiloMora\ElasticAPM\Helper\DistributedTracing;

final class ElasticApmMiddleware
{
    private const STACKTRACE_SKIP = 11;

    public static function trace(ElasticApmTracer $elasticApmTracer): \Closure
    {
        return static function (callable $handler) use ($elasticApmTracer) {
            return static function (Request $request, array $options) use ($handler, $elasticApmTracer) {
                if (false === $elasticApmTracer->active()) {
                    return $handler($request, $options);
                }

                $name = \sprintf(
                    '%s %s',
                    $request->getMethod(),
                    (string) $request->getUri(),
                );

                $span = $elasticApmTracer->startSpan(
                    $name,
                    'request',
                    null,
                    null,
                    new Context(),
                    self::STACKTRACE_SKIP,
                );

                $newRequest = $request->withHeader(
                    DistributedTracing::HEADER_NAME,
                    $span->distributedTracing(),
                );

                /** @var \GuzzleHttp\Promise\FulfilledPromise $result */
                $result = $handler($newRequest, $options);

                /** @var \GuzzleHttp\Psr7\Response $response */
                $response = $result->wait();

                $span->stop();

                $span->context()->setHttp(
                    new Context\Http(
                        (string) $request->getUri(),
                        $response->getStatusCode(),
                        $request->getMethod(),
                    )
                );

                return $result;
            };
        };
    }
}
