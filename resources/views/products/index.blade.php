@extends('layouts.app')
@section('page_style')
@endsection
@section('content')
    <!-- component -->
    <div class="antialiased font-sans">
        <div class="container mx-auto px-4 sm:px-8">
            <div class="py-8">
                <div class="flex flex-row justify-between items-center">
                    <h2 class="text-2xl font-semibold leading-tight">Product List</h2>
                    <a href="{{ route('products.create') }}"><button type="button"
                            class="text-white bg-gray-800 hover:bg-gray-900 focus:outline-none focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-gray-800 dark:hover:bg-gray-700 dark:focus:ring-gray-700 dark:border-gray-700">Add
                            New</button></a>
                </div>

                <div class="-mx-4 sm:-mx-8 px-4 sm:px-8 py-4 overflow-x-auto">
                    <div class="inline-block min-w-full shadow rounded-lg overflow-hidden">
                        <table id="dataTable" class="min-w-full divide-y divide-gray-200 text-sm text-left">
                            <thead class="bg-gray-100 text-gray-700">
                                <tr>
                                    <th class="px-4 py-3 font-semibold tracking-wide">
                                        <span class="flex items-center">
                                            Name
                                            <svg class="w-4 h-4 ms-1" xmlns="http://www.w3.org/2000/svg" fill="none"
                                                viewBox="0 0 24 24">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                            </svg>
                                        </span>
                                    </th>
                                    <th class="px-4 py-3 font-semibold tracking-wide">
                                        <span class="flex items-center">
                                            Parent
                                            <svg class="w-4 h-4 ms-1" xmlns="http://www.w3.org/2000/svg" fill="none"
                                                viewBox="0 0 24 24">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                            </svg>
                                        </span>
                                    </th>
                                    <th class="px-4 py-3 font-semibold tracking-wide text-right">Option</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">

                            </tbody>
                        </table>


                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('page_script')
    <script type="text/javascript">
        $(document).ready(function() {
            var dataTable = $('#dataTable').DataTable({
                dom: '<"row"<"col-12 col-sm-6"l><"col-12 col-sm-6"f>><"row"<"col-12 col-sm-12"tr><"col-12 col-sm-6"i><"col-12 col-sm-6"p>>',
                lengthMenu: [
                    [10, 20, 50, -1],
                    [10, 20, 50, "All"]
                ],
                buttons: [{
                    extend: 'excel',
                    text: "Excel Export",
                    attr: {
                        class: 'btn btn-info btn-sm'
                    },
                    exportOptions: {
                        columns: [0, 1, 2]
                    },
                    filename: 'Product List'
                }],
                columns: [{
                        'title': 'Name',
                        name: 'name',
                        data: "name"
                    },
                    {
                        'title': 'Description',
                        name: 'description',
                        data: 'description',
                    },
                    {
                        'title': 'Option',
                        data: 'id',
                        class: 'text-right width-5-per',
                        width: '10%',
                        render: function(data, type, row, col) {
                            let returnData = '';

                            returnData += '<a href="' + utlt.siteUrl('products/' + data +
                                    '/edit') +
                                '"><i class="fa-solid fa-pen-to-square"></i></a> ';

                            returnData += '<a href="javascript:void(0);" data-val="' + data +
                                '" class="deleteItem"><i class="fa-solid fa-trash"></i></a>';

                            return returnData;
                        }
                    },
                ],

                ajax: {
                    url: utlt.siteUrl("products"),
                },

                language: {
                    paginate: {
                        next: '&#8594;', // or '→'
                        previous: '&#8592;' // or '←'
                    }
                },
                columnDefs: [{
                    searchable: false,
                    orderable: false,
                    targets: [1, 2]
                }],
                responsive: true,
                autoWidth: false,
                serverSide: true,
                processing: true,
            });

            //delete
            $(document).on('click', '.deleteItem', function() {
                var $el = $(this);
                let config = Object.assign(utlt.swalConfig);
                Swal.fire(config).then(function(result) {
                    if (result.value === true) {
                        utlt.Delete('products/' + $el.attr('data-val'), '#dataTable');
                    }
                });
            });

        });
    </script>
@endsection
