<?php

namespace App\Http\Controllers\Category;

use App\Category;
use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CategoryTransactionController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Category $category)
    {
        $transitions = $category->products()
            ->whereHas('transitions')
            ->with('transitions')
            ->get()
            ->pluck('transitions')
            ->collapse();
        return $this->showAll($transitions);
    }
}
