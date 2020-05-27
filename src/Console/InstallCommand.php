<?php

namespace Jiaxincui\Repository\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;

class InstallCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'repository:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the commands necessary to prepare Repository for use';

    protected $files;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->files = new Filesystem();
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws FileNotFoundException
     */
    public function handle()
    {
        Artisan::call('vendor:publish', ['--provider' => 'Jiaxincui\\Repository\\RepositoryServiceProvider']);

        $this->info('config file copied successfully');

        $providerPath = $this->getProviderPath();

        if ($this->files->exists($providerPath)) {
            $this->info('The service provider already exists!');
        } else {
            Artisan::call('make:provider RepositoryServiceProvider');
            $provider = $this->files->get($providerPath);
            $provider = substr_replace($provider, Bindings::BIND_PLACEHOLDER, strpos($provider, '//', strpos($provider, 'register()')), 2);
            $this->files->put($providerPath, $provider);
            $this->info('Provider "App\Providers\RepositoryServiceProvider" created successfully!');
        }
    }

    protected function getProviderPath()
    {
        return app()->path() . '/Providers/RepositoryServiceProvider.php';
    }
}
