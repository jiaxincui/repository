<?php


namespace Jiaxincui\Repository\Console;

class CriteriaGenerator extends Generator
{

    public function __construct(string $name, array $options = [])
    {
        $name = $this->qualifyName($name);
        parent::__construct($name, $options);
    }

    protected function getStub()
    {
        return __DIR__.'/stubs/criteria.stub';
    }

    protected function qualifyName($name)
    {
        $name = trim($name, '\\/') . 'Criteria';
        return trim(config('repository.generator.paths.criteria', 'Repositories/Criteria'), '/') . '/' . $name;
    }

    protected function getReplaces()
    {
        $replaces = [];

        $replaces['className'] = $this->className;
        $replaces['namespace'] = $this->namespace;

        return $replaces;
    }
}
