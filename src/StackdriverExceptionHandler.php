<?php

namespace GlueDev\Laravel\Stackdriver;

use Google\Cloud\Core\Report\SimpleMetadataProvider;
use Google\Cloud\ErrorReporting\Bootstrap;
use Google\Cloud\Logging\LoggingClient;
use Illuminate\Support\Facades\Cache;

class StackdriverExceptionHandler
{
    public static function report($exception): void
    {
        if (config('stackdriver.enabled') === false || config('stackdriver.error_reporting.enabled') === false) {
            return;
        }

        list($projectId, $service, $labels) = [
            config('stackdriver.credentials.projectId'),
            config('stackdriver.error_reporting.serviceId'),
            config('stackdriver.error_reporting.labels'),
        ];

        $version = config('stackdriver.error_reporting.versionIdCacheKey')
            ? Cache::get(config('stackdriver.error_reporting.versionIdCacheKey'))
            : config('stackdriver.error_reporting.versionId');

        $clientOptions = array_merge(
            config('stackdriver.credentials'),
            config('stackdriver.logger.clientOptions')
        );

        $metadata = new SimpleMetadataProvider([], $projectId, $service, $version, $labels);
        $logging = new LoggingClient($clientOptions);

        $psrOptions = array_merge(
            ['metadataProvider' => $metadata],
            config('stackdriver.error_reporting.psrOptions')
        );
        $psrLogger = $logging->psrLogger('error-log', $psrOptions);

        Bootstrap::init($psrLogger);
        Bootstrap::exceptionHandler($exception);
    }
}
