<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\UserRepo;
use App\Repositories\SettingRepo;
use App\Repositories\RoleRepo;
use Illuminate\Support\Facades\Validator;
use View;
use Auth;
use DB;
use Response;
use App\User as User;
use App\Mail\EmailVerification;
use Mail;
use App\Mail\resetPassword;

class UserController extends Controller
{
    private $view_path;
    protected $UserRepo;
    protected $RoleRepo;
    protected $SettingRepo;
    protected $setting_details;

    public function __construct(Request $request,UserRepo $UserRepo,RoleRepo $RoleRepo,SettingRepo $SettingRepo)
    {
        $this->middleware('auth');
        $this->UserRepo = $UserRepo;
        $this->RoleRepo = $RoleRepo;
        $this->SettingRepo = $SettingRepo;
        $this->setting_details = $SettingRepo->getBy(array('single'=>true));

        $this->view_path = 'users.user';
        View::share('module_name', 'Users');
    }

    // Method : index
    // Param : request
    // Output : return index view
    public function index(Request $request)
    {
        $user = Auth::user();
        $role = DB::table('role_user')->select('role_id')->where('user_id',$user->id)->first(); 
        if ($role->role_id != 1) {
            if (strtolower($user->status)=='completed') {
                $salesforce_dashboard_url = str_replace('[CONTACT_ID]', $user->contact_id, $this->setting_details->salesforce_dashboard_url);
                return redirect($salesforce_dashboard_url);   
                //return redirect('http://sandbox1-theartofelysium.cs14.force.com/CalendarDashboard?profile=false&id='.$user->contact_id);   
            }
            $salesforce_application_page_url = str_replace('[CONTACT_ID]', $user->contact_id, $this->setting_details->salesforce_application_page_url);
            return redirect($salesforce_application_page_url);
            //return redirect('http://sandbox1-theartofelysium.cs14.force.com/VolunteerApplicationVFpage3?profile=false&id='.$user->contact_id);
        }

        $param['filter'] = $request->input("filter", array());
        $param['sort'] = $request->input("sort", array());
        $param['paginate'] = TRUE;
        $param['sort'] = array('id'=>'desc');
        if($request->input('filter.name.value')){
            $param['filter']['name']['value'] = '%'.$request->input('filter.name.value').'%';
        } 
        //$param['filter']['platform']['value'] = 'art-of-elysium';
        $items = $this->UserRepo->getBy($param);
        $roles = $this->RoleRepo->getBy();
        foreach($roles as $role ) {
            $roles[$role->id] = $role->display_name;
        }
        //serial number
        $srno = ($request->input('page', 1) - 1) * config("setup.par_page", 10)  + 1;
        $logged_id = \Auth::user()->id;
        $compact = compact('items','srno','roles','logged_id');

        return view($this->view_path . '.index',$compact)
                ->with('title', 'list');
    }

    public function create()
    {
        $roles = $this->RoleRepo->lists('name','id');
        $compact = compact('roles');
        return view($this->view_path . '.create',$compact)
                ->with('title', 'create');
    }

    public function store(Request $request)
    {
        $inputs = $request->except('_token');
        $data   = array_except($inputs, 'save', 'save_exit','password_confirmation');

        $rules = [
            'first_name' => 'required|alpha_space|string|max:255',
            'last_name' => 'required|alpha_space|string|max:255',
            'email' => 'required|string|email|max:255|is_user_exist:users,name,'.$data['first_name']." ".$data['last_name'],
            ///'password' => 'required|string|min:6|confirmed',
            'username' => 'required|string|max:255|unique:users',
            'role_id' => 'required'
        ];
        // Create a new validator instance from our validation rules
        $validator = Validator::make($inputs, $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails()) {
            return redirect('user/create')
                ->withErrors($validator)
                ->withInput();
        }

        if($user = $this->UserRepo->create($data)){
            $email = new EmailVerification(new User(['email_token' => $user->email_token, 'name' => $user->name]));
            Mail::to($user->email)->send($email);
            return redirect('user')->with('success', 'Record added sucessfully, Verification mail sent to user.');
        }

        return redirect('user/')->with('error', 'Can not be created');
    }

    public function edit($id)
    {
        $item = $this->UserRepo->find($id);
        $selected_roles = $this->UserRepo->currentUserRole($id);
        $item = $item->toArray();
        $item['role_id'] = $selected_roles;
        $roles = $this->RoleRepo->lists('name','id');

        //unset($item['password']);

        $compact = compact('item','roles');
        return view($this->view_path . '.update',$compact)
                ->with('title', 'edit');
    }

    public function update(Request $request,$id)
    {
        $inputs = $request->except('_token','_method','password_confirmation');
        $data   = array_except($inputs,array('save','save_exit'));

         $rules = [
            'first_name' => 'required|string|alpha_space|max:255',
            'last_name' => 'required|string|alpha_space|max:255',
            //'password' => 'required|string|min:6|confirmed',
            'username' => 'required|string|max:255|unique:users,username,'.$id,
            'role_id' => 'required'
        ];
        if(!$request->input('password')){
            unset($rules['password']);
        }
        $rules['email']= 'required|string|email|max:255|is_user_exist:users,name,'.$data['first_name']." ".$data['last_name']. ",".$id;
        // Create a new validator instance from our validation rules
        $validator = Validator::make($inputs, $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails()) {
            return redirect('user/'.$id.'/edit')
                ->withErrors($validator)
                ->withInput();
        }

        if($this->UserRepo->update($data,$id)){
            return redirect('user')
            ->with('success', 'Record updated sucessfully');
        }

        return redirect('user/')->with('error', 'Can not be created');
    }

    public function destroy(Request $request,$id)
    {
        if(!empty($id)) {
            User::where("id",$id)->forceDelete();
        }
        return redirect('user')->with('success', 'Records is deleted');
    }

    public function profile()
    {
        $item = $this->UserRepo->find(Auth::user()->id);
        $interest = $this->SettingRepo->lists('interest');
        $skill = $this->SettingRepo->lists('skill');
        $state = $this->SettingRepo->lists('state');
        
        View::share('title','Profile');
        
        $compact = compact('item','interest','skill','state');
        return view($this->view_path . '.profile',$compact)
                ->with('title', 'profile');        
    }

    public function postProfile(Request $request)
    {
        $inputs = $request->except('_token','_method');
       
        $data   = array_except($inputs,array('save','save_exit','password_confirmation'));
        $id = Auth::user()->id;

         $rules = [
            'first_name' => 'required|string|alpha_space|max:255',
            'last_name' => 'required|string|alpha_space|max:255',
            'password' => 'sometimes|required|string|min:6|confirmed',
            /*'mobile_contact_num' => 'required_without_all:work_contact_num,home_contact_num',
            'work_contact_num' => 'required_without_all:mobile_contact_num,home_contact_num',*/
            'home_contact_num' => 'required',
            'dob' => 'required|date|date_format:Y-m-d',
            'email' => "required|email|max:255|unique:users,email,".$id,
            //'username' => "required|max:255|unique:users,username,".$id,
            'address' => "max:255",
            //'zipcode' => "max:5|min:5"
        ];
        if(!$request->input('password')){
            unset($rules['password']);
        }
        
        // Create a new validator instance from our validation rules
        $validator = Validator::make($inputs, $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails()) {
            return redirect('user/profile')
                ->withErrors($validator)
                ->withInput();
        }

        if($this->UserRepo->updateProfile($data,$id)){
            return redirect('user/profile')
            ->with('success', 'Profile updated sucessfully');
        }

        return redirect('user/profile')->with('error', 'Can not be updated');
    }
    /**
     * this function use for reset user password 
     */
    public function resetPassword(Request $request)
    {   
        if($request->ajax()) {
            $inputs = $request->all();    
            $rules = ['password'=> 'required|min:6|confirmed'];
            $validator = Validator::make($inputs, $rules);
            if ($validator->fails()) {
                return Response::json(["msg"=>"Validation error occur."],403);
            }
            $userData = User::findOrFail($inputs['user_id']);
            $userData->password = bcrypt($inputs['password']);
            $userData->save();
            $mail_msg = "Your password is changed by admin, new password is : ".$inputs['password'];
            $email = new resetPassword(['user' => $userData, 'newPass' => $inputs['password']]);
            Mail::to($userData->email)->send($email);
            
            return Response::json(["msg"=>"Password has been changed and mail sent to user.",200]);
        }
    }
}
