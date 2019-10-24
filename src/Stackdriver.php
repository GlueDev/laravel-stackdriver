<?php

namespace GlueDev\Laravel\Stackdriver;

use Google\Cloud\Logging\LoggingClient;
use Google\Cloud\Logging\PsrLogger;
use OpenCensus\Trace\Exporter\StackdriverExporter;
use OpenCensus\Trace\Tracer;
use OpenCensus\Trace\Integrations\Laravel;
use OpenCensus\Trace\Integrations\Mysql;
use OpenCensus\Trace\Integrations\PDO;
use Illuminate\Support\Arr;

class Stackdriver
{
    /**
     * @var object
     */
    private $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public static function createExporter(): StackdriverExporter
    {
        return new StackdriverExporter(array_merge(
            [
                'clientConfig' => array_merge(
                    config('stackdriver.credentials'),
                    config('stackdriver.tracing.clientOptions')
                )
            ],
            config('stackdriver.tracing.exporterOptions')
        ));
    }

    public static function createLogger(): PsrLogger
    {
        $clientOptions = array_merge(
            config('stackdriver.credentials'),
            config('stackdriver.logger.clientOptions')
        );
        $logging = new LoggingClient($clientOptions);

        return $logging->psrLogger(
            config('stackdriver.logger.group_name'),
            config('stackdriver.logger.psrOptions')
        );
    }

    public function startTracing(): void
    {
        if (config('stackdriver.enabled') === false || config('stackdriver.tracing.enabled') === false) {
            return;
        }

        // Do not run in CLI mode
        if (php_sapi_name() == 'cli') {
            return;
        }

        // Enable OpenCensus extension integrations
        Laravel::load();
        Mysql::load();
        PDO::load();

        // Start the request tracing for this request
        $exporter = $this->app['Stackdriver\Exporter'];

        Tracer::start($exporter);
        Tracer::inSpan(
            [
                'name' => 'bootstrap',
                'startTime' => LARAVEL_START,
            ],
            function () { }
        );
    }

    public function listenForLogs(): void
    {
        if (config('stackdriver.enabled') === false || config('stackdriver.logger.enabled') === false) {
            return;
        }

        $this->app['log']->listen(function () {
            $args = Arr::first(func_get_args());
            $this->app['Stackdriver\Logger']->log(
                $args->level,
                $args->message,
                $args->context
            );
        });
    }
}
