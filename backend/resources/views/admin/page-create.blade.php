@extends('layouts.admin')
@section('content')
<?php
   if($action == 'edit'){
    $title = 'Edit Page';
    $readonly="";
}else if($action == 'add') {
    $title = 'Add Page';
    $readonly="";
}else if($action == 'view') {
    $title = 'View Page';
    $readonly='readonly';
}else{
    $title = 'Copy Page';
    $readonly="";
}
$path = route('admin.pages.index');
?>
<style>

.main_cat .select2-container--default .select2-selection--single {
    background-color: #fff; 
    border: 0px solid #aaa;
     border-radius: 0px;
     width : 100%;
     padding:5px;
}
.main_cat .select2-container--default .select2-selection--single .select2-selection__rendered {
    background: #fff;
    padding: 5px;
    height: 40px;
    border: 1px solid #ccc;
}
</style>
<section class="rightside-main">
	<div class="container-fluid">
        <div class="page-top">
            <div class="page-header borderless ">
                <h4><?php echo $title;?></h4>   
            </div>
            <div class="page-berdcrumb">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard')}}">
                            <i class="feather icon-home"></i>
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard')}}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.pages.index')}}">Pages Management</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="#"><?php echo $title;?></a>
                    </li>
                </ul>       
            </div>
        </div>
        <div class="card table-card ">
            <div class="row card-header borderless ">
                    <div class="col-md-8 col-lg-8">
                        <h3><?php echo $title;?></h3>
                    </div>
                </div>
           
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-12 col-md-12">
                                @if (count($errors) > 0)
                                <div class="alert alert-danger">
                                    <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                    </ul>
                                </div>
                                @endif
                            </div>
                        </div>
                        <?php
                                if($action == 'edit'){?>
                                   <form class="form-horizontal" role="form" action="{{url( config('app.admin_url') .'/pages/'.$id)}}" method="post" enctype="multipart/form-data">
                                    {{ method_field('PUT') }}
                                <?php }else {?>
                                    <form class="form-horizontal" role="form" action="{{url( config('app.admin_url') .'/pages')}}" method="post" enctype="multipart/form-data">
                                <?php }?>
                                    {{ csrf_field() }}
                                    <div class="row">
                                        <div class="col-lg-12 col-md-12">
                                            
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Type <span class="requried">*</span></label>
                                                <div class="col-sm-10">
                                                    <?php
                                                    if( old('type')!='' ){
                                                        $type = old('type');
                                                    }
                                                    else if( isset($page->type) && $page->type != ''){
                                                        $type = $page->type;
                                                    }else{
                                                        $type = '';
                                                    }
                                                    ?>
                                                    <select class="form-control" name="type">
                                                        <option name="type" value="">--Select--</option>
                                                        <option value="privacy-policy" <?php echo ($type=='privacy-policy') ? 'selected' : ''; ?> >Privacy Policy</option>
                                                        <option value="terms" <?php echo ($type=='terms') ? 'selected' : ''; ?>>Terms of Use</option>
                                                        <option value="data-delete" <?php echo ($type=='data-delete') ? 'selected' : ''; ?>>Data Deletion</option>
                                                    </select>
                                                    <!-- <input type="text" class="form-control" name="type" value="<?php //echo $type;?>" {{$readonly}}> -->
                                                </div>
                                            </div> 
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Title <span class="requried">*</span></label>
                                                <div class="col-sm-10">
                                                    <?php
                                                    if( old('title')!='' ){
                                                        $title = old('title');
                                                    }
                                                    else if( isset($page->title) && $page->title != ''){
                                                        $title = $page->title;
                                                    }else{
                                                        $title = '';
                                                    }
                                                    ?>
                                                    <input type="text" class="form-control" name="title" value="<?php echo $title;?>" {{$readonly}}>
                                                </div>
                                            </div> 
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Content <span class="requried">*</span></label>
                                                <div class="col-sm-10">
                                                    <?php
                                                    if( old('content')!='' ){
                                                        $content = old('content');
                                                    }
                                                    else if( isset($page->content) && $page->content != ''){
                                                        $content = $page->content;
                                                    }else{
                                                        $content = '';
                                                    }
                                                    ?>
                                                    <textarea name="content" class="form-control" >{{$content}}</textarea>
                                                    <!-- <input type="text" class="form-control" name="title" value="<?php //echo $title;?>" {{$readonly}}> -->
                                                </div>
                                            </div> 
                                            
                                        </div>
                                    </div>
                                    <div class="row margin-tp-bt-10">
                                        <div class="col-lg-12 col-md-12" <?php if($action == 'view'){ echo "style='display:none'"; }?>>                                        
                                           <button type="submit" class="btn btn-primary">Submit</button>
                                        </div>    
                                    </div> 
                                </form>
                      </div>
                </div>
</div>
</div>
</section>
<script type="text/javascript">
  $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });
    $(document).ready(function() {

        $(document).on("change","#main_cat_id", function() {          
            var main_cat=$(this).val();
            $.post('<?php echo $path;?>/select_cat','main_cat='+main_cat,function(data){
                $('#cat_id').html(data);
                    //window.location = '<?php //echo $path;?>';
                });
        });
        $('#main_cat_id').select2();
        $('#cat_id').select2();
    });
</script>
@endsection