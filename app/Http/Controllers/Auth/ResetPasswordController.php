<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Repositories\SettingRepo;
use App\Helpers\EloquentHelper;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    protected $SettingRepo;
    protected $EloquentHelper;
    public function __construct()
    {
        $SettingRepo = new SettingRepo;
        $this->EloquentHelper = new EloquentHelper();
        $this->setting_details = $SettingRepo->getBy(array('single'=>true));
        $this->middleware('guest');
    }

    /**
     * Display the password reset view for the given token.
     *
     * If no token is present, display the link request form.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string|null  $token
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showResetForm(Request $request, $token = null)
    {
        $user_details = array();
        if ($request->has('q')) {
            $user_id = decrypt($request->q);

            session([
                'showResetForm_user_id' => $user_id
            ]);

            $user_details = User::find($user_id)->toArray();
        }

        return view('auth.passwords.reset')->with(
            ['token' => $token, 'email' => $request->email, 'user_details' => $user_details]
        );
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @param  string  $password
     * @return void
     */
    protected function resetPassword($user, $password)
    {
        $user->forceFill([
            'password' => bcrypt($password),
            'remember_token' => Str::random(60),
        ])->save();

        //$this->guard()->login($user);
    }
    /**
     * Get the password reset credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        return $request->only(
            'email', 'password', 'password_confirmation', 'token', 'first_name', 'last_name'
        );
    }
    /**
     * Get the password reset validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        return [
            'token' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6',
        ];
    }
     /**
     * Get the response for a successful password reset.
     *
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetResponse($response)
    {
        if (\Session::has('showResetForm_user_id')){
            $user_details = User::find(\Session::get('showResetForm_user_id'));

            $securityToken = $this->EloquentHelper->generateSecurityToken();
            
            if (!empty($user_details->contact_id) && strtolower($user_details->status)=='completed' ) {
                $salesforce_dashboard_url = str_replace('[CONTACT_ID]', $user_details->contact_id, $this->setting_details->salesforce_dashboard_url);
                $salesforce_dashboard_url = str_replace('[UID]', $user_details->id, $salesforce_dashboard_url);
                $salesforce_dashboard_url = str_replace('[FNAME]', $user_details->first_name, $salesforce_dashboard_url);
                $salesforce_dashboard_url = str_replace('[LNAME]', $user_details->last_name, $salesforce_dashboard_url);
                $salesforce_dashboard_url = str_replace('[EMAIL]', $user_details->email, $salesforce_dashboard_url);
                $salesforce_dashboard_url = ($user_details->verified_by==2)?str_replace('[PHONE]', $user_details->home_contact_num, $salesforce_dashboard_url):str_replace('[PHONE]', '', $salesforce_dashboard_url);
                
                $salesforce_dashboard_url = $salesforce_dashboard_url.'&DAIS_tag='.$securityToken;

                return redirect($salesforce_dashboard_url);   
            }
            $salesforce_application_page_url = str_replace('&id=[CONTACT_ID]', '', $this->setting_details->salesforce_application_page_url);
            $salesforce_application_page_url = str_replace('[UID]', $user_details->id, $salesforce_application_page_url);
            $salesforce_application_page_url = str_replace('[FNAME]', $user_details->first_name, $salesforce_application_page_url);
            $salesforce_application_page_url = str_replace('[LNAME]', $user_details->last_name, $salesforce_application_page_url);
            $salesforce_application_page_url = str_replace('[EMAIL]', $user_details->email, $salesforce_application_page_url);
            $salesforce_application_page_url = ($user_details->verified_by==2)?str_replace('[PHONE]', $user_details->home_contact_num, $salesforce_application_page_url):str_replace('[PHONE]', '', $salesforce_application_page_url);
            
            $salesforce_application_page_url = $salesforce_application_page_url.'&DAIS_tag='.$securityToken;

            return redirect($salesforce_application_page_url);
        }
        return redirect($this->redirectPath())
                            ->with('status', trans($response));
    }
}
