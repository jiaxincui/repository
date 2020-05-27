<?php

namespace Jiaxincui\Repository\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RepositoryMakeCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'make:repository {name} {--model=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new repository interface and class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Repository';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
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
        $interfaceGenerator = new InterfaceGenerator($this->argument('name'), $this->options());
        $eloquentGenerator = new EloquentGenerator($this->argument('name'), $this->options());
        $bindings = new BindingsGenerator($this->argument('name'), $this->options());
        try {
            $interfaceGenerator->handle();
            $this->info('RepositoryInterface Created!');

            $eloquentGenerator->handle();
            $this->info('RepositoryEloquent Created!');

        } catch (FileAlreadyExistsException $e) {
            $this->info($e->getMessage() . ' is already exists!');
            return false;
        } catch (ModelNotFoundException $e) {
            $this->info(' The given model "' . $e->getMessage() . '" is not found!');
        }

        try {
            $bindings->handle();
            $this->info('Binding completed');
        } catch (FileNotFoundException $e) {
            $this->info($e->getMessage() . ' The provider not found, Please run repository:install');
            return  false;
        }
    }
}
