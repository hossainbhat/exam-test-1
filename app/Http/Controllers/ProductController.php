<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            $products = new Product();
            $limit = 10;
            $offset = 0;
            $search = [];
            $where = [];
            $with = [];
            $join = [];
            $orderBy = [];

            if ($request->input('length')) {
                $limit = $request->input('length');
            }

            if ($request->input('order')[0]['column'] != 0) {
                $column_name = $request->input('columns')[$request->input('order')[0]['column']]['name'];
                $sort = $request->input('order')[0]['dir'];
                $orderBy[$column_name] = $sort;
            }

            if ($request->input('start')) {
                $offset = $request->input('start');
            }

            if ($request->input('search') && $request->input('search')['value'] != "") {
                $search['name'] = $request->input('search')['value'];
            }

            if ($request->input('where')) {
                $where = $request->input('where');
            }

            $products = $products->getDataForDataTable($limit, $offset, $search, $where, $with, $join, $orderBy,  $request->all());
            return response()->json($products);
        }
        return view('products.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data['categories'] = Category::all();
        return view('products.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
        ]);
        DB::beginTransaction();
        try {
            $data = [
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description
            ];
            $product = Product::create($data);

            if ($request->has('categories')) {
                $product->categories()->attach($request->categories);
            }
            DB::commit();
            return sendSuccess('Successfully created !');
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $data['categories'] = Category::all();
        $data['product'] = $product->load('categories');
        return view('products.edit', $data);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
        ]);
        DB::beginTransaction();
        try {
            $data = [
                'name' => $request->name,
                'description' => $request->description
            ];
            $product->update($data);

            if ($request->has('categories')) {
                $product->categories()->sync($request->categories);
            } else {
                $product->categories()->sync([]);
            }

            DB::commit();
            return sendSuccess('Successfully Update !');
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        try {
            $product->categories()->detach();
            $product->delete();
            return sendMessage('Successfully Delete');
        } catch (\Exception $e) {
            return sendError($e->getMessage());
        }
    }
}
