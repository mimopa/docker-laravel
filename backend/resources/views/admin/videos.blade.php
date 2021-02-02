@extends('layouts.admin')
@section('content')
<?php
$path = route('admin.videos.index');
?>
<style>
.video_play{
    font-size: 35px;
    position: absolute;
    left: 45%;
    top: 37%;
    color: #fff;
    border-radius: 50%;
}
#homeVideo button{
    position: absolute;
    right: 0px;
    z-index: 9999;
}
</style>
<section class="rightside-main">
	<div class="container-fluid">
        <div class="page-top">
            <div class="page-header borderless ">
                <h4>Manage Videos</h4>   
            </div>
            <div class="page-berdcrumb">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard')}}">
                            <i class="fa fa-home"></i> Dashboard
                        </a>
                    </li>
                    <!-- <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard')}}">Dashboard</a>
                    </li> -->
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.videos.index')}}">Videos Management</a>
                    </li>
                    <!-- <li class="breadcrumb-item">
                        <a href="#">Manage Videos</a>
                    </li> -->
                </ul>       
            </div>
        </div>
        <div class="card table-card ">
            <div class="row card-header borderless ">
                    <div class="col-md-8 col-lg-8">
                        <h3>Videos</h3>
                    </div>
                    <div class="col-md-4 col-lg-4 align-right"> 
                        <button id="add" class="btn btn-primary" onclick='document.location.href="<?php echo $path.'/create/'?>"'>Add New
                        </button>
                    </div>
                </div>
           
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-12 col-md-12">
                                @if ($message = Session::get('success'))
                                <div class="alert alert-success alert-block">
                                    <button type="button" class="close" data-dismiss="alert">×</button> 
                                    <strong>{{ $message }}</strong>
                                    <?php Session::forget('success');?>
                                </div>
                                @endif
                                @if ($message = Session::get('error'))
                                <div class="alert alert-danger alert-block">
                                    <button type="button" class="close" data-dismiss="alert">×</button> 
                                    <strong>{!! $message !!}</strong>
                                    <?php Session::forget('error');?>
                                </div>
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12 col-md-12 table-responsive">
                                <table id="data_table" class="table cell-border compact hover order-column row-border stripe">
                                    <thead>
                                        <tr>
                                            <th width="10%">Action</th>
                                            <th width="5%" class="h_check">
                                                <input type="checkbox" id="remember_me" name="key_m[]" class="red-check"/>
                                            </th>
                                            <th class="sorting">Username</th>
                                            <th class="sorting">Title</th>
                                            <th class="sorting">Video</th>
                                            <th>Active</th>
                                            <th>Flaged</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row margin-tp-bt-10">
                            <div class="col-lg-12 col-md-12">
                                <button id="deleteSel" class="btn btn-danger">Delete Selected</button>
                            </div>
                        </div>

                    </div>
                </div>
</div>
</div>
</section>
<!-- Home Video Modal -->
<div class="modal fade " style="margin-top: 80px;" id="homeVideo" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <button type="button" class="btn btn-default text-right" data-dismiss="modal" onclick="pauseVid()">X</button>
      <div class="embed-responsive embed-responsive-16by9">
        <video id="thumbVideo" class="embed-responsive-item" controls="controls" poster="">
          <source src="" type="video/mp4"> 
        </video>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
    var table;
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });
    $(document).ready(function() {

        $(document).on("click",".process", function() {          
            $("#user_id_hidden").val($(this).attr("data-val"));
        });

        $('#deleteSel').click(function(){
            var favorite = [];
            $.each($(".delete_box:checked"), function(){
                favorite.push($(this).attr('data-val'));
            });
            if(favorite != "") {
                if (confirm('Are you sure you want to delete ?')) {
                    var ids =favorite.join(",");
                    $.post('<?php echo $path;?>/delete','ids='+ids,function(data){
                        window.location = '<?php echo $path;?>';
                    });
                }
            } else {
                alert("Please select item to delete.")
            }
        });
        $(document).on('click','.deleteSelSingle',function(e){
            e.preventDefault();   
            if (confirm('Are you sure you want to delete ?')) {
                var favorite = [];
                favorite.push($(this).attr('data-val'));
                var ids =favorite.join(",");
                $.post('<?php echo $path;?>/delete','ids='+ids,function(data){
                    window.location = '<?php echo $path;?>';
                });
            }
        });
        table = $('#data_table').DataTable({
                "processing": true, //Feature control the processing indicator.
                "serverSide": true, //Feature control DataTables' server-side processing mode.
                "order": [], //Initial no order.

                // Load data for the table's content from an Ajax source
                "ajax": {
                    "url": "<?php echo $path;?>/server_processing",
                    "type": "POST",
                   // "data":{"type" : <?php //echo "'".$type."'"?>}
                },
                "language": {
                    "processing": "<img src='<?php echo url('')?>/assets/images/loading.gif'>",
                    "search": '<i class="fa fa-search"></i>',
                    "searchPlaceholder": "Search",
                    "paginate": {
                        "previous": '<i class="fa fa-angle-double-left"></i>',
                        "next": '<i class="fa fa-angle-double-right"></i>'
                    }
                },
                "dom": '<"top"flp<"clear">>rt<"bottom"ip<"clear">>',
                "pageLength": <?php echo config('app.admin_records');?>,
                "lengthMenu": [ [10,20,30,50,100,-1], [10,20,30,50,100,"All"] ],
                //Set column definition initialisation properties.
                "columnDefs": [
                { 
                    "targets": [ 0,1 ], //first column / numbering column
                    "orderable": false, //set not orderable
                },
                { className: "actionss", "targets": [ 0 ] }, { className: "checkboxColumn", "targets": [ 1 ] }
                ],
            });
        $("#remember_me").parent().find('th').removeClass('sorting').addClass('sorting_disabled');
        $("#remember_me").click(function () {
            $(".delete_box").prop('checked', $(this).prop('checked'));
        });
        $(".delete_box").change(function(){
            if (!$(this).prop("checked")){
                $("#remember_me").prop("checked",false);
            }
        });
    });
  
    function start_loading(){
        $('#overlay').show();
    }
    function stop_loading() {
        $('#overlay').hide();
    }
    function showError(id,errMsg){
        if($.isArray(errMsg)){
            var errHtml = "<ul>";
            $.each( errMsg, function( key, value ) {
                errHtml +='<li>'+value+'</li>';
            });
            errHtml+= '</ul>';
            $("#" + id ).html(errHtml).show();
        }else{
            $('#' + id).html(errMsg).show();
        }
        setTimeout(function(){
            $("#" + id ).html('').hide('slow');
        }, 5000);
    }
    function showSuccess(id,msg,modal_id){
        $('#' + id).html(msg).show();
        setTimeout(function(){
            $("#" + id ).html('').hide('slow');
            $('#'+ modal_id).modal('hide');
        }, 5000);
    }

    $(document).on('click','.video_play', function(){
        $( this ).siblings('.video_thumb').trigger( "click" );
    });
    $(document).on('click','.video_thumb', function(){
        var url = $(this).attr('id');
        var vid = document.getElementById("thumbVideo");
        vid.src = url;
        // $('#thumbVideo').play();
        $("#thumbVideo")[0].autoplay = true;
    });

    $('#data_table').on('draw.dt', function() {
        $(".flaged_toggle").bootstrapToggle();
        $(".active_toggle").bootstrapToggle();
    });

    $(document).on('change','.flaged_toggle',function(){
                       var v_id=$(this).attr("data-id");
                 
                    if($(this).prop("checked")==true){
                        var enabled=0;
                        var status=1;
                        var msg="Flaged";
                        
                    }else{
                        var enabled=1;
                        var status=0;
                        var msg="Unflaged";
                    } 
                   swal({
                       title: "Are you sure?",
                       text: "You want to "+msg+" this Video !",
                       icon: "warning",
                       buttons: true,
                       dangerMode: true,
                       })
                       .then((willDelete) => {
                       if (willDelete) {
                          $.ajax(
                           {
                              type:"post",
                               url:"{{route('admin.flag_video')}}",
                               data:{"status":status,"id":v_id,"enabled":enabled},
                               error: function(xhr, status, error) {
                                   alert(status);
                                   alert(xhr.responseText);
                                    },
                               success:function(data)
                               {
                                   swal({
                                       // text: "Product added to Featured Listing ",
                                       title: data,
                                       button: "Close",
                                       icon:"success",
                                     });
                               },
                           });
                       }else {
                           if($(this).prop("checked")==true){
                               $(this).prop("checked",false);
                               $(this).parent(".toggle").removeClass("on").removeClass("btn-success").addClass("off").addClass("btn-danger");
                             
                           } else {
                               $(this).prop("checked",true);
                               $(this).parent(".toggle").removeClass("off").removeClass("btn-danger").addClass("on").addClass("btn-success");
                           }
                     
                         }
                   });
                   });

            $(document).on('change','.active_toggle',function(){
                       var v_id=$(this).attr("data-id");
                 
                    if($(this).prop("checked")==true){
                        var active=1;
                        var msg="Active";
                        
                    }else{
                        var active=0;
                        var msg="Inactive";
                    } 
                   swal({
                       title: "Are you sure?",
                       text: "You want to "+msg+" this Video !",
                       icon: "warning",
                       buttons: true,
                       dangerMode: true,
                       })
                       .then((willDelete) => {
                       if (willDelete) {
                          $.ajax(
                           {
                              type:"post",
                               url:"{{route('admin.active_video')}}",
                               data:{"active":active,"id":v_id},
                               error: function(xhr, status, error) {
                                   alert(status);
                                   alert(xhr.responseText);
                                    },
                               success:function(data)
                               {
                                   swal({
                                       // text: "Product added to Featured Listing ",
                                       title: data,
                                       button: "Close",
                                       icon:"success",
                                     });
                               },
                           });
                       }else {
                           if($(this).prop("checked")==true){
                               $(this).prop("checked",false);
                               $(this).parent(".toggle").removeClass("on").removeClass("btn-success").addClass("off").addClass("btn-danger");
                             
                           } else {
                               $(this).prop("checked",true);
                               $(this).parent(".toggle").removeClass("off").removeClass("btn-danger").addClass("on").addClass("btn-success");
                           }
                     
                         }
                   });
                   });
</script>
@endsection