@extends('layouts.butchery_master')

@section('content-header')
<div class="container">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0"> {{ $title }} |<small> Deboned Data</small></h1>
        </div><!-- /.col -->
    </div><!-- /.row -->
</div><!-- /.container-fluid -->

@endsection

@section('content')
<form id="form-save-scale3" action="{{ route('butchery_scale3_save') }}" method="post">
    @csrf
    <div class="card-group">
        <div class="card">
            <div class="card-body " style="">
                <div class="form-group">
                    <label for="exampleInputPassword1"> Product Name</label>
                    <select class="form-control select2" name="product" id="product" required>
                        <option value="">Select product</option>
                        @foreach($products as $product)
                            <option value="{{ $product->code }}">
                                {{ ucwords($product->description) }} - {{ $product->code }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group" id="product_type_select">
                            <label for="exampleInputPassword1">Product Type</label>
                            <input type="text" class="form-control" id="product_type" value="" name="product_type">
                        </div>
                    </div>
                    <div class="col-md-4" style="padding-top: 7.5%">
                        <button class="btn btn-outline-info btn-sm form-control" id="btn_product_type" type="button" data-toggle="modal"
                            disabled>
                            <strong>Edit?</strong>
                        </button>
                    </div>
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">Production Process</label>
                    <select class="form-control select2" name="process_type" id="process_type" required>
                        <option value="" selected disabled>Select type</option>
                        @foreach($processes as $type)
                            <option value="{{$type->process_code}}">{{$type->process}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="padding-left: 30%;">
                    <button type="button" onclick="getWeightAjaxApi()" id="weigh" value="COM4"
                        class="btn btn-primary btn-lg"><i class="fas fa-balance-scale"></i> Weigh</button> <br><br>
                    <small>Reading from <input type="text" id="comport_value" value="COM4" style="border:none"
                            disabled></small>
                </div>
            </div>
        </div>
        <div class="card ">
            <div class="card-body text-center">
                <div class="form-group">
                    <label for="exampleInputPassword1">No. of Crates</label>
                    <select class="form-control" name="no_of_crates" id="no_of_crates" required>
                        <option value="" selected disabled>select no. of crates</option>
                        <option>2</option>
                        <option>3</option>
                        <option>4</option>
                        <option>5</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="exampleInputEmail1">Reading</label>
                    <input type="number" step="0.01" class="form-control" id="reading" name="reading" value="0.00"
                        oninput="getNet()" placeholder="" readonly>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="manual_weight">
                    <label class="form-check-label" for="manual_weight">Enter Manual weight</label>
                </div>
            </div>
        </div>

        <div class="card ">
            <div class="card-body text-center">
                <div class="form-group">
                    <label for="exampleInputPassword1">Scale Tare-Weight</label>
                    <input type="number" class="form-control" id="tareweight" name="tareweight" value="{{ number_format($configs[0]->tareweight, 2) }}" readonly>
                    <input type="hidden" class="form-control " id="default_tareweight"
                                value="{{ number_format($configs[0]->tareweight, 2) }}" >
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">Net</label>
                    <input type="number" class="form-control" id="net" name="net" value="0.00" step=".01" placeholder="" readonly>
                </div>
                <div class="form-group" style="padding-top: 10%">
                    <button type="submit" onclick="return checkNetOnSubmit()" class="btn btn-primary btn-lg"><i class="fa fa-paper-plane"
                            aria-hidden="true"></i> Save</button>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- product type modal -->
<div class="modal fade" id="productTypesModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="form-edit-scale" action="#" method="post">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Edit Product Type</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class=" form-group">
                        <select name="edit_product_type" id="edit_product_type" class="form-control select2" required
                            autofocus>
                            @foreach($product_types as $type)
                                <option value="{{$type->code}}" selected="selected">
                                    {{ ucwords($type->description) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" type="button" onclick="setProductCode()">
                        <i class="fa fa-save"></i> Edit
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- end product code modal -->
<hr>

<div class="div">
    <button class="btn btn-primary " data-toggle="collapse" data-target="#slicing_output_show"><i
            class="fa fa-plus"></i>
        Output
    </button>
</div>

<div id="slicing_output_show" class="collapse"><br><br>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"> Scale 3 Deboned output data | <span id="subtext-h1-title"><small> entries ordered by
                                latest</small> </span></h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="hidden" hidden>{{ $i = 1 }}</div>
                    <table id="example1" class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Code </th>
                                <th>product </th>
                                <th>Weight(kgs)</th>
                                <th>Date </th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Code </th>
                                <th>product </th>
                                <th>Weight(kgs)</th>
                                <th>Date </th>
                            </tr>
                        </tfoot>
                        <tbody>
                        @foreach($deboning_data as $data)
                            <tr>
                                <td>{{ $i++ }}</td>
                                <td> {{ $data->item_code }}</td>
                                <td>{{ $helpers->getProductName($data->item_code) }}</td>
                                <td> {{ number_format($data->net_weight, 2) }}</td>
                                <td> {{ $data->created_at }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
            <!-- /.col -->
        </div>
    </div>
</div>
<!-- slicing ouput data show -->

@endsection

@section('scripts')
<script>
    $(document).ready(function () {

        $("body").on("click", "#btn_product_type", function (a) {
            a.preventDefault();

            var product = $('#product_type').val();
            var code = 1;
            if (product == "By Product") {
                code = 2;
            }
            $('#edit_product_type').val(code);

            $('#productTypesModal').modal('show');
        });

        $('#product').change(function(){
            var product_code = $('#product').val();
            var product_type = document.getElementById('product_type');
            if (product_code != '') {
                $.ajax({
                    type: "GET",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: "{{ url('product_type_ajax') }}",
                    data: {
                        'product_code': product_code,

                    },
                    dataType: 'JSON',
                    success: function (res) {
                        if (res) {
                            // console.log(res.product_type);
                            $('#btn_product_type').prop('disabled',false);

                            if(res.product_type == 1){
                                $('#product_type').val("Main Product");
                            } else if(res.product_type == 2){
                                $('#product_type').val("By Product");
                            }

                        }
                    }
                });

            }

        });

        $('#no_of_crates').change(function () {
            var number_of_crates = $(this).val();
            var default_tareweight = $('#default_tareweight').val();

            var new_tareweight = (number_of_crates) * (default_tareweight);
            $("#tareweight").val(Math.round((new_tareweight + Number.EPSILON) * 100) / 100);

            getNet();

        });

        $('#manual_weight').change(function () {
            var manual_weight = document.getElementById('manual_weight');
            var reading = document.getElementById('reading');
            if (manual_weight.checked == true) {
                reading.readOnly = false;
                reading.focus();
                $('#reading').val("");

            } else {
                reading.readOnly = true;

            }

        });
    });

    function setProductCode(){
        var edit_product_type = $('#edit_product_type').val();
        if (edit_product_type == null) {
            alert('please select item')
            return 1;
        }
        else if (edit_product_type == 1) {
            $('#product_type').val("Main Product");
        }
        else if (edit_product_type == 2) {
            $('#product_type').val("By Product");
        }
        $('#productTypesModal').modal('hide');
        return 1;

    }

    function getNet() {
        var reading = document.getElementById('reading').value;
        var tareweight = document.getElementById('tareweight').value;
        var net = document.getElementById('net');
        net.value = parseFloat(reading) - parseFloat(tareweight);
    }

    function checkNetOnSubmit(){
        var net = $('#net').val();
        $valid = true;
        if (net == "" || net <= 0.00 ) {
            $valid = false;
            alert("Please ensure you have valid netweight.");
        };
        return $valid;
    }

    function getWeightAjaxApi() {
        var ComPortID = document.getElementById('weigh').value;
        if (ComPortID) {
            alert('comport ' + ComPortID + 'is available');
            $.ajax({
                type: "GET",
                url: "{{ url('api/get-centres') }}?route_id=" + routeID,
                success: function (res) {
                    if (res) {
                        $("#centre").empty();
                        // $("#centre").append('<option>Select</option>');
                        $.each(res, function (key, value) {
                            $("#centre").append($("<option></option>").attr("value", value.id)
                                .text(value.centre_name));
                        });

                    } else {
                        $("#reading").empty();
                    }
                }
            });

        } else {
            // $("#reading").value = 0.00;
            alert('comport ' + ComPortID + 'is not available');
        }
    }

</script>
@endsection
