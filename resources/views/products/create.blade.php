@extends('layouts.app')
@section('page_style')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection
@section('content')
    <div class="antialiased font-sans">
        <div class="container mx-auto px-4 sm:px-8">
            <div class="py-8">
                <div class="max-w-lg mx-auto bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                    <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-6">Add New Product</h2>

                    <form id="FormData" class="space-y-6">

                        <!-- Name -->
                        <div class="relative">
                            <input type="text" id="name" name="name" placeholder=" " required
                                class="peer block w-full border-b-2 border-gray-300 bg-transparent px-0 pt-3 pb-1 text-sm text-gray-900 placeholder-transparent focus:border-blue-600 focus:outline-none dark:border-gray-600 dark:text-white dark:focus:border-blue-400" />
                            <label for="name"
                                class="absolute top-1.5 left-0 text-sm text-gray-500 transition-all peer-placeholder-shown:top-3 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-base peer-focus:top-1.5 peer-focus:text-sm peer-focus:text-blue-600 dark:text-gray-400 dark:peer-focus:text-blue-400">
                                Name
                            </label>
                        </div>
                        <div class="mb-5">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                Description <span class="text-red-500">*</span>
                            </label>
                            <textarea id="description" name="description" rows="4" placeholder="Enter Description"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"></textarea>
                        </div>
                        <!-- Parent -->
                        <div class="relative">

                            <div>
                                <label for="categories" class="block text-sm font-medium text-gray-700">Select
                                    Categories</label>
                                <select id="categories" name="categories[]" multiple="multiple"
                                    class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                        </div>

                        <!-- Submit -->
                        <div class="flex justify-end">
                            <button type="submit" id="addBtn"
                                class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-6 py-2 rounded-lg shadow focus:outline-none focus:ring-2 focus:ring-blue-400">
                                Save
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('page_script')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $(document).on('click', '#addBtn', function(e) {
                e.preventDefault();
                utlt.asyncFalseRequest('post', 'products', '#FormData', null, 'products');
            });
        });
    </script>
@endsection
