@extends('layouts.app')
@section('page_style')
    
@endsection
@section('content')
    <!-- component -->
    <div class="antialiased font-sans">
        <div class="container mx-auto px-4 sm:px-8">
            <div class="py-8">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight">Category List</h2>
                </div>

                <div class="-mx-4 sm:-mx-8 px-4 sm:px-8 py-4 overflow-x-auto">
                    <div class="inline-block min-w-full shadow rounded-lg overflow-hidden">
                        <table class="min-w-full leading-normal" id="dataTable">
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
                    filename: 'Category List'
                }],
                columns: [{
                        'title': 'Name',
                        name: 'name',
                        data: "name"
                    },
                    {
                        'title': 'Option',
                        data: 'parent_id',
                        render: function(data, type, row, col) {
                            let returnData = '';

                            returnData += data ? data : 'N/A';

                            return returnData;
                        }
                    },
                    {
                        title: 'Option',
                        data: 'id',
                        class: 'text-right w-[10%]', 
                        width: '10%',
                        render: function(data, type, row, col) {
                            let returnData = '';

                            returnData += `
                                <a href="${utlt.siteUrl('categories/' + data + '/edit')}"
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium text-white bg-blue-600 rounded-lg shadow hover:bg-blue-700 transition">
                                    Edit
                                </a>
                            `;
                            returnData += `
                                    <a href="javascript:void(0);" data-val="${data}" 
                                        class="deleteItem inline-flex items-center px-2 py-1 ml-2 text-xs font-medium text-white bg-red-600 rounded-lg shadow hover:bg-red-700 transition">
                                        Delete
                                    </a>
                                `;
                            return returnData;
                        }
                    }

                ],

                ajax: {
                    url: utlt.siteUrl("categories"),
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
                        utlt.Delete('categories/' + $el.attr('data-val'), '#dataTable');
                    }
                });
            });

        });
    </script>
@endsection
