# Elastic APM for Guzzle

This library supports Span traces of HTTP calls and distributed traces with [Guzzle](https://github.com/guzzle/guzzle).

## Installation

1) Install via [composer](https://getcomposer.org/)

    ```shell script
    composer require pccomponentes/apm-guzzle
    ```

## Usage

In all cases, an already created instance of [ElasticApmTracer](https://github.com/zoilomora/elastic-apm-agent-php) is assumed.

### Native PHP

```php
<?php
declare(strict_types=1);

$apmMiddleware = PcComponentes\ElasticAPM\GuzzleHttp\ElasticApmMiddleware::trace(
    $apmTracer, /** \ZoiloMora\ElasticAPM\ElasticApmTracer instance. */
);

$handler = GuzzleHttp\HandlerStack::create();
$handler->push($apmMiddleware);

$config = [
    'handler' => $handler,
];

$client = new GuzzleHttp\Client($config);
```

### Service Container (Symfony)

```yaml
http.client:
  class: GuzzleHttp\Client
  arguments:
    $config:
      handler: '@guzzle.handler'

guzzle.handler:
  class: GuzzleHttp\HandlerStack
  factory: 'GuzzleHttp\HandlerStack::create'
  calls:
    - method: push
      arguments: ['@guzzle.middleware.apm', 'trace']

guzzle.middleware.apm:
  class: Closure
  factory: 'PcComponentes\ElasticAPM\GuzzleHttp\ElasticApmMiddleware::trace'
  arguments:
    $elasticApmTracer: '@apm.tracer' # \ZoiloMora\ElasticAPM\ElasticApmTracer instance.
```

## License
Licensed under the [MIT license](http://opensource.org/licenses/MIT)

Read [LICENSE](LICENSE) for more information
