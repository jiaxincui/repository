<?php


namespace Jiaxincui\Repository\Eloquent;


use Illuminate\Container\Container as Application;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Jiaxincui\Repository\Contracts\CriteriaInterface;
use Jiaxincui\Repository\Contracts\RepositoryInterface;
use Jiaxincui\Repository\Exceptions\RepositoryException;

abstract class Repository implements RepositoryInterface
{

    /**
     * @var Application
     */
    protected $app;

    /**
     * @var Model
     */
    protected $model;

    /**
     * @var array
     */
    protected $fieldsQueryable = [];

    /**
     * @var array
     */
    protected $releasable = [];

    /**
     * Collection of Criteria
     *
     * @var Collection
     */
    protected $criteria = [];

    /**
     * @var bool
     */
    protected $skipCriteria = false;

    /**
     * @param Application $app
     * @throws RepositoryException
     * @throws BindingResolutionException
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->makeModel();
        $this->boot();
    }

    /**
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Specify Model class name
     *
     * @return string
     */
    abstract public function model();

    /**
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @return Model|mixed|object
     * @throws BindingResolutionException
     * @throws RepositoryException
     */
    public function makeModel()
    {
        $model = $this->app->make($this->model());

        if (!$model instanceof Model) {
            throw new RepositoryException("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $this->model = $model;
    }

    /**
     * @return array
     */
    public function getReleasable() {
        return $this->releasable;
    }

    /**
     * @return array
     */
    public function getFieldsQueryable()
    {
        return $this->fieldsQueryable;
    }

    /**
     * Get Collection of Criteria
     *
     * @return Collection
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * Push Criteria for filter the query
     *
     * @param $criteria
     *
     * @return $this
     * @throws RepositoryException
     * @throws BindingResolutionException
     */
    public function pushCriteria($criteria)
    {
        if (is_string($criteria) && class_exists($criteria)) {
            $criteria = new $criteria;
        }
        if (!$criteria instanceof CriteriaInterface) {
            throw new RepositoryException("Class " . get_class($criteria) . " must be an instance of Jiaxincui\\Repository\\Contracts\\CriteriaInterface");
        }
        $this->criteria[] = $criteria;

        $this->applyCriteria();
        return $this;
    }

    /**
     * Pop Criteria
     *
     * @param $criteria
     *
     * @return $this
     * @throws BindingResolutionException
     * @throws RepositoryException
     */
    public function popCriteria($criteria)
    {
        if (is_string($criteria) && class_exists($criteria)) {
            $criteria = new $criteria;
        }

        if (!$criteria instanceof CriteriaInterface) {
            throw new RepositoryException("Class " . get_class($criteria) . " must be an instance of Jiaxincui\\Repository\\Contracts\\CriteriaInterface");
        }

        $this->criteria = array_filter($this->criteria, function ($item) use ($criteria) {
            return get_class($item) !== get_class($criteria);
        });

        $this->applyCriteria();
        return $this;
    }

    /**
     * Skip Criteria
     *
     * @param bool $status
     *
     * @return $this
     * @throws BindingResolutionException
     * @throws RepositoryException
     */
    public function skipCriteria($status = true)
    {
        $this->skipCriteria = $status;

        $this->applyCriteria();

        return $this;
    }

    /**
     * Reset all Criteria
     *
     * @return $this
     * @throws BindingResolutionException
     * @throws RepositoryException
     */
    public function resetCriteria()
    {
        $this->criteria = [];

        $this->makeModel();

        return $this;
    }

    /**
     * Apply criteria in current Query
     *
     * @return $this
     * @throws BindingResolutionException
     * @throws RepositoryException
     */
    protected function applyCriteria()
    {
        $this->makeModel();

        if ($this->skipCriteria) {
            return $this;
        }

        $criteria = $this->criteria;

        if ($criteria) {
            foreach ($criteria as $c) {
                if ($c instanceof CriteriaInterface) {
                    $this->model = $c->apply($this->model, $this);
                }
            }
        }

        return $this;
    }

    /**
     * Trigger method calls to the model
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return call_user_func_array([$this->model, $method], $arguments);
    }
}