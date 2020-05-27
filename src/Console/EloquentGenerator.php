<?php


namespace Jiaxincui\Repository\Console;

use Illuminate\Database\Eloquent\ModelNotFoundException;

class EloquentGenerator extends Generator
{

    protected $originalName;

    public function __construct(string $name, array $options = [])
    {
        $this->originalName = $name;
        $name = $this->qualifyName($name);
        parent::__construct($name, $options);
    }

    protected function getStub()
    {
        return __DIR__.'/stubs/eloquent.stub';
    }

    protected function qualifyName($name)
    {
        $name = trim($name, '\\/') . 'RepositoryEloquent';
        return trim(config('repository.generator.paths.repositories', 'Repositories/Eloquent'), '/') . '/' . $name;
    }

    protected function getReplaces()
    {
        $replaces = [];

        $replaces['className'] = $this->className;
        $replaces['namespace'] = $this->namespace;
        $replaces['interface'] = $this->getInterfaceGenerator()->getClassName();
        $replaces['interface_use'] = $this->getInterfaceGenerator()->getClass();
        $replaces['model_use'] = $this->getModel();
        $replaces['model_class'] = basename(str_replace('\\', '/', $replaces['model_use']));;

        return $replaces;
    }

    protected function getModel()
    {
        if ($model = isset($this->options['model']) ? str_replace('/', '\\', trim($this->options['model'], '\\/')) : null) {
            if (class_exists($model)) {
                return $model;
            }
            throw new ModelNotFoundException($model);
        }

        return 'Jiaxincui\\Repository\\Models\\Example';
    }

    protected function getInterfaceGenerator()
    {
        return new InterfaceGenerator($this->originalName, $this->options);
    }
}
