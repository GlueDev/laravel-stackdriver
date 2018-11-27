<?php


namespace GlueDev\Laravel\Stackdriver;

use Illuminate\Support\ServiceProvider;

class StackdriverServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }

        $sd = new Stackdriver($this->app);
        $sd->startTracing();
        $sd->listenForLogs();
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/stackdriver.php', 'stackdriver');

        $this->app->singleton('Stackdriver\Logger', function () {
            return Stackdriver::createLogger();
        });

        $this->app->singleton('Stackdriver\Exporter', function () {
            return Stackdriver::createExporter();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['stackdriver'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__ . '/../config/stackdriver.php' => config_path('stackdriver.php'),
        ], 'stackdriver.config');
    }
}
