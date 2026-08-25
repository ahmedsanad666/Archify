<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class OrderedScope implements Scope
{
    /**
     * Apply default ordering by the `order` column.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $builder->orderBy($model->getTable().'.order');
    }
}
