<?php

return [
    /**
     * Should the entire Stackdriver package be enabled or disabled
     * By default, it is enabled when the app_env is not local
     */
    'enabled' => env('STACKDRIVER_ENABLED', false),

    /**
     * There are multiple way to authenticate in order to send data to Google Stackdriver
     * See: https://github.com/googleapis/google-cloud-php/blob/master/AUTHENTICATION.md
     */
    'credentials' => [
        /**
         * Your Google Cloud Project ID
         * Note that this is a numeric value, and not the string variant
         */
        'projectId' => env('STACKDRIVER_PROJECT_ID', env('GOOGLE_CLOUD_PROJECT')),

        /**
         * The contents of the service account credentials .json file retrieved from the Google Developer's Console
         */
        'keyFile' => [],

        /**
         * The full path to your service account credentials .json file retrieved from the Google Developers Console
         */
        'keyFilePath' => env('STACKDRIVER_KEY_FILE_PATH', ''),
    ],

    'logger' => [
        /**
         * Disable logging
         */
        'enabled' => env('STACKDRIVER_LOGGING_ENABLED', true),

        /**
         * The name of the log to write entries to. Defaults to Laravel app name
         */
        'group_name' => env('STACKDRIVER_LOGGING_GROUP_NAME', env('APP_NAME')),

        /**
         * The monitored resource to associate log entries with. Defaults to type global
         */
        'resource' => env('STACKDRIVER_LOGGING_RESOURCE'),

        /**
         * Add any extra options for the PsrLogger
         * See: http://googleapis.github.io/google-cloud-php/#/docs/cloud-trace/v0.12.0/trace/traceclient
         */
        'clientOptions' => [],

        /**
         * Add any extra options for the PsrLogger
         * See: http://googleapis.github.io/google-cloud-php/#/docs/cloud-logging/v1.14.0/logging/psrlogger
         */
        'psrOptions' => [
            'batchEnabled' => true,
            'batchOptions' => [
                'batchSize' => 50,
                'callPeriod' => 2.0,
                'workerNum' => 1,
            ],
        ],
    ],

    'tracing' => [
        /**
         * Disable tracing alone
         */
        'enabled' => env('STACKDRIVER_TRACING_ENABLED', true),

        /**
         * Add any extra options for the Tracing client here
         * See: https://github.com/googleapis/google-cloud-php-trace/blob/master/src/TraceClient.php
         */
        'clientOptions' => [],

        /**
         * Add any extra options for the Stackdriver
         * See: https://github.com/census-ecosystem/opencensus-php-exporter-stackdriver/blob/master/src/StackdriverExporter.php
         */
        'exporterOptions' => [
            'batchOptions' => [
                'batchSize' => 50,
                'callPeriod' => 2.0,
                'workerNum' => 1,
            ],
        ],

        /**
         * List url path patterns for which you want to disable tracing
         * Useful for keeping third party packages (like Laravel Telescope) from clouding your tracing logs
         */
        'ignored_paths' => [],
    ],

    'error_reporting' => [
        /**
         * Disable error reporting alone
         */
        'enabled' => env('STACKDRIVER_ERROR_REPORTING_ENABLED', true),

        /**
         * Name of the service, defaults to the Laravel app name
         */
        'serviceId' => env('STACKDRIVER_ERROR_REPORTING_SERVICE_ID', env('APP_NAME')),

        /**
         * The version of the current running app
         */
        'versionId' => env('STACKDRIVER_ERROR_REPORTING_VERSION_ID', '1.0.0'),

        /**
         * If you save your app version in cache, set it here. Leave it empty otherwise
         * Note: this setting takes preference over the versionId
         */
        'versionIdCacheKey' => '',

        /**
         * A set of user-defined (key, value) data that provides additional information about the log entries
         */
        'labels' => [],

        /**
         * Add any extra options for the Reporting client here
         * See: http://googleapis.github.io/google-cloud-php/#/docs/google-cloud/v0.86.0/errorreporting/v1beta1/reporterrorsserviceclient?method=__construct
         */
        'clientOptions' => [],

        /**
         * Add any extra options for the PsrLogger
         * See: http://googleapis.github.io/google-cloud-php/#/docs/cloud-logging/v1.14.0/logging/psrlogger
         */
        'psrOptions' => [
            'batchEnabled' => true,
            'batchOptions' => [
                'batchSize' => 50,
                'callPeriod' => 2.0,
                'workerNum' => 1,
            ],
        ],
    ],
];
