<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function index()
    {
        $categories = Category::with('products')->get();
        return view('index',compact('categories'));
    }
}
