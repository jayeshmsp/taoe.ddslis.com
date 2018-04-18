<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Auth;
use View;
use Illuminate\Support\Facades\Redis;
use App\Repositories\UserRepo;
use App\Chatmessage;

class ChatController extends Controller
{

    protected $UserRepo;
	public function __construct(UserRepo $UserRepo)
    {
    	$this->middleware('auth');
        $this->ctrl_url = 'chat';
        $this->UserRepo = $UserRepo;
        $this->view_path = 'admin.chat';
        View::share(['ctrl_url'=>$this->ctrl_url,
                    'view_path'=>$this->view_path,
                    'module_name'=> 'Chat',
                    'title'=>'Chat'
                ]);
    }

    /**
     * Show Chat Page
     */
    public function index(Request $request)
    {
        $current_user = Auth::user();
        $param['sort'] = $request->input("sort", array());
        $param['paginate'] = FALSE;
        $param['filter']['id']['value'] = $current_user->id;
        $param['filter']['id']['oprator'] = '!=';
        
        $items = $this->UserRepo->getBy($param);
        
        return view($this->view_path.'.index')
                ->with('items',$items)
                ->with('current_user',$current_user);
    }
    public function store(Request $request){
        $current_user = Auth::user();

        $message = $request->input('message');
        $receiver_id = $request->input('receiver_id');
        
        if(isset($message)) {

            /*INSERT CHAT INTO DATABASE*/
            Chatmessage::create([
                'sender_id'=>$current_user->id,
                'message'=>$message,
                'receiver_id'=>$receiver_id,
            ]);

            $redis = Redis::connection();
            $param = [
                "status" => 200, 
                "message" => $message,
                "sender_id" => $current_user->id,
                "receiver_id" => $receiver_id,
                "sender_name" => $current_user->name
                //"send_time" => \Carbon\Carbon::now()->diffForHumans()
            ];
            $redis->publish("messages", json_encode($param));
        }
    }

    public function upload(Request $request)
    {
        $current_user = Auth::user();
        $receiver_id = $request->get('receiver_id');
        
        $file = $request->file('file');
        $filename = time(). '-' . $file->getClientOriginalName();
        $file->move(public_path().'/chat_upload/', $filename);

        $redis = Redis::connection();
        $param = [
            "status" => 200, 
            "file_name" => $filename,
            "sender_id" => $current_user->id,
            "receiver_id" => $receiver_id,
            "sender_name" => $current_user->name
        ];
        $redis->publish("file_upload", json_encode($param));
        
    }
}
