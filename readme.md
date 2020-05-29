## About

适用于 Laravel 的 Repository 包

提供Criteria以及请求参数查询

## 安装

```terminal
composer require jiaxincui/repository
```

## 部分示例

```terminal
php artisan repository:install
```

```terminal
php artisan make:repository User --model="App\Models\User"
```

```terminal
php artisan make:criteria Example
```

### 请求参数查询

提供了一个默认的 Criteria，它可以解析请求字符串并生成查询。

所有的查询字段需要在设置为白名单的情况下才会生效，所以你不用担心数据的安全。

```php
<?php

class UserRepositoryEloquent extends Repository implements UserRepository
{
    // 可以生效的查询字段，关联字段用 . 分割
    protected $fieldsQueryable = [
        'name',
        'age',
        'role.name'
    ];

    // 可以生效的关联查询
    protected $releasable = ['book', 'order'];


    public function model()
    {
        return User::class;
    }

   // 应用请求参数查询
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}

```

请求示例：

`/api/user?where=name:liming`

`/api/user?where=age:>:18`

#### 使用

使用`where`作为请求参数的健，可使用`;`分割多个条件，约束为`OR`查询。

`/api/user?where=name:like:ming;name:=:zhangsan`

要使用 `AND` 约束查询，需要提供一个`where`数组。

`/api/user?where[]=name:like:liming&wehre[]=age:>:18`

可以使用的查询有`where`,`whereIn`,`whereNotIn`,`whereNull`, 'whereNotNull', 'whereBetween'

下表列出了如何使用方法和示例

| 字符 | 方法 | 说明 | 示例 |
-|-|-|-
| in | whereIn() | 数组元素使用`,`分割 | `/api/user?where=name:in:zhang,li,wang` |
| notin | whereNotIn() | 数组元素使用`,`分割 | `/api/user?where=name:notin:zhang,li,wang` |
| null | whereNull() | | `/api/user?where=name:null` |
| notnull | whereNotNull() | | `/api/user?where=name:notnull`  |
| between | whereBetween() | 数组元素使用`,`分割 | `/api/user?where=age:between:18,23`  |
| notbetween | whereNotBetween() | 数组元素使用`,`分割 | `/api/user?where=age:notbetween:18,23` |
| like | where() | 作为第二个参数传入 | `/api/user?where=name:like:zhang` |
| <>= | where() | 作为第二个参数传入 | `/api/user?where=age:>:18` |

同时还可以使用`whereHas()`方法,如：

查询角色名称为 admin 的用户。

`/api/user?where=role.name:=:admin`

#### 排序

使用`orderBy`作为请求参数的键

如: `/api/user?where=age:>:18&orderBy=user_id,desc`

#### 限制结果集

使用`slice`作为请求参数的键

从第2条开始取5条数据

`/api/user?where=age:>:18&slice=2,5`

#### 关联查询

使用`with`作为请求参数的键

使用 , 分割多个关联

`/api/user?where=age:>:18&with=role,book`

#### 软删除

使用 `trashed` 作为请求参数的键，可以应用软删除作用域
如：

仅列出已经软删除的项目

`/api/user?trashed=only`

包含软删除项目

`/api/user?trashed=with`




更多示例正在准备中...

## License

[MIT](https://github.com/jiaxincui/repository/blob/master/LICENSE.md) © [JiaxinCui](https://github.com/jiaxincui)

