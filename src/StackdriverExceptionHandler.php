<?php

namespace GlueDev\Laravel\Stackdriver;

use Google\Cloud\Core\Report\SimpleMetadataProvider;
use Google\Cloud\ErrorReporting\Bootstrap;
use Google\Cloud\Logging\LoggingClient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;

class StackdriverExceptionHandler
{
    /**
     * @param mixed $exception \Throwable (PHP 7) or \Exception (PHP 5)
     * @param array $additionalContext
     */
    public static function report($exception, array $additionalContext = []): void
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
        self::logError($psrLogger, $exception, $additionalContext);
    }

    private static function logError($psrLogger, $exception, array $additionalContext): void
    {
        $message = sprintf('PHP Notice: %s', (string) $exception);

        $psrLogger->error($message, array_merge_recursive([
            'context' => [
                'reportLocation' => [
                    'filePath' => $exception->getFile(),
                    'lineNumber' => $exception->getLine(),
                    'functionName' => self::getFunctionNameForReport($exception->getTrace()),
                ],
                'user' => (string) (Auth::user()->id ?? ''),
                'httpRequest' => [
                    'url' => Request::url(),
                    'method' => Request::method(),
                    'userAgent' => Request::userAgent(),
                    'referrer' => Request::server('HTTP_REFERER'),
                    'remoteIp' => Request::ip(),
                ],
            ],
            'serviceContext' => [
                'service' => $psrLogger->getMetadataProvider()->serviceId(),
                'version' => $psrLogger->getMetadataProvider()->versionId(),
            ],
        ], $additionalContext));
    }

    /**
     * Copied from Google\Cloud\ErrorReporting\Bootstrap::getFunctionNameForReport as that function is private
     *
     * Format the function name from a stack trace. This could be a global
     * function (function_name), a class function (Class->function), or a static
     * function (Class::function).
     *
     * @param array $trace The stack trace returned from Exception::getTrace()
     */
    private static function getFunctionNameForReport(array $trace = null)
    {
        if (null === $trace) {
            return '<unknown function>';
        }
        if (empty($trace[0]['function'])) {
            return '<none>';
        }
        $functionName = [$trace[0]['function']];
        if (isset($trace[0]['type'])) {
            $functionName[] = $trace[0]['type'];
        }
        if (isset($trace[0]['class'])) {
            $functionName[] = $trace[0]['class'];
        }
        return implode('', array_reverse($functionName));
    }
}
