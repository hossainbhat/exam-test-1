@extends('layouts.app')
@section('page_style')
@endsection
@section('content')
    <div class="antialiased font-sans">
        <div class="container mx-auto px-4 sm:px-8">
            <div class="py-8">
                <div class="max-w-lg mx-auto bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                    <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-6">Edit Product</h2>

                    <form id="FormData" class="space-y-6">

                        <!-- Product Name -->
                        <div class="relative">
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Product Name
                            </label>
                            <input type="text" id="name" name="name" value="{{ $product->name }}"
                                placeholder="Enter product name"
                                class="peer block w-full border-b-2 border-gray-300 bg-transparent px-0 pt-3 pb-1 text-sm text-gray-900 placeholder-transparent focus:border-blue-600 focus:outline-none dark:border-gray-600 dark:text-white dark:focus:border-blue-400" />
                        </div>

                        <!-- Product Description -->
                        <div>
                            <label for="description"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Description
                            </label>
                            <textarea id="description" name="description" rows="4" placeholder="Enter Description"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:text-white dark:focus:border-blue-400">{{ $product->description }}</textarea>
                        </div>

                        <!-- Categories Multi-select -->
                        <div>
                            <label for="categories" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Select Categories
                            </label>
                            <select id="categories" name="categories[]" multiple
                                class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 sm:text-sm dark:border-gray-600 dark:text-white dark:focus:border-blue-400">
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @if ($product->categories->contains('id', $category->id)) selected @endif>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end">
                            <button type="submit" id="addBtn"
                                class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-6 py-2 rounded-lg shadow focus:outline-none focus:ring-2 focus:ring-blue-400">
                                Update
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('page_script')
    <script type="text/javascript">
        $(document).on('click', '#addBtn', function(e) {
            e.preventDefault();
            var id = {{ $product->id }};
            utlt.asyncFalseRequest('PUT', 'products/' + id, '#FormData', null, 'products');
        });
    </script>
@endsection
