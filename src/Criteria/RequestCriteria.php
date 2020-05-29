<?php

namespace Jiaxincui\Repository\Criteria;

use Illuminate\Http\Request;
use Jiaxincui\Repository\Contracts\CriteriaInterface;
use Jiaxincui\Repository\Contracts\RepositoryInterface;

/**
 * Class RequestCriteria.
 *
 * 对请求的查询字符串解析
 * 在一个请求参数里使用orWhere查询，不同的参数里使用where查询，相当于or和and
 * 如：?where=name:liming;email:like:liming;同一个weher内多个查询使用orWhere()
 * 如：?where[]=name:liming&where[]=email:like:liming，where数组此查询使用where()
 * 还可使用.查询关联
 * 如：?where=account.id_munber:like:611422 使用.分割为关联查询
 * 还可使用in,null,notIn,notNull,between,notBetween,like
 * 如：?where=name:like:lim;email:notnull
 * whereIn使用,分割（1,2,3将转换成whereIn数组参数）
 * 如:?where=id:in:1,2,3
 * slice如:?slice=10,2 从第10条开始取2条数据
 * orderBy如:?orderBy=user_id,desc
 *
 * @package namespace App\Repositories\Criteria;
 */
class RequestCriteria implements CriteriaInterface
{
    /**
     * @var Request
     */
    protected $request, $queryable, $releasable;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Apply criteria in query repository
     *
     * @param string              $model
     * @param RepositoryInterface $repository
     *
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository)
    {
        $request = $this->request->query();
        $this->queryable = $repository->getFieldsQueryable();
        if (isset($request['trashed'])) {
            if ($request['trashed'] === 'only') {
                $model = $model->onlyTrashed();
            }
            if ($request['trashed'] === 'with') {
                $model = $model->withTrashed();
            }
        }
        if (isset($request['with']) && $request['with']) {
            $this->releasable = $repository->getReleasable();
            $with = explode(',', $request['with']);
            $with = array_filter($with, function ($v) {
                return in_array($v, $this->releasable);
            });
            $model = $model->with($with);
        }
        if (isset($request['where']) && is_array($request['where'])) {
            foreach ($request['where'] as $v) {
                $model = $this->applyWhere($model, $v);
            }
        } elseif (isset($request['where']) && is_string($request['where'])) {
            $model = $this->applyWhere($model, $request['where']);
        }

        if ($order = $request['orderBy'] ?? null) {
            $arr = explode(',', $order);
            $by = $arr[0];
            $sort = $arr[1] ?? 'asc';
            $model = $model->orderBy($by, $sort);
        }

        if ($slice = $request['slice'] ?? null) {
            $arr = explode(',', $slice);
            $offset = (int)($arr[0] ?? 0);
            $limit = (int)($arr[1] ?? 0);
            $model = $model->offset($offset < 0 ? 0 : $offset)->limit($limit < 0 ? 0 : $limit);
        }

        return $model;
    }

    protected function parseWhere($data)
    {
        $result = [];
        foreach (explode(';', $data) as $v) {
            $item = explode(':', $v, 3);
            if (count($item) < 2 || !in_array($item[0], $this->queryable)) {
                continue;
            }
            if (count($item) === 2 && !in_array(strtolower($item[1]), ['null', 'notnull'])) {
                $result[] = [$item[0], '=', $item[1]];
            } else {
                $result[] = $item;
            }
        }
        return $result;
    }

    protected function applyWhere($model, $where)
    {
        return $model->where(function ($query) use ($where) {
            $parseWhere = $this->parseWhere($where);
            $first = true;
            foreach ($parseWhere as $or) {
                $relation = null;
                $relation_field = null;
                if(stripos($or[0], '.')) {
                    $explode = explode('.', $or[0]);
                    $relation_field = array_pop($explode);
                    $relation = implode('.', $explode);
                }
                if ($first) {
                    if (!is_null($relation)) {
                        $func = $this->whereQuery($relation_field, $or[1] ?? null, $or[2] ?? null);
                        $query->whereHas($relation, $func);
                        $first = false;
                    } else {
                        $func = $this->whereQuery($or[0], $or[1] ?? null, $or[2] ?? null);
                        $func($query);
                        $first = false;
                    }
                } else {
                    if (!is_null($relation)) {
                        $func = $this->whereQuery($relation_field, $or[1] ?? null, $or[2] ?? null);
                        $query->orWhereHas($relation, $func);
                    } else {
                        $func = $this->orWhereQuery($or[0], $or[1] ?? null, $or[2] ?? null);
                        $func($query);
                    }
                }
            }
        });
    }

    protected function whereQuery($field, $separator, $value)
    {
        return function ($query) use ($field, $separator, $value) {
            switch (strtolower($separator)) {
                case 'in':
                    $query->whereIn($field, explode(',', $value));
                    break;
                case 'notin':
                    $query->whereNotIn($field, explode(',', $value));
                    break;
                case 'between':
                    $query->whereBetween($field, explode(',', $value, 2));
                    break;
                case 'notbetween':
                    $query->whereNotBetween($field, explode(',', $value, 2));
                    break;
                case 'null':
                    $query->whereNull($field);
                    break;
                case 'notnull':
                    $query->whereNotNull($field);
                    break;
                case 'like':
                    $query->where($field, 'like', "%{$value}%");
                    break;
                default:
                    $query->where($field, $separator, $value);
            }
        };
    }

    protected function orWhereQuery($field, $separator, $value)
    {
        return function ($query) use ($field, $separator, $value) {
            switch (strtolower($separator)) {
                case 'in':
                    $query->orWhereIn($field, explode(',', $value));
                    break;
                case 'notin':
                    $query->orWhereNotIn($field, explode(',', $value));
                    break;
                case 'between':
                    $query->orWhereBetween($field, explode(',', $value, 2));
                    break;
                case 'notbetween':
                    $query->orWhereNotBetween($field, explode(',', $value, 2));
                    break;
                case 'null':
                    $query->orWhereNull($field);
                    break;
                case 'notnull':
                    $query->orWhereNotNull($field);
                    break;
                case 'like':
                    $query->orWhere($field, 'like', "%{$value}%");
                    break;
                default:
                    $query->orWhere($field, $separator, $value);
            }
        };
    }
}
