<?php namespace App\Http\Controllers\API\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Helpers\EloquentHelper;
use App\User;
use DB;
use Response;
use App\Customer;

class UserController extends Controller
{
    protected $EloquentHelper;
    public function __construct(Request $request)
    {
        $this->EloquentHelper = new EloquentHelper;
        $this->content = array();
    }
    //GbNS3sSEhOGAlsbhWXdLpFvtFwyBJlLos4cdgJEw
    public function login(Request $request) {
        if(Auth::attempt(['username' => request('username'), 'password' => request('password')])){
             $user = Auth::user();
             $this->content['token'] =  $user->createToken('Art Of Elysium')->accessToken;
             $status = 200;
        }
        else{
            $this->content['error'] = "Unauthorised";
            $status = 200;
        }
        DB::table('api_token')->insert(['client_secret'=>$this->content['token'],'UserID'=>$user->id]);
        return response()->json($this->content, $status);    
    }
    public function details(){
        return response()->json(['user' => Auth::user()]);
    }
    public function create(Request $request)
    {
        $result = [];
        $data = $request->json();
        if ( count($data) && !empty($data)) {
            foreach ($data as $key => $value) {
                //$o = array();
                //foreach ($value as $k => $val) { $new_key = str_replace(' ', '_', $k); $o[$new_key] = $val; }
                if( isset($value['UserID'])  && $this->is_user_exist($value['UserID'])) {
                    $result[]=$this->updateDB($value,1);
                } else {
                    $result[]=$this->createDB($value);
                }
            }
        }else{
            if( (isset($request['UserID'])) && ($this->is_user_exist($request['UserID']))) {
                $result[]=$this->updateDB($request->all(),1);
            } else {
                $result = $this->createDB($request->all());
            }
        }
        $log = ['request_input'=>json_encode(\Request::all()),
                'response_output'=>json_encode($result),
                'action'=>'POST',
                'ip'=>\Request::ip()];
        $this->EloquentHelper->apiLog($log);
        return response()->json($result, 200);
    }
    // ============================================= 
    /* menthod : createDB
    * @output : 
    * @Description : use for create a user
    */// ==============================================    
    public function createDB($inputs=array())
    {
		if (!isset($inputs['ClientSecret'])) {
            $this->content['error'] = "Unauthorised : Please provide valid ClientSecret";
            return $this->content;

        }
        $token_exits = DB::table('api_token')->where('client_secret','=',$inputs['ClientSecret'])->first();
        if (empty($token_exits)) {
            $this->content['error'] = "Unauthorised : Please provide valid ClientSecret.";
            return $this->content;
        }
        if (isset($inputs['FirstName']) && isset($inputs['LastName']) && isset($inputs['EmailAddress']) ) {
            if (!empty($inputs['FirstName']) && !empty($inputs['LastName']) && !empty($inputs['EmailAddress']) ) {
                //SEARCH USER
                $user_exists = User::where('first_name','=',$inputs['FirstName'])->where('last_name', '=', $inputs['LastName'])->where('email', '=' ,$inputs['EmailAddress'])->first();
                if (!empty($user_exists)) {
                    $this->content['UserID'] = $user_exists->id;
                    $this->content['ContactID'] = $inputs['ContactID'];
                    $this->content['ResultCode'] = 1;
                }else{
                    //CREATE USER
                    $user_id = User::create([
                        'name' => $inputs['FirstName'].' '.$inputs['LastName'],
                        'first_name' => $inputs['FirstName'],
                        'platform'  => 'art-of-elysium',
                        'last_name' => $inputs['LastName'],
                        'customer_id' => $token_exits->customer_id,
                        'email' => $inputs['EmailAddress'],
                        'contact_id' => (isset($inputs['ContactID'])?$inputs['ContactID']:''),
                        'status' => (isset($inputs['ApplicationStatus'])?$inputs['ApplicationStatus']:''),
                        'verified' => DB::raw('"1"'),
                    ])->getKey();
                    
                    $this->content['UserID'] = $user_id;
                    $this->content['ResultCode'] = 0;
                    $this->content['ContactID'] = (isset($inputs['ContactID'])?$inputs['ContactID']:'');
                }

                return $this->content;

            }else{
                $this->content['error'] = "Error : Please provide all value of inputs";
                return $this->content;
            }
        }else{
            $this->content['error'] = "Error : Please provide all inputs (FirstName,LastName,EmailAddress)";
            return $this->content;
        }
    }
    // ============================================= 
    /** menthod : 
    * @param  : 
    * @Description : 
    **/// ==============================================    
    public function update(Request $request)
    {
        $result = [];
        //echo "<pre>";  print_r($request->all()); exit();
        $data = $request->json();

        if ( count($data) && !empty($data)) {
            foreach ($data as $key => $value) {
                //$o = array();
                //foreach ($value as $k => $val) { $new_key = str_replace(' ', '_', $k); $o[$new_key] = $val; }
                $result[]=$this->updateDB($value);
            }
        }else{
        	//echo"test";
            $result = $this->updateDB($request->all());
        }
        $log = ['request_input'=>json_encode(\Request::all()),
                'response_output'=>json_encode($result),
                'action'=>'PATCH',
                'ip'=>\Request::ip()];
        $this->EloquentHelper->apiLog($log);
        return response()->json($result, 200);
        
    }
    // ============================================= 
    /* menthod : updateDB
    * @output : 
    * @Description : use for update a user
    */// ==============================================    
    public function updateDB($inputs=array(),$from_create = 0)
    {
		
		if (!isset($inputs['ClientSecret'])) {
            $this->content['ContactID'] = $inputs['ContactID'];
            $this->content['error'] = "Unauthorised : Please provide valid ClientSecret";
            return $this->content;
        }
        $token_exits = DB::table('api_token')->where('client_secret','=',$inputs['ClientSecret'])->first();
        
        if (empty($token_exits)) {
            $this->content['ContactID'] = $inputs['ContactID'];
            $this->content['error'] = "Unauthorised : Please provide valid ClientSecret.";
            return $this->content;
        }
        
        /*USER ID COMPARSORY*/
        /*USER ID COMPARSORY*/
        if (!isset($inputs['UserID']) || !isset($inputs['FirstName']) || !isset($inputs['LastName']) || !isset($inputs['EmailAddress']) ) {
            $this->content['ContactID'] = $inputs['ContactID'];
            $this->content['error'] = "Error : Please provide all inputs (UserID,FirstName,LastName,EmailAddress)";
            return $this->content;
        }

        $user = User::find($inputs['UserID']);
        $this->content['UserID'] = $inputs['UserID'];
        if (empty($user)) {
            $this->content['ResultCode'] = 2;
            $this->content['ContactID'] = $inputs['ContactID'];
        }else{
            User::where('id','=',$inputs['UserID'])->update([
                'contact_id'=>$inputs['ContactID'],
                'email'=>$inputs['EmailAddress'],
                'customer_id'=>$token_exits->customer_id,
                'first_name'=>$inputs['FirstName'],
                'last_name'=>$inputs['LastName'],
                'name'=>$inputs['FirstName'].' '.$inputs['LastName'],
                'status'=> isset($inputs['ApplicationStatus']) ? $inputs['ApplicationStatus'] : null
            ]);
            if($from_create == 1) {
                $this->content['ResultCode'] = 1;
            }else {
                $this->content['ResultCode'] = 0;
            }
            $this->content['ContactID'] = User::find($inputs['UserID'])->contact_id;

        }
        return $this->content;
    }

    public function delete(Request $request)
    {
        $result = [];
        $data = $request->json();
        if ( count($data) && !empty($data)) {
            foreach ($data as $key => $value) {
                //$o = array();
                //foreach ($value as $k => $val) { $new_key = str_replace(' ', '_', $k); $o[$new_key] = $val; }
                $result[]=$this->deleteDB($value);
            }
        }else{
            $result = $this->deleteDB($request->all());
        }
        $log = ['request_input'=>json_encode(\Request::all()),
                'response_output'=>json_encode($result),
                'action'=>"DELETED",
                'ip'=>\Request::ip()];
        $this->EloquentHelper->apiLog($log);
        return response()->json($result, 200);
    }
    // ============================================= 
    /* menthod : deleteDB
    * @output : 
    * @Description : use for delete a user
    */// ==============================================    
    public function deleteDB($inputs=array())
    {
        if (!isset($inputs['ClientSecret'])) {
            $this->content['error'] = "Unauthorised : Please provide valid ClientSecret";
            return $this->content;
        }
        $token_exits = DB::table('api_token')->where('client_secret','=',$inputs['ClientSecret'])->first();
        
        if (empty($token_exits)) {
            $this->content['error'] = "Unauthorised : Please provide valid ClientSecret.";
            return $this->content;
        }
        
        /*USER ID COMPARSORY*/
        if (!isset($inputs['UserID'])) {
            $this->content['error'] = "Error : Please provide UserID";
            return $this->content;
        }

        $user = User::find($inputs['UserID']);
        $this->content['UserID'] = $inputs['UserID'];
        if (empty($user)) {
            $this->content['ResultCode'] = 3;
        }else{
            User::where('id','=',$inputs['UserID'])->delete();
            $this->content['ContactID'] = $user->contact_id;
            $this->content['ResultCode'] = 0;
        }
        return $this->content;
    }

    /**
    * this function check provided userid found or not in database 
    * @return @true on found 
    */
    public function is_user_exist($id) 
    {
        $returnVal = false;
        if(empty($id)) {
            return $returnVal;
        }
        $user = User::find($id);
        if(!empty($user)) {
            $returnVal =  true;
        }
        return $returnVal;
    }
    /**
    * this function use for return users list with sort and filter with company number
    * @return @list on found
    */
    public function getList(Request $request)
    {
        $inputs = $request->all();
        if ( !empty($inputs) && isset($inputs['CompanyNumber']) && !empty($inputs['CompanyNumber']) ) {
            $customer_exists = Customer::where('company_number','=',\DB::raw('"'.$inputs['CompanyNumber'].'"'))->first();
                
            if (empty($customer_exists)) {
                $this->content['error'] = "Error : CompanyNumber not found in our record provide valid number.";    
            }else{
                $query = User::where('customer_id','=',DB::raw('"'.$customer_exists->id.'"'));
                foreach ($inputs as $key => $value) {
                    $key = preg_replace('/\B([A-Z])/', '_$1', $key);
                    if (\Schema::hasColumn('users', $key)) {
                        $query = $query->where(strtolower($key),'=',$value);
                    }
                }
                $this->content['data'] = $query->get();
            }

        }else{
            $this->content['error'] = "Error : Please provide CompanyNumber.";
        }

        $log = ['request_input'=>json_encode($inputs),
                'response_output'=>json_encode($this->content),
                'action'=>"GET",
                'ip'=>$request->ip()];
        $this->EloquentHelper->apiLog($log);
        
        return response()->json($this->content, 200);
    }
}
