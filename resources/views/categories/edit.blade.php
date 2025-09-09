@extends('layouts.app')
@section('page_style')
@endsection
@section('content')
    <div class="antialiased font-sans">
        <div class="container mx-auto px-4 sm:px-8">
            <div class="py-8">
                <div class="max-w-lg mx-auto bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                    <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-6">Edit Category</h2>

                    <form id="FormData" class="space-y-6">

                        <!-- Name -->
                        <div class="relative">
                            <input type="text" value="{{ $product->name }}" id="name" name="name" placeholder=" "
                                required
                                class="peer block w-full border-b-2 border-gray-300 bg-transparent px-0 pt-3 pb-1 text-sm text-gray-900 placeholder-transparent focus:border-blue-600 focus:outline-none dark:border-gray-600 dark:text-white dark:focus:border-blue-400" />

                        </div>

                        <!-- Parent -->
                        <div class="relative">
                            <select id="parent_id" name="parent_id"
                                class="peer block w-full border-b-2 border-gray-300 bg-transparent px-0 pt-3 pb-1 text-sm text-gray-900 focus:border-blue-600 focus:outline-none dark:border-gray-600 dark:text-white dark:focus:border-blue-400">
                                
                                @foreach ($categories as $category)
                                    <option  @if ($category->parent_id == $category->id) @selected(true) @endif value="{{ $category->id }}">
                                        {{ $category->name }}</option>
                                @endforeach
                            </select>

                        </div>

                        <!-- Submit -->
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
            var id = {{ $category->id }};
            utlt.asyncFalseRequest('PUT', 'categories/' + id, '#FormData', null, 'categories');
        });
    </script>
@endsection
