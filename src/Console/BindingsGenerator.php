<?php

namespace Jiaxincui\Repository\Console;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;

class BindingsGenerator
{
    protected $files;

    protected $name;

    protected $options;

    public function __construct(string $name, array $options = [])
    {
        $this->files = new Filesystem();
        $this->name = $name;
        $this->options = $options;
    }

    /**
     * @throws FileNotFoundException
     */
    public function handle()
    {
        $path = $this->getProviderPath();
        $provider = $this->files->get($path);
        $provider = $this->bindReplace($provider);
        $this->files->put($path, $provider);
    }

    protected function bindReplace($provider)
    {
        $eloquent = $this->getEloquent();
        $interface = $this->getInterface();
        $provider = str_replace(Bindings::BIND_PLACEHOLDER, "\$this->app->bind(\\{$interface}::class, \\{$eloquent}::class);" . PHP_EOL . '        ' . Bindings::BIND_PLACEHOLDER, $provider);
        return $provider;
    }

    protected function getProviderPath()
    {
        return app()->path() . '/Providers/RepositoryServiceProvider.php';
    }
    protected function getEloquent()
    {
        return (new EloquentGenerator($this->name, $this->options))->getClass();
    }

    protected function getInterface()
    {
        return (new InterfaceGenerator($this->name, $this->options))->getClass();
    }
}
