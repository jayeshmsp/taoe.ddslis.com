<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Chatmessage extends Model
{
    //
    protected $fillable = ['sender_id','message','receiver_id'];
    protected $table = 'chat_messages';
}
