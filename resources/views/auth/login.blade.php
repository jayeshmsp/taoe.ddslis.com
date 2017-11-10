@extends('layouts.auth')
@section('content')
<section id="wrapper" class="new-login-register">
    <div class="new-login-box"> 
        <div class="white-box">
            @include('layouts.partials.notifications')
            @if ($message = Session::get('status'))
                <div class="alert alert-success alert-block">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <h4>Success!</h4>
                    {{ $message }}
                </div>
            @endif
            <h3 class="box-title m-b-0"> {{isset($customer_details->company_name)?$customer_details->company_name:''}} Volunteer Login</h3>
            <small>Enter your details below</small>
            <form class="form-horizontal new-lg-form" role="form" id="loginform" method="POST" action="{{ route('login') }}">
                {{ csrf_field() }}
                <div class="form-group {{ $errors->has('username') ? ' has-error' : '' }} m-t-20">
                    <div class="col-xs-12">
                        <input placeholder="Username" id="username" type="text" class="form-control" name="username" value="{{ old('username') }}" required autofocus>
                        @if (isset($customer_details) && !empty($customer_details) )
                            {!! Form::hidden('customer_id', $customer_details->id) !!}
                            {!! Form::hidden('company_number', $customer_details->company_number) !!}
                        @endif

                        @if ($errors->has('username'))
                        <span class="help-block">
                            <strong>{{ $errors->first('username') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>
                <div class="form-group {{ $errors->has('password') ? ' has-error' : '' }}">
                    <div class="col-xs-12">
                        <input placeholder="Enter password" id="password"  class="form-control" name="password" required type="password">
                        @if ($errors->has('password'))
                        <span class="help-block">
                            <strong>{{ $errors->first('password') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>
                {{-- <div class="form-group {{ $errors->has('last_name') ? ' has-error' : '' }}">
                    <div class="col-xs-12">
                        <input placeholder="Last Name" id="last_name" type="text" class="form-control" name="last_name" required>
                        @if ($errors->has('last_name'))
                        <span class="help-block">
                            <strong>{{ $errors->first('last_name') }}</strong>
                        </span>
                        @endif
                    </div>
                </div> --}}
                {{-- 
                <div class="form-group {{ $errors->has('g-recaptcha-response') ? ' has-error' : '' }}" style="margin-left: 0.5%;">
                    {!! Form::captcha() !!}
                    @if ($errors->has('g-recaptcha-response')) <span class="help-block"> <strong>{{ $errors->first('g-recaptcha-response') }}</strong> </span> @endif
                </div> --}}
                <div class="form-group">
                    <div class="col-md-12">
                        {{-- <div class="pull-left p-t-0">
                            <label for="checkbox-signup"><a href="{{ url('manageMailChimp') }}"> MailChimp Demo </a> </label>
                        </div> --}}
                        
                    </div>
                    <div class=" text-center m-t-20">
                        <div class="row">
                        <div class="col-xs-12">
                            <button class="btn btn-info btn-lg btn-block btn-rounded text-uppercase waves-effect waves-light" type="submit">Log In</button>
                        </div>
                        </div>
                    </div>
                     <div>
                     <div class="row">
                    <div class="col-md-12 col-xs-12 m-t-10">
                        {{-- <div class="pull-left p-t-0">
                            <label for="checkbox-signup"><a href="{{ url('manageMailChimp') }}"> MailChimp Demo </a> </label>
                        </div> --}}
                        @if (isset($customer_details->company_number))
                            <a href="{{ url('password/reset?CompanyNumber='.$customer_details->company_number) }}" class="text-dark pull-right"><i class="fa fa-lock m-r-5"></i> Forgot password?</a> </div>
                        @else
                            <a href="{{ url('password/reset') }}" class="text-dark pull-right"><i class="fa fa-lock m-r-5"></i> Forgot password?</a> </div>
                        @endif
                    </div>
                    </div>
                    {{-- 
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12 m-t-10 text-center">
                            <div class="social">
                                <a href="auth/facebook" class="btn  btn-facebook" data-toggle="tooltip"  title="Login with Facebook">
                                    <i aria-hidden="true" class="fa fa-facebook"></i>
                                </a>
                                <a href="auth/google" class="btn btn-googleplus" data-toggle="tooltip"  title="Login with Google">
                                    <i aria-hidden="true" class="fa fa-google-plus"></i>
                                </a>
                                <a href="auth/twitter" class="btn btn-twitter" data-toggle="tooltip"  title="Login with Twitter">
                                    <i aria-hidden="true" class="fa fa-twitter"></i>
                                </a>
                                <a href="auth/linkedin" class="btn btn-linkedin" data-toggle="tooltip"  title="Login with Linkedin">
                                    <i aria-hidden="true" class="fa fa-linkedin"></i>
                                </a>
                                <a href="custom-auth/pinterest" class="btn btn-pinterest" data-toggle="tooltip"  title="Login with Pinterest">
                                    <i aria-hidden="true" class="fa fa-pinterest"></i>
                                </a>
                            </div>
                        </div>
                    </div> --}}
                    <div class="form-group m-b-0 m-t-5">
                        <div class="col-sm-12 text-center">
                            <p>Don't have an account? <a href="{{ url((isset($customer_details->company_number))?'register?CompanyNumber='.$customer_details->company_number:'register') }}" class="text-primary m-l-5"><b>Sign Up</b></a></p>
                        </div>
                    </div>
                </form>
                {!! Captcha::script() !!}
            </div>
        </div>
    </section>
@endsection