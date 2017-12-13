@extends('layouts.auth')
@section('content')
<section id="wrapper" class="new-login-register">
    <div class="new-login-box">
        <div class="white-box">
            @include('layouts.partials.notifications')
            @if (empty(Session::get('success'))) 
            <h3 class="box-title m-b-0" style="font-size: 14px">{{isset($customer_details->company_name)?$customer_details->company_name:''}} Volunteer Registration</h3>
            <small>Enter your details below </small> 
            <form class="form-horizontal new-lg-form" role="form" method="POST" action="{{ route('register') }}">
                {{ csrf_field() }}
                <div class="form-group{{ $errors->has('first_name') ? ' has-error' : '' }} {{ $errors->has('last_name') ? ' has-error' : '' }}">
                    <div class="col-xs-6">
                        {!! Form::text('first_name',old('first_name',request('FirstName')),['class'=>'form-control','placeholder'=>'First Name','id'=>'first_name','required'=>'required','autofocus']) !!}
                        @if (isset($customer_details) && !empty($customer_details))
                            {!! Form::hidden('customer_id', $customer_details->id) !!}
                            {!! Form::hidden('company_name', $customer_details->company_name) !!}
                            {!! Form::hidden('company_number', $customer_details->company_number) !!}
                        @endif
                        @if ($errors->has('first_name'))
                        <span class="help-block">
                            <strong>{{ $errors->first('first_name') }}</strong>
                        </span>
                        @endif
                    </div>
                    <div class="col-xs-6">
                        {!! Form::text('last_name',old('last_name',request('LastName')),['class'=>'form-control','placeholder'=>'Last Name','id'=>'last_name','required'=>'required','autofocus']) !!}
                        @if ($errors->has('last_name'))
                        <span class="help-block">
                            <strong>{{ $errors->first('last_name') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>
                <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                    <div class="col-xs-12">
                        {!! Form::text('email',old('email',request('Email')),['class'=>'form-control','placeholder'=>'Email','id'=>'email','required'=>'required','autofocus']) !!}
                        @if ($errors->has('email'))
                        <span class="help-block">
                            <strong>{{ $errors->first('email') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>

                {{-- 5.2. Remove Password and Confirm Password from this page – it will be on a separate page. --}}
                {{-- <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                    <div class="col-xs-12">
                        <input id="password" type="password" class="form-control"  placeholder="Password" name="password" required>
                        @if ($errors->has('password'))
                        <span class="help-block">
                            <strong>{{ $errors->first('password') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-xs-12">
                        <input id="password-confirm" placeholder="Confirm Password" type="password" class="form-control" name="password_confirmation" required>
                    </div>
                </div> --}}
                
                <div class="form-group{{ $errors->has('home_contact_num') ? ' has-error' : '' }}">
                    <div class="col-xs-6">
                        {!!  Form::select('home_contact_ext', config('plivo.COUNTRY_CODE'), old('home_contact_ext'), ['class'=>'form-control select2']) !!}
                        @if ($errors->has('home_contact_ext'))
                        <span class="help-block">
                            <strong>{{ $errors->first('home_contact_ext') }}</strong>
                        </span>
                        @endif
                    </div>
                    <div class="col-xs-6 {{ $errors->has('home_contact_num') ? ' has-error' : '' }}">
                        {!! Form::text('home_contact_num',old('home_contact_num'),array('class'=>'form-control','id'=>'contact_number','placeholder'=>'Mobile Phone No.','data-mask'=>"999 999-9999",'onfocus' => "cursourChangeFun(this)")) !!}
                        @if ($errors->has('home_contact_num'))
                            <span class="help-block">
                                <strong>{{ $errors->first('home_contact_num') }}</strong>
                            </span>
                        @endif
                    </div>
                </div>
                
                <div class=" form-group ">
                    <div class="col-xs-6">
                        {!! Form::radio('verified_by',1,true,['class'=>'radio-inline email_varification','required'=>'required','id'=>'verified_by',(old('verified_by') && old('verified_by')==1 )?'checked=checked':'']) !!}
                        <strong>Email verification</strong>
                    </div>
                    <div class="col-xs-6">
                        @if (old('verified_by')==2)
                            <input checked="checked" type="radio" name="verified_by" value="2" class="radio-inline mobile_varification" required='required' id='verified_by'>
                        @else
                            <input type="radio" name="verified_by" value="2" class="radio-inline mobile_varification" required='required' id='verified_by'>
                        @endif
                        <strong>Mobile verification</strong>
                    </div>
                </div>

                
                <div class="form-group {{ $errors->has('g-recaptcha-response') ? ' has-error' : '' }}" style="margin-left: 0.5%;">
                    {!! Form::captcha() !!}
                    @if ($errors->has('g-recaptcha-response')) <span class="help-block"> <strong>{{ $errors->first('g-recaptcha-response') }}</strong> </span> @endif
                </div>
                <div class="form-group text-center m-t-20">
                    <div class="col-xs-12">
                        <button class="btn btn-info btn-lg btn-block btn-rounded  text-uppercase waves-effect waves-light" type="submit">submit</button>
                    </div>
                </div>
                <div class="form-group m-b-0">
                    <div class="col-sm-12 text-center">
                        <p>Already have an account? <a href="{{ url((isset($customer_details->company_number))?'login?CompanyNumber='.$customer_details->company_number:'login') }}" class="text-danger m-l-5"><b>Sign In</b></a></a></p>
                    </div>
                </div>
                
               
                
            </form>
            {!! Captcha::script() !!} 
            @else
                <div class="col-md-6">
                    <span class="pull-right"><a title="Go back" href="http://www.theartofelysium.org/" class="btn btn-danger btn-xs btn-block btn-rounded  text-uppercase waves-effect waves-light" ><i class="fa fa-arrow-left" aria-hidden="true"></i></a></span>             
                </div>
            @endif
        </div>
    </div>
</section>
<script src="{{asset('public/plugins/bower_components/jquery/dist/jquery.min.js')}}"></script>
<script type="text/javascript">
    $("#contact_number").keyup(function(){
        var contact_number = $(this).val() || '';
        if (contact_number!='___ ___-____') {
            $(".mobile_varification").prop("checked", true);
        }else{
            $(".email_varification").prop("checked", true);
        }
    });

    $("#contact_number").blur(function(){
        var contact_number = $(this).val() || '';
        if (contact_number.indexOf("_") >= 0) {
            $(".email_varification").prop("checked", true);   
        }
    });
</script>
@endsection