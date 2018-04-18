<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use App\User;
Use Plivo;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }
    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        $this->validateEmail($request);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        
        $reset_user = User::where('email','=',$request->get('email'))->where('username','=',$request->get('username'))->first();
        
        \DB::table('password_resets')->where('email','=',$request->get('email'))->delete();
        


        if (!empty($reset_user)) {
            
            if ($request->verified_by=='2' && empty($reset_user->home_contact_num)) {
                return back()->with('error','We did not found mobile number for this user. please use email reset password');
            }elseif($request->verified_by=='2'){
                $reset_token = $this->generate_token($request->verified_by);
                User::find($reset_user->id)->update(['email_token'=>$reset_token]);

                $mobile_no = str_replace('+', '', $reset_user->home_contact_ext).$reset_user->home_contact_num;

                $this->send_sms($mobile_no,$reset_token,$reset_user->first_name);

                $url = 'reset_code_verification/'.base64_encode($reset_user->id);
                if ($request->has('company_number')) {
                    $url = 'reset_code_verification/'.base64_encode($reset_user->id).'?CompanyNumber='.$request->get('company_number');
                }
                return redirect($url)
                        ->with('success','We have sent OTP to your registered mobile number, Please enter OTP');
            }

            $request->session()->forget('reset_first_name');
            $request->session()->forget('reset_last_name');

            session([
                'reset_first_name' => (isset($reset_user->first_name)?$reset_user->first_name:''),
                'reset_last_name' => (isset($reset_user->last_name)?$reset_user->last_name:''),
                'reset_username' => (isset($reset_user->username)?$reset_user->username:''),
                'reset_user_id' => (isset($reset_user->id)?$reset_user->id:''),
            ]);
        }
        
        $response = $this->broker()->sendResetLink(
            [
                'email' => $request->get('email'),
                'username' => $request->get('username')
            ]
        );

        return $response == Password::RESET_LINK_SENT
                    ? $this->sendResetLinkResponse($response)
                    : $this->sendResetLinkFailedResponse($request, $response);
    }

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
        $smsText .= "Please enter following code for reset password : $token";
        $response = $this->send_otp($mobile_number,$smsText);
        return $response;
    }

    /**
     * Validate the email for the given request.
     *
     * @param \Illuminate\Http\Request  $request
     * @return void
     */
    protected function validateEmail(Request $request)
    {
        $this->validate($request, ['email' => 'required|email','username'=>'required']);
    }

    /**
     * Get the response for a failed password reset link.
     *
     * @param  \Illuminate\Http\Request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        return back()->withErrors([
            'email' => trans($response),
            'username' => 'Please provide valid username'
        ]);
    }
    /**
     * Get the response for a successful password reset link.
     *
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendResetLinkResponse($response)
    {
        return back()->with('status', trans($response));
    }

    /**
    * this function return mobile code verification view 
    * @return code_verification view 
    */
    public function reset_code_verification($user_id)
    {
        return view('auth.reset_code_verification')->with('user_id',$user_id);
    }
}
