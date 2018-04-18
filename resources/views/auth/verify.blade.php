@extends('layouts.auth')
@section('content')
<section id="wrapper" class="new-login-register">
    <div class="new-login-box">
        <div class="white-box">
            @include('layouts.partials.notifications')
            <h3 class="box-title m-b-0">{{isset($customer_details->company_name)?$customer_details->company_name:'The Art of Elysium'}} volunteer user verification </h3>
            <small>Enter your details below</small>
            <form class="form-horizontal new-lg-form" role="form" id="loginform" method="POST" action="{{ url("register/verify/$user->id") }}">
                {{ csrf_field() }}
                <div class="form-group {{ $errors->has('f_name') ? ' has-error' : '' }} m-t-20">
                    <div class="col-xs-4 m-t-10 text-right">
                        <label>First Name</label>
                    </div>
                    <div class="col-xs-8">
                        <input placeholder="First name" id="f_name" type="text" class="form-control" name="f_name" value="{{ old('f_name',$user->f_name) }}" disabled autofocus>
                        @if ($errors->has('f_name'))
                        <span class="help-block">
                            <strong>{{ $errors->first('f_name') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>
                <div class="form-group {{ $errors->has('l_name') ? ' has-error' : '' }} m-t-20">
                    <div class="col-xs-4 m-t-10 text-right">
                        <label>Last Name</label>
                    </div>
                    <div class="col-xs-8">
                        <input placeholder="Last name" id="l_name" type="text" class="form-control" name="l_name" value="{{ old('l_name',$user->l_name) }}" disabled autofocus>
                        @if ($errors->has('l_name'))
                        <span class="help-block">
                            <strong>{{ $errors->first('l_name') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>
                
                <div class="form-group {{ $errors->has('email') ? ' has-error' : '' }} m-t-20">
                    <div class="col-xs-4 m-t-10 text-right">
                        <label>Email Address</label>
                    </div>
                    <div class="col-xs-8">
                        <input placeholder="Email address" id="email" type="text" class="form-control" name="l_name" value="{{ old('email',$user->email) }}" disabled autofocus>
                        @if ($errors->has('email'))
                        <span class="help-block">
                            <strong>{{ $errors->first('email') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>
                
                <div class="form-group {{ $errors->has('username') ? ' has-error' : '' }} m-t-20">
                    <div class="col-xs-4 m-t-10 text-right">
                        <label>Username</label>
                    </div>
                    <div class="col-xs-8">
                        <input placeholder="Username" id="username" type="text" class="form-control" name="username" value="{{ old('username',!empty($user->username) ? $user->username : $user->email) }}" required autofocus>
                        @if ($errors->has('username'))
                        <span class="help-block">
                            <strong>{{ $errors->first('username') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>
                <div class="form-group {{ $errors->has('password') ? ' has-error' : '' }}">
                    <div class="col-xs-4 m-t-10 text-right">
                        <label>Password</label>
                    </div>
                    <div class="col-xs-8">
                        <input placeholder="Password" id="password" type="password" class="form-control" name="password" required>
                        @if ($errors->has('password'))
                        <span class="help-block">
                            <strong>{{ $errors->first('password') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-xs-4 text-right">
                        <label>Confirm Password</label>
                    </div>
                    <div class="col-xs-8">
                        <input placeholder="Confirm Password" id="password" type="password" class="form-control" name="password_confirmation" required>
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