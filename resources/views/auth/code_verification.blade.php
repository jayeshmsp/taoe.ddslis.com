@extends('layouts.auth')
@section('content')
<section id="wrapper" class="new-login-register">
    <div class="new-login-box">
        <div class="white-box">
            @include('layouts.partials.notifications')
            <h3 class="box-title m-b-0"> {{isset($customer_details->company_name)?$customer_details->company_name:''}} OTP Code verification </h3>
            <small>Enter your details below</small>
            <form class="form-horizontal new-lg-form" role="form" id="loginform" method="GET" action="{{ url("register/verify/token") }}">
                {{ csrf_field() }}
                 <div class="form-group {{ $errors->has('token') ? ' has-error' : '' }} m-t-20">
                    <div class="col-xs-12">
                        @if (isset($customer_details) && !empty($customer_details))
                            {!! Form::hidden('customer_id', $customer_details->id) !!}
                            {!! Form::hidden('company_name', $customer_details->company_name) !!}
                            {!! Form::hidden('company_number', $customer_details->company_number) !!}
                        @endif
                        <input placeholder="Verification code" id="token" type="text" class="form-control" name="token" value="" autofocus>
                        @if ($errors->has('token'))
                        <span class="help-block">
                            <strong>{{ $errors->first('token') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>
                
                <div class="form-group pull-right"> 
                    <div class="col-xs-12">
                        <label>For, Resend Activation Token Click <a class="text-left" href='{{url(isset($customer_details->company_number)?"resend-activation-token/$user_id?CompanyNumber=$customer_details->company_number":"resend-activation-token/$user_id")}}'>Here</a></label>
                        <label>Send Email Activation Link<a class="text-left" href='{{url(isset($customer_details->company_number)?"resend-activation-email/$user_id?CompanyNumber=$customer_details->company_number":"resend-activation-email/$user_id")}}'> Here</a></label>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 5px;">
                    <div class="form-group text-center m-t-20">
                        <div class="col-xs-12">
                            <button class="btn btn-info btn-lg btn-block btn-rounded text-uppercase waves-effect waves-light" type="submit">Submit</button>
                        </div>
                    </div>
                </div>
                 <div class="row">
                    <div class="col-md-12 col-xs-12 m-t-10">
                        <a href="{{ url('login') }}" class="text-primary pull-right"><b>Sign In</b></a> 
                    </div>
                </div>
                </form>
            </div>
        </div>
    </section>
@endsection