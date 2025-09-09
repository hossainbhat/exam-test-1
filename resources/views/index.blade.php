@extends('layouts.app')
@section('content')
    <div class="antialiased font-sans">
        <div class="container mx-auto px-4 sm:px-8">
            <div class="py-8">
                <ul
                    class="flex flex-wrap text-sm font-medium text-center text-gray-500 border-b border-gray-200 dark:border-gray-700 dark:text-gray-400">

                    <li class="me-2">
                        <a href="{{ route('categories.index') }}"
                            class="inline-block p-4 rounded-t-lg hover:text-gray-600 hover:bg-gray-50 dark:hover:bg-gray-800 dark:hover:text-gray-300">Category
                            List</a>
                    </li>
                    <li class="me-2">
                        <a href="{{ route('products.index') }}"
                            class="inline-block p-4 rounded-t-lg hover:text-gray-600 hover:bg-gray-50 dark:hover:bg-gray-800 dark:hover:text-gray-300">Product
                            List</a>
                    </li>
                </ul>
                <div class="container mx-auto px-4 sm:px-8 py-8">
                    <h2 class="text-2xl font-semibold mb-6 text-gray-800 dark:text-white">Categories & Products</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($categories as $category)
                            <div
                                class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg shadow p-4">
                                <!-- Category Name -->
                                <h3 class="text-lg font-semibold mb-3 text-gray-700 dark:text-gray-200">
                                    {{ $category->name }}</h3>

                                @if ($category->products->isEmpty())
                                    <p class="text-sm text-gray-500 dark:text-gray-400">No products available.</p>
                                @else
                                    <ul class="text-sm text-gray-900 dark:text-white space-y-2">
                                        @foreach ($category->products as $product)
                                            <li class="px-3 py-2 border rounded hover:bg-blue-50 dark:hover:bg-gray-700">
                                                {{ $product->name }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
