<?php

namespace App\Http\Controllers\Auth;

use Mail;
use DB;
use App\User;
use Illuminate\Http\Request;
use App\Mail\EmailVerification;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Auth;
use Form;
use View;
use App\Repositories\SettingRepo;
Use Plivo;
use App\Helpers\EloquentHelper;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    protected $setting_details;
    protected $EloquentHelper;
    public function __construct()
    {
        parent::__construct();
        $SettingRepo = new SettingRepo;
        $this->EloquentHelper = new EloquentHelper();
        $this->setting_details = $SettingRepo->getBy(array('single'=>true));
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $rules = [
            'first_name' => 'required|string|alpha_space|max:255',
            'last_name' => 'required|string|alpha_space|max:255',
            //'email' => 'required|string|email|max:255|is_user_exist:users,name,'.$data['first_name']." ".$data['last_name'],
            'email' => 'required|string|email|max:255',
            
            'verified_by' => 'required|verify_choosen_method:'.$data['email'].",".$data['home_contact_num'],
            'g-recaptcha-response' => 'required|captcha',
        ];
        
        $msg = [
            'email.unique'=> "This contact is already registered."
        ];
        return Validator::make($data, $rules,$msg);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        $token = $this->generate_token($data['verified_by']);

        $user =  User::create([
            'name' => $data['first_name'].' '.$data['last_name'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => isset($data['email'])?$data['email']:'',
            'username' => '',
            'home_contact_ext' => $data['home_contact_ext'],
            'verified_by' => $data['verified_by'],
            'platform' => isset($data['company_name'])?$data['company_name']:'art-of-elysium',
            'home_contact_num' => $data['home_contact_num'],
            'verified' => '0',
            'customer_id' => isset($data['customer_id'])?$data['customer_id']:'0',
            'email_token' =>  $token,
        ]);
        $user->attachRole('2');
        return $user;
    }

    /**
    *  Over-ridden the register method from the "RegistersUsers" trait
    *  Remember to take care while upgrading laravel
    */
    public function register(Request $request)
    {
        $inputs = $request->all();

        $inputs['home_contact_num'] = str_replace(' ', '', $inputs['home_contact_num']);
        $inputs['home_contact_num'] = str_replace('-', '', $inputs['home_contact_num']);
        
        // Laravel validation
        $validator = $this->validator($request->all());
        if ($validator->fails()) 
        {   
            $back_url = isset($inputs['company_number'])?'register?CompanyNumber='.$inputs['company_number']:'register';
            return redirect($back_url)
                        ->withErrors($validator)
                        ->withInput();
            //$this->throwValidationException($request, $validator);
        }

        $validator1 = Validator::make($inputs, ['email'=>'email_name_username_validation']);
        if ($validator1->fails()) 
        {   
            $back_url = isset($inputs['company_number'])?'login?CompanyNumber='.$inputs['company_number']:'login';
            return redirect($back_url)
                        ->with('error','User already exists with same details, Please login')
                        ->withErrors($validator1)
                        ->withInput();
        }

        $user_exists= User::where('first_name','=',$request->get('first_name'))
                            ->where('last_name','=',$request->get('last_name'))
                            ->where('email','=',$request->get('email'))
                            ->first();
        
        $securityToken = $this->EloquentHelper->generateSecurityToken();

        if ( !empty($user_exists->username) && !empty($user_exists) && !empty($user_exists->contact_id) && strtolower($user_exists->status) == 'completed' ) {
            
            $salesforce_dashboard_url = str_replace('[CONTACT_ID]', $user_exists->contact_id, $this->setting_details->salesforce_dashboard_url);
            $salesforce_dashboard_url = str_replace('[UID]', $user_exists->id, $salesforce_dashboard_url);
            $salesforce_dashboard_url = str_replace('[FNAME]', $user_exists->first_name, $salesforce_dashboard_url);
            $salesforce_dashboard_url = str_replace('[LNAME]', $user_exists->last_name, $salesforce_dashboard_url);
            $salesforce_dashboard_url = str_replace('[EMAIL]', $user_exists->email, $salesforce_dashboard_url);
            $salesforce_dashboard_url = ($user_exists->verified_by==2)?str_replace('[PHONE]', $user_exists->home_contact_num, $salesforce_dashboard_url):str_replace('[PHONE]', '', $salesforce_dashboard_url);
            
            $salesforce_dashboard_url = $salesforce_dashboard_url.'&DAIS_tag='.$securityToken;

            return redirect($salesforce_dashboard_url);   
        }elseif (!empty($user_exists->username) && !empty($user_exists)) {
            //$salesforce_application_page_url = str_replace('[CONTACT_ID]', $user_exists->contact_id, $this->setting_details->salesforce_application_page_url);
            $salesforce_application_page_url = str_replace('&id=[CONTACT_ID]', '', $this->setting_details->salesforce_application_page_url);
            $salesforce_application_page_url = str_replace('[FNAME]', $user_exists->first_name, $salesforce_application_page_url);
            $salesforce_application_page_url = str_replace('[LNAME]', $user_exists->last_name, $salesforce_application_page_url);
            $salesforce_application_page_url = str_replace('[EMAIL]', $user_exists->email, $salesforce_application_page_url);
            $salesforce_application_page_url = str_replace('[UID]', $user_exists->id, $salesforce_application_page_url);
            $salesforce_application_page_url = ($user_exists->verified_by==2)?str_replace('[PHONE]', $user_exists->home_contact_num, $salesforce_application_page_url):str_replace('[PHONE]', '', $salesforce_application_page_url);

            $salesforce_application_page_url = $salesforce_application_page_url.'&DAIS_tag='.$securityToken;

            return redirect($salesforce_application_page_url);

        }else{
            $user = $this->create($inputs);
            if($request['verified_by'] == 2) {
                $phone_ext = !empty($request['home_contact_ext'])?filter_var($request['home_contact_ext'], FILTER_SANITIZE_NUMBER_INT):'';
                $this->send_sms($phone_ext.$request['home_contact_num'],$user->email_token, $user->first_name);
                
                $url = 'code_verification/'.base64_encode($user->id);
                if ($request->has('company_number')) {
                    $url = 'code_verification/'.base64_encode($user->id).'?CompanyNumber='.$request->get('company_number');
                }
                return redirect($url);
            } else {
                /*$user_ary = ['email_token' => $user->email_token, 'name' => $user->name];
                if ($request->has('company_number')) {
                    $user_ary['company_number'] = $request->company_number;
                }*/
                $email = new EmailVerification($inputs,new User(['email_token' => $user->email_token, 'name' => $user->name]));
                Mail::to($user->email)->send($email);
            }
            $msg = 'Registration Successful.   Please check your email for verification instructions.';
            
            return back()->with('success',$msg);
        }
        
    }

    // Get the user who has the same token and change his/her status to verified i.e. 1
    public function verify(Request $request,$token)
    {
        $token = $request->has('token')?$request->get('token'):$token;
        if (!empty($token)) {
            $user = User::where('email_token',$token)->first();
            if(!empty($user)){
                if(!empty($user['name'])) {
                    $user['f_name'] = $user['first_name'];
                    $user['l_name'] = $user['last_name'];
                }
                return view('auth.verify')
                        ->with('user',$user);
            }
            return back()->with('error','You Are Already verified or token not found in our records.');
        }
        return back()->with('error','Please Insert Token ');
    }

    public function verifyStore(Request $request,$id='')
    {
        $data = $request->all();
        $rules = [
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) 
        {
            return back()->withErrors($validator)
                        ->withInput();
            //$this->throwValidationException($request, $validator);
        }
        $securityToken = $this->EloquentHelper->generateSecurityToken();
        User::where('id','=',$id)->update([
            'username' => $data['username'],
            'password' => bcrypt($data['password'])
        ]);
        User::where('id',$id)->firstOrFail()->verified();
        $user = User::where('id',$id)->first();
        //Auth::loginUsingId($id);
        $salesforce_application_page_url = str_replace('&id=[CONTACT_ID]', '', $this->setting_details->salesforce_application_page_url);

        $salesforce_application_page_url = str_replace('[FNAME]', $user->first_name, $salesforce_application_page_url);
        $salesforce_application_page_url = str_replace('[LNAME]', $user->last_name, $salesforce_application_page_url);
        $salesforce_application_page_url = str_replace('[EMAIL]', $user->email, $salesforce_application_page_url);
        $salesforce_application_page_url = str_replace('[UID]',$id, $salesforce_application_page_url);
        $salesforce_application_page_url = ($user->verified_by==2)?str_replace('[PHONE]', $user->home_contact_num, $salesforce_application_page_url):str_replace('[PHONE]', '', $salesforce_application_page_url);
        $salesforce_application_page_url = $salesforce_application_page_url.'&DAIS_tag='.$securityToken;

        return redirect($salesforce_application_page_url);
    }
    
    /**
     * this function send email verification mail while user change there email on verification
     * page 
     * @return true on success 
     */
    /*public function verificationMail($requestData,$token)
    {
        $response = array('status'=>0);
        $userUpdateData = array();
        if(empty($token)) {
            $response['msg'] = "You Are Already verified or token not found in our records.";
            return $response;
        }
        $user = User::where('email_token',$token)->first();
        if(empty($user)) {
            $response['msg'] = "You Are Already verified or token not found in our records.";
            return $response;
        }
        if(strcasecmp($requestData['email'],$user['email'])  == 0 ) {
            $response['status'] = 1;
            return $response;
        }
        
        $rules = [
            'email' => 'required|string|email|max:255|unique:users',
        ];
        $validator = Validator::make($requestData, $rules,$msg);
        if($validator->fails()) {
            $this->throwValidationException($requestData, $validator);
            return $response;
        }
        $userUpdateData['email_token'] = str_random(10);
        $userUpdateData['email'] = $requestData['email'];
        $userObj = new User();
        User::where('id','=', $user['id'])->update(['username' => $data['username'],'password' => bcrypt($data['password'])]);
        
    }*/
    
    /** 
    * send OTP for account verification
    * @return true on sent
    */ 
    public function send_otp($phoneNumber, $msg )
    {
        $response = false;
        if(empty($phoneNumber) || empty($msg)) {
            return $response;
        }

        //echo  env('APP_SRC_NUMBER', ''); exit;
        $params = array(
            'src' => config('plivo.APP_SRC_NUMBER', ''),
            'dst' => $phoneNumber,
            'text' => $msg
        );
        $response = Plivo::sendSMS($params);

        return $response;
    }

    /**
    * this function generate unique email token or mobile code 
    * @return code or token
    */
    public function generate_token($method = 1) 
    {
        $token = '';
        if($method == 2) {
            $token = mt_rand(100000, 999999);

        } else {
            $token = str_random(10);
        }
        return $token;
    }

    /*
    * this function set sms text and send sms to provided number 
    * @return  true on success
    */
    public function send_sms($mobile_number,$token,$name)
    {
        $response = false;
        $smsText = "Hello $name,";
        $smsText .= "Thank you for registering with Art Of Elysium, Please enter following code for account verification : $token";
        $response = $this->send_otp($mobile_number,$smsText);
        return $response;
    }

    /**
    * this function return mobile code verification view 
    * @return code_verification view 
    */
    public function code_verification($user_id)
    {
        return view('auth.code_verification')->with('user_id',$user_id);
    }

    /**
    * this function use for resend mobile activation token
    * @return 
    */
    public function resendActivationToken(Request $request,$user_id='')
    {
        if ($user_id) {
            $user_id = base64_decode($user_id);
            $user = User::find($user_id);
            if (!empty($user)) {
                $this->send_sms($user->home_contact_ext.$user->home_contact_num,$user->email_token,$user->first_name);

                $url = 'code_verification/'.base64_encode($user_id);
                if ($request->has('CompanyNumber')) {
                    $url='code_verification/'.base64_encode($user_id).'?CompanyNumber='.$request->get('CompanyNumber');
                }

                return redirect($url)
                        ->with('success','Token sent again Successfully');
            }
        }       

    }
    /**
    * this function use for resend mobile activation token
    * @return 
    */
    public function resendActivationEmail(Request $request,$user_id='')
    {
        if ($user_id) {
            $inputs = $request->all();
            $user_id = base64_decode($user_id);
            
            $user = User::find($user_id);
            if (!empty($user)) {
                $_REQUEST['first_name'] = $user->first_name;
                $_REQUEST['last_name'] = $user->last_name;
                $user_ary = ['email_token' => $user->email_token, 'name' => $user->name];
                
                $url='code_verification/'.base64_encode($user_id);
                if ($request->has('CompanyNumber')) {
                    //$user_ary['company_number'] = $request->company_number;
                    $url='code_verification/'.base64_encode($user_id).'?CompanyNumber='.$request->get('CompanyNumber');
                    $inputs['company_number'] = $inputs['CompanyNumber'];
                }
                
                $email = new EmailVerification($inputs,new User($user_ary));
                Mail::to($user->email)->send($email);
                return redirect($url)
                        ->with('success','Please check your mailbox for activation link');
            }
        }       

    }
}
