@extends('layouts.butchery_master')

@section('content-header')
<div class="container">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0"> {{ $title }} |<small> Asset Movement </small></h1>
        </div><!-- /.col -->
    </div><!-- /.row -->
</div><!-- /.container-fluid -->

@endsection

@section('content')
<form id="form-save-scale3" class="form-prevent-multiple-submits"
    action="{{ route('beef_slicing_save') }}" method="post">
    @csrf
    <div class="card-group">
        <div class="card">
            <div class="card-body">
                <h5><strong>To:</strong></h5>
                <div class="form-group text-center">
                    <div class="row">
                        <div class="col-md-12">
                            <label for="exampleInputPassword1"> FA List</label>
                            <select class="form-control select2" name="fa" id="fa_select" required>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row form-group text-center">
                    <div class="col-md-12">
                        <div class="form-group" id="product_type_select">
                            <label for="exampleInputPassword1"> Move To Dept List</label>
                            <select class="form-control select2" name="to_dept" id="to_dept_select" required>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row form-group text-center">
                    <div class="col-md-12">
                        <div class="form-group" id="product_type_select">
                            <label for="exampleInputPassword1"> Move To User List</label>
                            <select class="form-control select2" name="to_user" id="to_user_select" required>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h5><strong>From:</strong></h5>
                <div class="row form-group text-center">
                    <div class="col-md-12">
                        <label for="exampleInputPassword1"> Move From User List</label>
                        <select class="form-control select2" name="from_user" id="from_user_select" required>
                        </select>
                    </div>
                </div>
                <div class="row form-group text-center">
                    <div class="col-md-12">
                        <label for="exampleInputPassword1"> Move From Dept List</label>
                        <select class="form-control select2" name="from_dept" id="from_dept_select" required>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h5><strong>Authentication:</strong></h5>
                <div class="row form-group text-center">
                    <div class="col-md-12">
                        <label for="exampleInputPassword1"> Receiving Username</label>
                        <input type="text" id="receipt_user" value="" class="form-control" required>
                    </div>
                </div>
                <div class="row form-group text-center">
                    <div class="col-md-8">
                        <label for="exampleInputPassword1"> Receiving User Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password"
                                placeholder="Enter your password" aria-describedby="password-toggle">
                            <div class="input-group-append">
                                <button class="btn btn-secondary" type="button" id="password-toggle">
                                    <i class="fa fa-eye" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4" style="padding-top: 7%">
                        <button type="button" id="validate_user_btn" class="btn btn-info">Validate</button>
                    </div>
                </div>
                <div class="col-md-4">
                    <span class="text-danger" id="err"></span>
                    <span class="text-success" id="succ"></span>
                </div>
                <input type="hidden" id="auth_val" value="0">
                <div class="row form-group text-center" style="padding-top: 5%; padding-left: 40%">
                    <button type="submit" onclick="return validateOnSubmit()" disabled
                        class="btn btn-primary btn-lg btn-prevent-multiple-submits"><i
                            class="fa fa-paper-plane single-click" aria-hidden="true"></i> Save</button>
                </div>
            </div>
        </div>
    </div>
    <div id="loading" class="collapse">
        <div class="row d-flex justify-content-center">
            <div class="spinner-border" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
    </div>
</form>
<br>

<div class="div">
    <button class="btn btn-primary " data-toggle="collapse" data-target="#slicing_output_show"><i
            class="fa fa-plus"></i>
        Output
    </button>
</div>

<div id="slicing_output_show" class="collapse">
    <hr>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"> Movements today | <span id="subtext-h1-title"><small> entries
                                ordered by
                                latest</small> </span></h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="hidden" hidden>{{ $i = 1 }}</div>
                    <div class="table-responsive">
                        <table id="example1" class="table table-striped table-bordered table-hover" width="100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Code </th>
                                    <th>product </th>
                                    <th>Product Type</th>
                                    <th>Production Process</th>
                                    <th>Total Crates</th>
                                    <th>Black Crates</th>
                                    <th>Scale Weight(kgs)</th>
                                    <th>Total Tare</th>
                                    <th>Net Weight(kgs)</th>
                                    <th>Total Pieces</th>
                                    <th>Prod Date</th>
                                    <th>Created Date </th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>#</th>
                                    <th>Code </th>
                                    <th>product </th>
                                    <th>Product Type</th>
                                    <th>Production Process</th>
                                    <th>Total Crates</th>
                                    <th>Black Crates</th>
                                    <th>Scale Weight(kgs)</th>
                                    <th>Total Tare</th>
                                    <th>Net Weight(kgs)</th>
                                    <th>Total Pieces</th>
                                    <th>Prod Date</th>
                                    <th>Created Date </th>
                                </tr>
                            </tfoot>
                            <tbody>
                                {{-- @foreach($entries as $data)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                <td>{{ $data->item_code }}</td>
                                <td>{{ $data->description }}</td>
                                @if($data->product_type == 1)
                                    <td>Main</td>
                                @elseif($data->product_type == 2)
                                    <td>By-Product</td>
                                @else
                                    <td>Intake</td>
                                @endif
                                <td>{{ $data->process }}</td>
                                <td>{{ $data->no_of_crates }}</td>
                                <td>{{ $data->black_crates }}</td>
                                <td>{{ $data->scale_reading }}</td>
                                <td>{{ number_format(($data->no_of_crates * 1.8) + ($data->black_crates * 0.2), 2) }}
                                </td>
                                <td>{{ number_format($data->net_weight, 2) }}</td>
                                <td>{{ $data->no_of_pieces }}</td>
                                <td>{{ \Carbon\Carbon::parse($data->production_date)->format('d/m/Y') }}
                                </td>
                                <td>{{ \Carbon\Carbon::parse($data->created_at)->format('d/m/Y H:i') }}
                                </td>
                                </tr>
                                @endforeach--}}
                            </tbody>
                        </table>
                    </div>
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
        fetchData()

        $('.form-prevent-multiple-submits').on('submit', function () {
            $(".btn-prevent-multiple-submits").attr('disabled', true);
        });

        let isMouseDown = false;
        let passwordInput = $("#password");
        let passwordToggleBtn = $("#password-toggle");

        passwordToggleBtn.mousedown(function () {
            // Change the input type to "text" when the button is pressed
            passwordInput.attr("type", "text");
            isMouseDown = true;
        });

        $(document).mouseup(function () {
            if (isMouseDown) {
                // Change the input type back to "password" when the mouse is released
                passwordInput.attr("type", "password");
                isMouseDown = false;
            }
        });

        // $('#auth_val').on('change', function (e) {
        //     e.preventDefault()
        //     alert('value changed')
        //     checkValidUserValue()          
        // });

        $('#validate_user_btn').on('click', function (e) {
            e.preventDefault()
            checkParams()          
        });
    });

    const setUserMessage = (field_succ, field_err, message_succ, message_err) => {
        document.getElementById(field_succ).innerHTML = message_succ
        document.getElementById(field_err).innerHTML = message_err
    }

    const checkValidUserValue = () => {
        let auth_val = $('#auth_val').val()

        // Check if the value not zero and enable it
        if (auth_val != 0) {
            submitButton.disabled = false; // enable the button
        }
    }

    const checkParams = () => {
        let username = $('#receipt_user').val()
        let password = $('#password').val()

        if (username == '' || password == '') {
            setUserMessage('succ', 'err', '', 'please enter both username & Password')
        } else {
            setUserMessage('succ', 'err', '', '')
            validateUser(username, password)
        }
    }

    const setUserValidity = (status) => {
        $("#auth_val").val(status);
    }

    const validateUser = (username, password) => {
        const url = "/asset/validate-user"

        $('#validate_user_btn').addClass('disabled')
        setUserMessage('succ', 'err', 'Validating user...', '')

        const request_data = {
            username: 'FARMERSCHOICE\\' + username,
            password: password
        }

        axios.post(url, request_data)
            .then((res) => {
                if (res) {
                    const obj = JSON.parse(res.data)
                    if (obj.success == true) {
                        setUserMessage('succ', 'err', 'validated receiver', '')
                        setUserValidity(1)
                        checkValidUserValue()
                    } else {
                        setUserMessage('succ', 'err', '', 'Wrong credentials')
                        setUserValidity(0)
                    }

                } else {
                    setUserMessage('succ', 'err', '', 'No response from login Api service. Contact IT')
                }

                $('#validate_user_btn').removeClass('disabled')

                // if (response.data == '0100') {
                //     // console.log('Pin verification Successful')
                //     setUserMessage('succ', 'err', 'Pin verification Successful..fiscalising', '')
                //     createInvoice();
                // } else if (response.data == '' || response.data == undefined) {
                //     // console.log('No response from device')
                //     setUserMessage('succ', 'err', '', 'No response from device')
                //     $('#validate_user_btn').removeClass('disabled')
                // } else {
                //     // console.log('Pin verification error: ' + response.data)
                //     setUserMessage('succ', 'err', '', 'Pin verification error: ' + response.data)
                //     $('#validate_user_btn').removeClass('disabled')
                // }
            })
            .catch((error) => {
                console.log(error);
                setUserMessage('succ', 'err', '', error)
                $('#validate_user_btn').removeClass('disabled')
            })
    }

    const validateOnSubmit = () => {
        $valid = true;

        var net = $('#net').val();
        var product_type = $('#product_type').val();
        var no_of_pieces = $('#no_of_pieces').val();
        var process = $('#production_process').val();
        var process_substring = process.substr(0, process.indexOf(' '));

        if (net == "" || net <= 0.00) {
            $valid = false;
            alert("Please ensure you have valid netweight.");
        }

        //check main product pieces
        if (product_type == 'Main Product' && no_of_pieces < 1 && process_substring == 'Debone') {
            $valid = false;
            alert("Please ensure you have inputed no_of_pieces,\nThe item is a main product in deboning process");
        }
        return $valid;
    }

    const fetchData = () => {
        $('#loading').collapse('show');
        axios.get('/asset/fetch-data')
            .then(function (response) {
                $('#loading').collapse('hide');
                let faSelect = document.getElementById('fa_select');
                let toDeptSelect = document.getElementById('to_dept_select');
                let fromDeptSelect = document.getElementById('from_dept_select');
                let toUserSelect = document.getElementById('to_user_select');
                let fromUserSelect = document.getElementById('from_user_select');

                // Create an object to keep track of unique values
                let uniqueValues = {};

                // Clear existing options and add an empty option
                faSelect.innerHTML = '<option value="">Select an option</option>';
                toDeptSelect.innerHTML = '<option value="">Select an option</option>';
                fromDeptSelect.innerHTML = '<option value="">Select an option</option>';
                toUserSelect.innerHTML = '<option value="">Select an option</option>';
                fromUserSelect.innerHTML = '<option value="">Select an option</option>';

                // Append options from Axios response
                response.data.forEach(function (item) {
                    appendOption(faSelect, item.No_, item.No_ + ' ' + item.Description);

                    // Check if the value is unique
                    if (!uniqueValues.hasOwnProperty(item.Location_code)) {
                        appendOption(toDeptSelect, item.Location_code, item.LocationName);
                        uniqueValues[item.Location_code] = true;

                        appendOption(fromDeptSelect, item.Location_code, item.LocationName);
                        uniqueValues[item.Location_code] = true;
                    }
                    if (!uniqueValues.hasOwnProperty(item.Responsible_employee)) {
                        appendOption(toUserSelect, item.Responsible_employee, item
                            .Responsible_employee);
                        uniqueValues[item.Responsible_employee] = true;

                        appendOption(fromUserSelect, item.Responsible_employee, item
                            .Responsible_employee);
                        uniqueValues[item.Responsible_employee] = true;
                    }
                });
            })
            .catch(function (error) {
                console.error(error);
            });
    }

    const appendOption = (selectElement, value, text) => {
        var option = document.createElement('option');
        option.value = value;
        option.text = text;
        selectElement.appendChild(option);
    }

</script>
@endsection
