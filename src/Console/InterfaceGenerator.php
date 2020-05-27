<?php


namespace Jiaxincui\Repository\Console;

class InterfaceGenerator extends Generator
{

    public function __construct(string $name, array $options = [])
    {
        $name = $this->qualifyName($name);
        parent::__construct($name, $options);
    }

    protected function getStub()
    {
        return __DIR__.'/stubs/interface.stub';
    }

    protected function qualifyName($name)
    {
        $name = trim($name, '\\/') . 'Repository';
        return trim(config('repository.generator.paths.interfaces', 'Repositories'), '/') . '/' . $name;
    }

    protected function getReplaces()
    {
        $replaces = [];

        $replaces['className'] = $this->className;
        $replaces['namespace'] = $this->namespace;

        return $replaces;
    }
}
