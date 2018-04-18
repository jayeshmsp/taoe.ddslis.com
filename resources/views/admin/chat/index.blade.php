@extends('layouts.chat')

@section('content')
    <div id="page-wrapper">
        <div class="container-fluid">
            <!-- .chat-row -->
                <div class="chat-main-box">
                    <!-- .chat-left-panel -->
                    <div class="chat-left-aside">
                        <div class="open-panel"><i class="ti-angle-right"></i></div>
                        <div class="chat-left-inner">
                            <ul class="chatonline style-none ">
                                @foreach($items as $item)
                                    <li>
                                        <a href="javascript:void(0)" receiver-name="{{$item->name}}" class="user-name" id="{{$item->id}}">
                                            <span>
                                                {{$item->name}}
                                                <small id="user-status-{{$item->id}}" class="text-{{(!empty($item->is_logged_in))?'success':'danger'}}">{{ (!empty($item->is_logged_in))?'online':'offline' }}</small>
                                            </span>
                                        </a>
                                    </li>
                                    <hr style="margin:0;padding:0">
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <!-- .chat-left-panel -->
                    <!-- .chat-right-panel -->
                    <div class="chat-right-aside">
                        <div class="chat-main-header">
                            <div class="p-10 b-b">
                                <h3 class="box-title" id="chat-box-heder"></h3> </div>
                        </div>
                        <div class="chat-box">
                            <input type="hidden" name="receiver_id" id="receiver_id">
                            <ul class="chat-list slimscroll p-t-30" id="message-content">
                                
                            </ul>
                            <div class="row send-chat-box">
                                <div class="col-sm-12">
                                    <textarea class="form-control" id="message" placeholder="Type your message"></textarea>
                                    <div class="custom-send">
                                        {{-- <a href="javacript:void(0)" class="cst-icon" data-toggle="tooltip" title="Insert Emojis"><i class="ti-face-smile"></i></a> 
                                        <a href="javacript:void(0)" class="cst-icon" data-toggle="tooltip" title="File Attachment"><i class="fa fa-paperclip"></i></a> --}}
                                        <button class="btn btn-danger btn-rounded" id="send-msg" type="button">Send</button>
                                    </div>
                                    <div class="clip-upload custom-send" style="right: 10%;font-size: 22px;bottom: 7px;background: #dfdfdf;padding: 2px;width: 42px;text-align: center;vertical-align: middle;border-radius: 21px;cursor: pointer;">
                                      <label for="file-input" style="cursor: pointer;">
                                      <i class="fa fa-paperclip fa-lg" aria-hidden="true"></i>
                                      <input type="file" class="file-input hide" name="file-input" id="file-input">
                                      <div class="filename-container hide"></div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- .chat-right-panel -->
                </div>
                <!-- /.chat-row -->
        </div>
    </div>
    <script type="text/javascript">
        var current_login_user_name = "{{ $current_user->name }}";
        $('#file-input').change(function(event) {
            var receiver_id = parseInt($("#receiver_id").val()) || 0;
            if (receiver_id!=0) {
                //console.log(event.target.files[0]);
                $("#message-content").append('<li class="odd"><div class="chat-body"><div class="chat-text"><h4>'+current_login_user_name+'</h4><img src="'+URL.createObjectURL(event.target.files[0])+'" width="200"/></div></div></li>');
                var receiver_id = parseInt($("#receiver_id").val()) || 0;
                
                var myFormData = new FormData();
                myFormData.append('file', event.target.files[0]);
                myFormData.append('_token', "{{csrf_token()}}");
                myFormData.append('receiver_id', receiver_id);

                $.ajax({
                  url: 'chat/upload',
                  type: 'POST',
                  processData: false, // important
                  contentType: false, // important
                  dataType : 'json',
                  data: myFormData,
                  success: function(data)
                    {
                        $(".loader").hide();
                    },
                    error: function(jqXHR)
                    {
                        $(".loader").hide();
                    },
                    complete: function()
                    {
                        $(".loader").hide();
                    }
                });
            }else{
                alert('Please select user for send msg');
            }
        });

        /*ON CHANGE USER CHAT HEADER CHANGE*/
        $(".user-name").click(function(){
            $(".user-name").each(function(){
                $(this).removeClass('active-user');
            });
            $(this).addClass('active-user');
            
            var receiver_id = $(this).attr('id');
            var receiver_name = $(this).attr('receiver-name');
            
            $('#receiver_id').val(receiver_id);
            $("#chat-box-heder").text(receiver_name);
        });


        /*SEND MESSAGE*/
        $("textarea").keyup(function(e) {
            var code = e.keyCode ? e.keyCode : e.which;
            if (code == 13) {  // Enter keycode
                send_message();
            }
        });
        $('#send-msg').click(function(){
            send_message();
        });
        function send_message() {
            var message = $("#message").val();
            var receiver_id = parseInt($("#receiver_id").val()) || 0;
            if (receiver_id!=0) {
                $.post("chat", {message:message,receiver_id:receiver_id,"_token": "{{ csrf_token() }}"}, function (data) {
                    $("#message").val('');
                });
            }else{
                alert('Please select user for send msg');
            }
        }


        /*RECEIVER SOCKET*/
        var socket = io.connect('http://127.0.0.1:3000/');
        socket.on("messages", function (data) {
            var data = JSON.parse(data);
            console.log(data);
            var current_login_user_id = "{{ $current_user->id }}";
            var current_login_user_name = "{{ $current_user->name }}";
            
            if (current_login_user_id==data.receiver_id) {
                $('.chat-list').animate({scrollTop: $('.chat-box').offset().top +999999999999999 }, 'slow');
                $("#message-content").append('<li><div class="chat-body"><div class="chat-text"><h4>'+data.sender_name+'</h4><p> '+data.message+'  </div></div></li>');
            }
            if (current_login_user_id==data.sender_id) {
                $('.chat-list').animate({scrollTop: $('.chat-box').offset().top +999999999999999 }, 'slow');
                $("#message-content").append('<li class="odd"><div class="chat-body"><div class="chat-text"><h4>'+current_login_user_name+'</h4><p> '+data.message+'  </div></div></li>');
            }
        });
        /*ONLINE OFF LINE STATUS*/
        socket.on("user_status", function (data) {
            var data = JSON.parse(data);
            $('#user-status-'+data.user_id).text(data.status);
            $('#user-status-'+data.user_id).removeClass();
            $('#user-status-'+data.user_id).addClass('text-'+data.label_class);
        });
        /*File Sharing*/
        socket.on("file_upload", function (data) {
            var data = JSON.parse(data);
            var current_login_user_id = "{{ $current_user->id }}";
            if (current_login_user_id==data.receiver_id) {
                $('.chat-list').animate({scrollTop: $('.chat-box').offset().top +999999999999999 }, 'slow');
                var file_path = "{{asset('chat_upload')}}"+"/"+data.file_name;
                $("#message-content").append('<li>'+
                    '<div class="chat-body">'+
                        '<div class="chat-text">'+
                            '<h4>'+data.sender_name+'</h4>'+
                            '<img src="'+file_path+'" width="200"/>'+
                        '</div>'+
                    '</div>'+
                '</li>');
            }
        });
    </script>
@stop