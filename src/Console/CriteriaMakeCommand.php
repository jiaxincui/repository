<?php

namespace Jiaxincui\Repository\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class CriteriaMakeCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'make:criteria {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new criteria class';

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

        $criteriaGenerator = new CriteriaGenerator($this->argument('name'), $this->options());
        try {
            $criteriaGenerator->handle();
            $this->info('Criteria class created successfully!');
        } catch (FileAlreadyExistsException $e) {
            $this->info($e->getMessage() . ' is already exists!');
            return false;
        }
    }
}
