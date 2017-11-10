 $(document).ready(function(){
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    oTable = $('#users').DataTable({
            responsive: true,
            //"fixedHeader": true,
            "processing": true,
            "serverSide": true,
            "ajax": "user/getDatas",
            "columns": [
                {data:'id',id:'id'},
                {data:'company_name',id:'company_name'},
                {data:'first_name',id:'first_name'},
                {data:'last_name',id:'last_name'},
                {data:'email',id:'email'},
                {data:'username',id:'username'},
                {data:'contact_id',id:'contact_id'},
                {data:'status',id:'status'},
                {data:'platform',id:'platform'},
                {data:'last_login',id:'last_login'},
                {data:'provider',id:'provider'},
                {data:'login_ip',id:'login_ip'},
                {data:'action',id:'action',orderable: false, searchable: false},
            ]
        });

        var url = "/change-password";
        
        $('body').on('click','.reset-pass-modal',function(){
           $("#resetPassID").val($(this).attr('data-userId'));
           $("#resetPassModal").modal("show"); 
        });
        
        $("#savePass").click(function(){
            var formData = $("#reset_pass_form").serialize();
            $.post(url, formData, function(result){
                $(".pass-success").show();
                $(".pass-success").text(result.msg);
                setTimeout(function(){
                    $(".pass-success").hide();
                    $("#resetPassModal").modal('hide');
                    $('#reset_pass_form')[0].reset();
                },2000);
            }).fail(function(result) {
                $(".pass-error").show();
                var result = $.parseJSON(result.responseText);
                console.log(result);
                $(".pass-error").text(result.msg);
                setTimeout(function(){
                    $(".pass-error").hide();
                },3000);
            });
        })
    });