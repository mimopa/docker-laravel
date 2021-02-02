@extends('layouts.admin')
@section('content')
<?php
if($action == 'edit'){
    $title = 'Settings';
    $readonly="";
}
?>
<style>
.card{
    min-height:450px;
    margin-top: 0px;
}
</style>
<section class="rightside-main">
    <div class="container-fluid">
        <div class="page-top">
            <div class="page-header borderless">
                <h4><?php echo $title; ?></h4>   
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
                        <a href="{{ route('admin.categories.index')}}">{{$title}}</a>
                    </li>
                </ul>       
            </div>
        </div>
        <div class="row">
            <div class="col-md-3 pr-0">
                @include('includes.admin.settings')
            </div>
            <div class="col-md-9 pl-1">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card table-card">
                            <div class="card-header borderless">
                            <h3><?php echo $title;?></h3>
                                
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-12 col-md-12">
                                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                                            <li class="nav-item">
                                                <a class="nav-link <?php echo ($type=='G') ? 'active' : ''; ?>" id="g_Setting_tab" data-toggle="tab" href="#g_settings" role="tab" aria-controls="home" aria-selected="true" data-type="G"> <i class="fa fa-cog"></i> General</a>
                                            </li>
                                        </ul>
                                        <br />
                                        @if (count($errors) > 0)
                                        <div class="alert alert-danger">
                                            <ul>
                                                @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        @endif
                                        @if($message = Session::get('success'))
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
                                <!-- <div class="row"> -->
                                <div class="tab-content" id="myTabContent">
                                <div class="tab-pane fade show active" id="g_settings" role="tabpanel" aria-labelledby="g_Setting_tab">
                                        <form role="form" action="{{url( config('app.admin_url') .'/settings-update')}}" method="post" enctype="multipart/form-data">
                                        {{ csrf_field() }}
                                        <div class="row">
                                            <div class="col-lg-12 col-md-12">
                                                <div class="form-group row">
                                                    <label class="col-sm-2 col-form-label">Company Name</label>
                                                    <div class="col-sm-5">
                                                        <?php
                                                        if(old('site_name')!=''){
                                                            $site_name = old('site_name');
                                                        }
                                                        else if( isset($settings->site_name) && $settings->site_name != ''){
                                                            $site_name = $settings->site_name;
                                                        }else{
                                                            $site_name = '';
                                                        }
                                                        ?>
                                                        <input type="text" class="form-control" name="site_name" value="<?php echo $site_name; ?>">
                                                    </div> 
                                                </div> 
                                                <div class="form-group row">
                                                    <label class="col-sm-2 col-form-label">Address</label>
                                                    <div class="col-sm-5">
                                                        <?php
                                                        if(old('site_address')!=''){
                                                            $site_address = old('site_address');
                                                        }
                                                        else if( isset($settings->site_address) && $settings->site_address != ''){
                                                            $site_address = $settings->site_address;
                                                        }else{
                                                            $site_address = '';
                                                        }
                                                        ?>
                                                        <textarea name="site_address" class="form-control">{{$site_address}}</textarea>
                                                    </div> 
                                                </div> 
                                                <div class="form-group row">
                                                    <label class="col-sm-2 col-form-label">Phone</label>
                                                    <div class="col-sm-5">
                                                        <?php
                                                        if(old('site_phone')!=''){
                                                            $site_phone = old('site_phone');
                                                        }
                                                        else if( isset($settings->site_phone) && $settings->site_phone != ''){
                                                            $site_phone = $settings->site_phone;
                                                        }else{
                                                            $site_phone = '';
                                                        }
                                                        ?>
                                                        <input type="text" class="form-control" name="site_phone" value="<?php echo $site_phone; ?>">
                                                    </div> 
                                                </div> 
                                                <div class="form-group row">
                                                    <label class="col-sm-2 col-form-label">Email</label>
                                                    <div class="col-sm-5">
                                                        <?php
                                                        if(old('site_email')!=''){
                                                            $site_email = old('site_email');
                                                        }
                                                        else if( isset($settings->site_email) && $settings->site_email != ''){
                                                            $site_email = $settings->site_email;
                                                        }else{
                                                            $site_email = '';
                                                        }
                                                        ?>
                                                        <input type="text" class="form-control" name="site_email" value="<?php echo $site_email; ?>">
                                                    </div> 
                                                </div> 
                                                <div class="form-group row">
                                                    <label class="col-sm-2 col-form-label">Logo</label>
                                                    <div class="col-sm-5">
                                                        <?php
                                                        if( old('site_logo')!='' ){
                                                            $site_logo = old('site_logo');
                                                        }
                                                        else if( isset($settings->site_logo) && $settings->site_logo != ''){
                                                            $site_logo = $settings->site_logo;
                                                        }else{
                                                            $site_logo = '';
                                                        }
                                                        ?>
                                                        @if($action!='view') <input type="file" class="form-control" name="site_logo"> @endif
                                                        <input type="hidden" class="form-control" name="old_site_logo" value="<?php echo $site_logo;?>" readonly>
                                                        @if($site_logo!="")
                                                        <img src="<?php echo asset(Storage::url('public/uploads/logos/'.$site_logo));?>" width="130px">
                                                        @endif
                                                    </div>  
                                                </div>  
                                                <div class="form-group row">
                                                    <label class="col-sm-2 col-form-label">Watermark <b>(Max : 200x70)</b></label>
                                                    <div class="col-sm-5">
                                                        <?php
                                                            if( old('watermark')!='' ){
                                                                $watermark = old('watermark');
                                                            }
                                                            else if( isset($settings->watermark) && $settings->watermark != ''){
                                                                $watermark = $settings->watermark;
                                                            }else{
                                                                $watermark = '';
                                                            }
                                                        ?>
                                                        @if($action!='view') <input type="file" class="form-control" name="watermark"> @endif
                                                        <input type="hidden" class="form-control" name="old_watermark" value="<?php echo $watermark;?>" readonly>
                                                        @if($watermark!="")
                                                        <img src="<?php echo asset('storage/uploads/logos/'.$watermark);?>" width="130px">
                                                        @endif
                                                    </div>  
                                                </div>   
                                            </div>
                                        </div>
                                        <div class="row">
                                            <input type="hidden" name="id" value="<?php echo (isset($id)) ? $id : 0; ?>">
                                            <input type="hidden" name="type" id="setting_type" value="G">
                                            <div class="col-lg-12 col-md-12" <?php if($action == 'view'){ echo "style='display:none'"; }?>>                                        
                                            <button type="submit" class="btn btn-primary">Submit</button>
                                        </div>    
                                    </div> 
                                    </form>  
                                </div>
                                <!-- <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">...</div> -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
             </div>
         </div>
     </div>
 </section>
 <script type="text/javascript">
    $(function()
    {
        $('.nav-link').on('click',function(){
            var val=$(this).attr("data-type");
            $('#setting_type').val(val);
        });
    });
</script>
@endsection