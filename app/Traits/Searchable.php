<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait Searchable
{

    protected static int $perPage = 10;

    protected static function whereQuery(Builder $query, Request $request, string $param, array $allowed): Builder
    {
        if ($request->filled($param) && in_array($request->query($param), $allowed, true)) {
            $query->where($param, $request->query($param));
        }
        return $query;
    }

    protected static function limitThePages(Builder $query, Request $request): int
    {
        if ($request->filled('page')) {
            $currentPage = (int) $request->query('page');
            $lastPage =  ceil($query->count() / self::$perPage);
            if ($currentPage < 1 || $currentPage > $lastPage) {
                return 1;
            }
            return $currentPage;
        }
    }
}
