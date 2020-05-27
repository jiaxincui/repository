<?php
namespace Jiaxincui\Repository;

use Illuminate\Support\ServiceProvider;
use Jiaxincui\Repository\Console\CriteriaMakeCommand;
use Jiaxincui\Repository\Console\InstallCommand;
use Jiaxincui\Repository\Console\RepositoryMakeCommand;

class RepositoryServiceProvider extends ServiceProvider
{

    /**
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/repository.php' => config_path('repository.php')
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                RepositoryMakeCommand::class,
                CriteriaMakeCommand::class
            ]);
        }
    }


    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        if (class_exists('App\Providers\RepositoryServiceProvider')) {
            $this->app->register(\App\Providers\RepositoryServiceProvider::class);
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
