@extends('layouts.slaughter_master')

@section('content-header')
<div class="container">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0"> {{ $title }}<small></small></h1>
        </div><!-- /.col -->
    </div><!-- /.row -->
</div><!-- /.container-fluid -->

@endsection

@section('content')
<form>
    <div class="card-group">
        <div class="card">
            <div class="card-body" style="padding-top: 50%; padding-left: 20%">
                <button type="submit" class="btn btn-primary btn-lg">Weigh</button> <br> <br>
                <small>Reading from <input type="text" id="comport_value" value="COM4" style="border:none" disabled></small>
            </div>
        </div>
        <div class="card ">
            <div class="card-body text-center">
                <div class="form-group">
                    <label for="exampleInputEmail1">Reading</label>
                    <input type="number" step="0.01" class="form-control" id="reading" name="reading" value="0.00" oninput="getNet()"  placeholder="" readonly>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="manual_weight">
                    <label class="form-check-label" for="manual_weight">Enter Manual weight</label>
                </div> <br>
                <div class="form-group">
                    <label for="exampleInputPassword1">Tare-Weight</label>
                    <input type="number" class="form-control" id="tareweight" name="tareweight" value="2.4" readonly>
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">Net</label>
                    <input type="number" class="form-control" id="net" value="" step="0.01" placeholder="" readonly>
                </div>
            </div>
        </div>
        <div class="card ">
            <div class="card-body text-center">
                <div class="form-group">
                    <label for="exampleInputPassword1">Receipt No.</label>
                    <select class="form-control select2" name="receipt_no" id="receipt_no" required>
                        <option value="" selected disabled>select</option>
                        <option>option 1</option>
                        <option>option 2</option>
                        <option>option 3</option>
                        <option>option 4</option>
                        <option>option 5</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">Slapmark</label>
                    <select class="form-control select2" name="slapmark" id="slapmark" required>
                        <option value="" selected disabled>select</option>
                        <option>option 1</option>
                        <option>option 2</option>
                        <option>option 3</option>
                        <option>option 4</option>
                        <option>option 5</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">Carcass Type</label>
                    <select class="form-control select2" name="carcass_type" id="carcass_type" required>
                        <option value="" selected disabled>select</option>
                        <option>option 1</option>
                        <option>option 2</option>
                        <option>option 3</option>
                        <option>option 4</option>
                        <option>option 5</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">Vendor Number</label>
                    <input type="text" class="form-control" name="vendor_no" id="vendor_no" placeholder="" readonly>
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">Vendor Name</label>
                    <input type="text" class="form-control" name="vendor_name" id="vendor_name" placeholder="" readonly>
                </div>
            </div>
        </div>
        <div class="card ">
            <div class="card-body text-center">
                <div class="form-group">
                    <label for="exampleInputPassword1">Meat %</label>
                    <input type="number" class="form-control" id="meat_percent" name="meat_percent" placeholder=""
                        readonly>
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">Classification Code</label>
                    <input type="text" class="form-control" id="tareweight" placeholder="" readonly>
                </div>
                <div class="form-group" style="padding-top: 20%">
                    <button type="submit" class="btn btn-primary btn-lg"><i class="fa fa-paper-plane"
                        aria-hidden="true"></i> Save</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
    $(document).ready(function () {

        $('#carcass_type').change(function () {
            var carcass_type = $(this).val();
            var meat_percent = document.getElementById('meat_percent');
            if (carcass_type.value == '') {
                meat_percent.readOnly = true;
            } else {
                meat_percent.readOnly = false;
            }
        });

        $('#manual_weight').change(function(){
            var manual_weight = document.getElementById('manual_weight');
            var reading = document.getElementById('reading');
            if (manual_weight.checked == true) {
                reading.readOnly = false;

            } else {
                reading.readOnly = true;

            }

        });
    });

    function getNet(){
        var reading = document.getElementById('reading').value;
        var tareweight = document.getElementById('tareweight').value;
        var net = document.getElementById('net');
        net.value = parseFloat(reading) - parseFloat(tareweight);
    }

</script>
@endsection
