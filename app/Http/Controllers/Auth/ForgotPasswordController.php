<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use App\User;
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
}
