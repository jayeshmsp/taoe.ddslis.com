@extends('layouts.auth')
@section('content')
<div class="container">
    <section id="wrapper" class="new-login-register">
        <div class="new-login-box">
            <div class="white-box">
                @include('layouts.partials.notifications')
                @if (session('status'))
                <div class="alert alert-success alert-dismissable">
                    {{ session('status') }}
                </div>
                <div class="col-md-6">
                    <span class="pull-right"><a title="Go back" href="{{url('/')}}" class="btn btn-danger btn-xs btn-block btn-rounded  text-uppercase waves-effect waves-light" ><i class="fa fa-arrow-left" aria-hidden="true"></i></a></span>             
                </div>
                @else
                    <h3 class="box-title m-b-0">Reset Password</h3>
                    <small>Enter your details below</small>
                    <form class="form-horizontal new-lg-form" role="form" method="POST" action="{{ route('password.email') }}">
                        {{ csrf_field() }}
                        <div class="form-group{{ $errors->has('username') ? ' has-error' : '' }}">
                            <div class="col-xs-12">
                                <input id="username" placeholder="Username" type="text" class="form-control" name="username" value="{{ old('username') }}" required>
                                @if ($errors->has('username'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('username') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>
                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <div class="col-xs-12">
                                <input id="email" placeholder="E-Mail Address" type="email" class="form-control" name="email" value="{{ old('email') }}" required>
                                @if ($errors->has('email'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('email') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>
                        <div class=" form-group ">
                            <div class="col-xs-6">
                                {!! Form::radio('verified_by',1,true,['class'=>'radio-inline','required'=>'required','id'=>'verified_by',(old('verified_by') && old('verified_by')==1 )?'checked=checked':'']) !!}
                                <strong>Send Email</strong>
                            </div>
                            <div class="col-xs-6">
                                @if (old('verified_by')==2)
                                    {!! Form::radio('verified_by',2,['class'=>'radio-inline','required'=>'required','id'=>'verified_by',(old('verified_by') && old('verified_by')==2 )?'checked=checked':'']) !!}
                                @else
                                    {!! Form::radio('verified_by',2,['class'=>'radio-inline','required'=>'required','id'=>'verified_by']) !!}
                                @endif                                
                                
                                <strong>Send SMS</strong>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-xs-12">
                                <button class="btn btn-info btn-lg btn-block btn-rounded text-uppercase waves-effect waves-light" type="submit">Send Password Reset Link</button>
                            </div>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </section>
@endsection