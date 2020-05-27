<?php


namespace Jiaxincui\Repository\Console;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

/**
 * Class Generator
 * @package Jiaxincui\Repository\Console
 */
abstract class Generator
{
    /**
     * FileSystem.
     *
     * @var Filesystem
     */
    protected $files;

    /**
     * @var string
     */
    protected $name;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * 待生成包含命名空间的类名
     *
     * @var string
     */
    protected $class;

    /**
     * 待生成类名
     *
     * @var string
     */
    protected $className;

    /**
     * 命名空间
     *
     * @var string
     */
    protected $namespace;

    /**
     * 待生成文件完全路径
     *
     * @var string
     */
    protected $path;

    /**
     * @var array
     */
    protected $options;

    public function __construct(string $name, array $options = [])
    {
        $this->files = new Filesystem();
        $this->name = $name;
        $this->options = $options;
        $this->init();
    }

    protected function init()
    {
        $this->class = $this->qualifyClass($this->name);
        $this->className = basename(str_replace('\\', '/', $this->class));
        $this->namespace = $this->qualifyNamespace($this->class);
        $this->path = $this->qualifyPath($this->class);
    }

    abstract protected function getStub();

    abstract protected function getReplaces();

    /**
     * Execute the console command.
     *
     * @return void
     *
     * @throws FileAlreadyExistsException
     * @throws FileNotFoundException
     */
    public function handle()
    {
        $path = $this->path;

        // First we will check to see if the class already exists. If it does, we don't want
        // to create the class and overwrite the user's code. So, we will bail out so the
        // code is untouched. Otherwise, we will continue generating this class' files.
        if ($this->alreadyExists($path)) {
           throw new FileAlreadyExistsException($path);
        }

        // Next, we will generate the path to the location where this class' file should get
        // written. Then, we will build the class and make the proper replacements on the
        // stub files so that it gets the correctly formatted namespace and class name.
        $this->makeDirectory($path);

        $this->files->put($path, $this->sortImports($this->buildClass()));
    }

    /**
     * Parse the class name and format according to the root namespace.
     *
     * @param  string  $name
     * @return string
     */
    protected function qualifyClass($name)
    {
        $name = ltrim($name, '\\/');

        $rootNamespace = $this->rootNamespace();

        if (Str::startsWith($name, $rootNamespace)) {
            return $name;
        }

        $name = str_replace('/', '\\', $name);

        return $this->qualifyClass(
            $this->getDefaultNamespace(trim($rootNamespace, '\\')).'\\'.$name
        );
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace;
    }

    /**
     * Determine if the class already exists.
     *
     * @param $path
     * @return bool
     */
    protected function alreadyExists($path)
    {
        return $this->files->exists($path);
    }

    /**
     * Get the destination class path.
     *
     * @param $class
     * @return string
     */
    protected function qualifyPath($class)
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $class);

        return app()->path().'/'.str_replace('\\', '/', $name).'.php';
    }

    /**
     * Build the directory for the class if necessary.
     *
     * @param  string  $path
     * @return string
     */
    protected function makeDirectory($path)
    {
        if (! $this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        }

        return $path;
    }

    /**
     * Build the class with the given name.
     *
     * @return string
     *
     * @throws FileNotFoundException
     */
    protected function buildClass()
    {
        $stub = $this->files->get($this->getStub());

        foreach ($this->getReplaces() as $search => $replace) {
            $stub = str_replace('$' . strtoupper($search) . '$', $replace, $stub);
        }
        return $stub;
    }

    /**
     * Get the full namespace for a given class, without the class name.
     *
     * @param $class
     * @return string
     */
    protected function qualifyNamespace($class)
    {
        return trim(implode('\\', array_slice(explode('\\', $class), 0, -1)), '\\');
    }

    /**
     * Alphabetically sorts the imports for the given stub.
     *
     * @param  string  $stub
     * @return string
     */
    protected function sortImports($stub)
    {
        if (preg_match('/(?P<imports>(?:use [^;]+;$\n?)+)/m', $stub, $match)) {
            $imports = explode("\n", trim($match['imports']));

            sort($imports);

            return str_replace(trim($match['imports']), implode("\n", $imports), $stub);
        }

        return $stub;
    }

    /**
     * Get the root namespace for the class.
     *
     * @return string
     */
    protected function rootNamespace()
    {
        return app()->getNamespace();
    }
}
