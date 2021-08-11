@extends('layouts.sausage_master')

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Sausage Production Registry | showing {{ $title }}</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <div class="hidden" hidden>{{ $i = 1 }}</div>
                <div class="table-responsive">
                    <table id="example1" class="table table-bordered table-hover" style="scroll-behavior: smooth;">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Barcode</th>
                                <th>Item Code </th>
                                <th>Item description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Barcode</th>
                                <th>Item Code </th>
                                <th>Item description</th>
                                <th>Count</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($entries as $data)
                            <tr>
                                <td>{{ $i++ }}</td>
                                <td>{{ $data->barcode }}</td>
                                <td>{{ $data->code }}</td>
                                <td>{{ $data->description }}</td>
                                <td>{{ $data->total_count }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
    </div>
    <!-- /.col -->
</div>
@endsection

