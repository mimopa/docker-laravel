@extends('layouts.admin')
@section('content')
<?php
if($action == 'edit'){
    $title = 'Ads Settings';
    $readonly="";
}
?>
<style>
	.panel-body {

       border-radius: 5px;
       padding-left: 20px;
       padding-bottom: 20px;
       padding-right: 20px;
       padding-top: 20px;
       margin-top: 20px;
       box-shadow: 0 0 5px 1px #a9a9a9;
   }
   .panel-title {
      position: absolute;
      background: white;
      margin-top: -30px;
      padding-left: 10px;
      font-weight: bold;
      padding-right: 10px;
  }
  label.title {
   color: black;
   font-weight: 500;
   font-size: 15px;
}
.custom-btn {
  font-size: 13px;
  /* padding: 3px 8px 4px 8px; */
}
.modal {
  margin-top: 50px;
}
.card{
    min-height:450px;
    margin-top: 0px;
}
.tab-setting{
    box-shadow: 0 2px 10px -1px rgba(69, 90, 100, 0.3);
    border: none !important;
    padding: 10px 10px;
    border-radius: 8px;
    background: white;
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
                                                <a class="nav-link <?php echo ($type=="G") ? 'active' : ''; ?>" id="google_ads_tab" data-toggle="tab" href="#google_ads" role="tab" aria-controls="home" aria-selected="true" data-type="G"> <i class="fa fa-cog"></i> Google Ads Mob</a>
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
                                    <div class="tab-pane fade show active" id="google_ads" role="tabpanel" aria-labelledby="google_ads_tab">
                                
                                    <!-- <div class="row"> -->
                                    <form role="form" action="{{url( config('app.admin_url') .'/ad-settings-update')}}" method="post" enctype="multipart/form-data">
                                        {{ csrf_field() }}
                                        <div class="row">
                                            <div class="col-lg-12 col-md-12">
                                                <div class="panel-body" style="padding-bottom: 30px;">
                                                    <div class="row">
                                                        
                                                        <div class="col-lg-6 col-md-6">
                                                            <label class="panel-title">ADMOB SETTINGS</label>
                                                            <div class="form-group label-floating is-empty" style="padding: 0px;margin: 0px;">
                                                                <div  style="padding-top:20px"></div>
                                                                <label class="control-label title">Android App Id</label>
                                                                <?php
                                                                if(old('android_app_id')!=''){
                                                                    $android_app_id = old('android_app_id');
                                                                }
                                                                else if( isset($adSettings->android_app_id) && $adSettings->android_app_id != ''){
                                                                    $android_app_id = $adSettings->android_app_id;
                                                                }else{
                                                                    $android_app_id = '';
                                                                }
                                                                ?>
                                                                <input type="text" name="android_app_id" class="form-control" value="{{$android_app_id}}">
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6 col-md-6">
                                                            <div class="form-group label-floating is-empty" style="padding: 0px;margin: 0px;">
                                                                <div  style="padding-top:20px"></div>
                                                                <label class="control-label title">IOS App Id</label>
                                                                <?php
                                                                if(old('ios_app_id')!=''){
                                                                    $ios_app_id = old('ios_app_id');
                                                                }
                                                                else if( isset($adSettings->ios_app_id) && $adSettings->ios_app_id != ''){
                                                                    $ios_app_id = $adSettings->ios_app_id;
                                                                }else{
                                                                    $ios_app_id = '';
                                                                }
                                                                ?>
                                                                <input type="text" name="ios_app_id" class="form-control" value="{{$ios_app_id}}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div> 
                                                <div style="padding-top:20px"></div>
                                                <div class="panel-body">
                                                    <div class="row">
                                                        <div class="col-lg-12 col-md-12">
                                                            <label class="panel-title">INTERSTITIAL AD</label>
                                                            <div class="row">
                                                                <div class="col-md-12 text-right">
                                                                    <input type="button" class="custom-btn btn btn-primary" value="View Interstitial Ad" data-toggle="modal" data-target="#interstitialAdModal"/>
                                                                </div>
                                                                <div class="col-sm-6">
                                                                    <div class="form-group label-floating is-empty" style="padding: 0px;margin: 0px;">
                                                                        <label class="control-label title">Android Unit Id</label>
                                                                        <?php
                                                                        if(old('android_interstitial_app_id')!=''){
                                                                            $android_interstitial_app_id = old('android_interstitial_app_id');
                                                                        }
                                                                        else if( isset($adSettings->android_interstitial_app_id) && $adSettings->android_interstitial_app_id != ''){
                                                                            $android_interstitial_app_id = $adSettings->android_interstitial_app_id;
                                                                        }else{
                                                                            $android_interstitial_app_id = '';
                                                                        }
                                                                        ?>
                                                                        <input type="text" name="android_interstitial_app_id" class="form-control" value="{{$android_interstitial_app_id}}">
                                                                    </div>
                                                                </div>
                                                                <div class="col-sm-6">
                                                                    <div class="form-group label-floating is-empty" style="padding: 0px;margin: 0px;">
                                                                        <label class="control-label title">IOS Unit Id</label>
                                                                        <?php
                                                                        if(old('ios_interstitial_app_id')!=''){
                                                                            $ios_interstitial_app_id = old('ios_interstitial_app_id');
                                                                        }
                                                                        else if( isset($adSettings->ios_interstitial_app_id) && $adSettings->ios_interstitial_app_id != ''){
                                                                            $ios_interstitial_app_id = $adSettings->ios_interstitial_app_id;
                                                                        }else{
                                                                            $ios_interstitial_app_id = '';
                                                                        }
                                                                        ?>
                                                                        <input type="text" name="ios_interstitial_app_id" class="form-control" value="{{$ios_interstitial_app_id}}">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div style="padding-top:20px"></div>
                                                            <div class="row">
                                                                <div class="col-sm-6">
                                                                    <div class="form-group label-floating is-empty" style="padding: 0px;margin: 0px;">
                                                                        <label class="control-label title">Show On</label>
                                                                        <?php
                                                                        if(old('interstitial_show_on')!=''){
                                                                            $interstitial_show_on = old('interstitial_show_on');
                                                                        }
                                                                        else if( isset($adSettings->interstitial_show_on) && $adSettings->interstitial_show_on != ''){
                                                                            $interstitial_show_on = $adSettings->interstitial_show_on;
                                                                        }else{
                                                                            $interstitial_show_on = '';
                                                                        }
                                                                        if($interstitial_show_on!="") {
                                                                            $interstitial_show_on_arr = explode(",",$interstitial_show_on); 
                                                                        } else {
                                                                            $interstitial_show_on_arr = array();    
                                                                        }
                                                                        ?>
                                                                        <select class="form-control select2Field" name="interstitial_show_on[]" multiple="">
                                                                            <option value="1" <?php echo ( in_array('1',$interstitial_show_on_arr) == TRUE) ? 'selected' : '';?>>Home Page</option>
                                                                            <option value="2" <?php echo ( in_array('2',$interstitial_show_on_arr) == TRUE) ? 'selected' : '';?>>User Detail Page</option>
                                                                            <option value="3" <?php echo ( in_array('3',$interstitial_show_on_arr) == TRUE) ? 'selected' : '';?>>Search Page</option>
                                                                        </select>
                                                                    </div>  
                                                                </div>
                                                                <!-- <div class="col-sm-6">
                                                                    <div class="form-group label-floating is-empty" style="padding: 0px;margin: 0px;">
                                                                        <label class="control-label title">How to look this ad on your device.</label>
                                                                        &nbsp;<input type="button" class="custom-btn btn btn-primary" value="Interstitial Ad View" data-toggle="modal" data-target="#interstitialAdModal"/>
                                                                    </div>  
                                                                </div> -->
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div> 
                                                <div style="padding-top:20px"></div>
                                                <div class="panel-body">
                                                    <div class="row">
                                                        <div class="col-lg-12 col-md-12">
                                                            <label class="panel-title">BANNER AD</label>
                                                            <div class="row">
                                                                <div class="col-md-12 text-right">
                                                                    <input type="button" class="custom-btn btn btn-primary" value="View Banner Ad" data-toggle="modal" data-target="#bannerAdModal"/>
                                                                </div>
                                                                <div class="col-sm-6">
                                                                    <div class="form-group label-floating is-empty" style="padding: 0px;margin: 0px;">
                                                                        <label class="control-label title">Android Unit Id</label>
                                                                        <?php
                                                                        if(old('android_banner_app_id')!=''){
                                                                            $android_banner_app_id = old('android_banner_app_id');
                                                                        }
                                                                        else if( isset($adSettings->android_banner_app_id) && $adSettings->android_banner_app_id != ''){
                                                                            $android_banner_app_id = $adSettings->android_banner_app_id;
                                                                        }else{
                                                                            $android_banner_app_id = '';
                                                                        }
                                                                        ?>
                                                                        <input type="text" name="android_banner_app_id" class="form-control" value="{{$android_banner_app_id}}">
                                                                    </div>
                                                                </div>
                                                                <div class="col-sm-6">
                                                                    <div class="form-group label-floating is-empty" style="padding: 0px;margin: 0px;">
                                                                        <label class="control-label title">IOS Unit Id</label>
                                                                        <?php
                                                                        if(old('ios_banner_app_id')!=''){
                                                                            $ios_banner_app_id = old('ios_banner_app_id');
                                                                        }
                                                                        else if( isset($adSettings->ios_banner_app_id) && $adSettings->ios_banner_app_id != ''){
                                                                            $ios_banner_app_id = $adSettings->ios_banner_app_id;
                                                                        }else{
                                                                            $ios_banner_app_id = '';
                                                                        }
                                                                        ?>
                                                                        <input type="text" name="ios_banner_app_id" class="form-control" value="{{$ios_banner_app_id}}">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div style="padding-top:20px"></div>
                                                            <div class="row">
                                                                <div class="col-sm-6">
                                                                    <div class="form-group label-floating is-empty" style="padding: 0px;margin: 0px;">
                                                                        <label class="control-label title">Show On</label>
                                                                        <?php
                                                                        if(old('banner_show_on')!=''){
                                                                            $banner_show_on = old('banner_show_on');
                                                                        }
                                                                        else if( isset($adSettings->banner_show_on) && $adSettings->banner_show_on != ''){
                                                                            $banner_show_on = $adSettings->banner_show_on;
                                                                        }else{
                                                                            $banner_show_on = '';
                                                                        }
                                                                        if($banner_show_on!="") {
                                                                            $banner_show_on_arr = explode(",",$banner_show_on); 
                                                                        } else {
                                                                            $banner_show_on_arr = array();  
                                                                        }
                                                                        ?>
                                                                        <select class="form-control select2Field" name="banner_show_on[]" multiple="">
                                                                            <option value="1" <?php echo ( in_array('1',$banner_show_on_arr) == TRUE) ? 'selected' : '';?>>Home Page</option>
                                                                            <option value="2" <?php echo ( in_array('2',$banner_show_on_arr) == TRUE) ? 'selected' : '';?>>User Detail Page</option>
                                                                            <option value="3" <?php echo ( in_array('3',$banner_show_on_arr) == TRUE) ? 'selected' : '';?>>Search Page</option>
                                                                        </select>
                                                                    </div>  
                                                                </div>
                                                                <!-- <div class="col-sm-6">
                                                                    <div class="form-group label-floating is-empty" style="padding: 0px;margin: 0px;">
                                                                        <label class="control-label title">How to look this ad on your device.</label>
                                                                        &nbsp;<input type="button" class="custom-btn btn btn-primary" value="Banner Ad View" data-toggle="modal" data-target="#bannerAdModal"/>
                                                                    </div>
                                                                </div> -->
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div> 
                                                <div style="padding-top:20px"></div>
                                                <div class="panel-body">
                                                    <label class="panel-title">REWARDED VIDEO AD</label>
                                                    <div class="row">
                                                        <div class="col-md-12 text-right">
                                                            <input type="button" class="custom-btn btn btn-primary" value="View Rewarded Video Ad" data-toggle="modal" data-target="#videoAdModal"/>
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <div class="form-group label-floating is-empty" style="padding: 0px;margin: 0px;">
                                                                <label class="control-label title">Android Unit Id</label>
                                                                <?php
                                                                if(old('android_video_app_id')!=''){
                                                                    $android_video_app_id = old('android_video_app_id');
                                                                }
                                                                else if( isset($adSettings->android_video_app_id) && $adSettings->android_video_app_id != ''){
                                                                    $android_video_app_id = $adSettings->android_video_app_id;
                                                                }else{
                                                                    $android_video_app_id = '';
                                                                }
                                                                ?>
                                                                <input type="text" name="android_video_app_id" class="form-control" value="{{$android_video_app_id}}">
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <div class="form-group label-floating is-empty" style="padding: 0px;margin: 0px;">
                                                                <label class="control-label title">IOS Unit Id</label>
                                                                <?php
                                                                if(old('ios_video_app_id')!=''){
                                                                    $ios_video_app_id = old('ios_video_app_id');
                                                                }
                                                                else if( isset($adSettings->ios_video_app_id) && $adSettings->ios_video_app_id != ''){
                                                                    $ios_video_app_id = $adSettings->ios_video_app_id;
                                                                }else{
                                                                    $ios_video_app_id = '';
                                                                }
                                                                ?>
                                                                <input type="text" name="ios_video_app_id" class="form-control" value="{{$ios_video_app_id}}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div style="padding-top:20px"></div>
                                                    <div class="row">
                                                        <div class="col-sm-6">
                                                            <div class="form-group label-floating is-empty" style="padding: 0px;margin: 0px;">
                                                                <label class="control-label title">Show On</label>
                                                                <?php
                                                                if(old('video_show_on')!=''){
                                                                    $video_show_on = old('video_show_on');
                                                                }
                                                                else if( isset($adSettings->video_show_on) && $adSettings->video_show_on != ''){
                                                                    $video_show_on = $adSettings->video_show_on;
                                                                }else{
                                                                    $video_show_on = '';
                                                                }
                                                                if($video_show_on!="") {
                                                                    $video_show_on_arr = explode(",",$video_show_on);   
                                                                } else {
                                                                    $video_show_on_arr = array();   
                                                                }
                                                                ?>
                                                                <select class="form-control select2Field" name="video_show_on[]" multiple>
                                                                    <option value="1" <?php echo ( in_array('1',$video_show_on_arr) == TRUE) ? 'selected' : '';?>>Home Page</option>
                                                                    <option value="2" <?php echo ( in_array('2',$video_show_on_arr) == TRUE) ? 'selected' : '';?>>User Detail Page</option>
                                                                    <option value="3" <?php echo ( in_array('3',$video_show_on_arr) == TRUE) ? 'selected' : '';?>>Search Page</option>
                                                                </select>
                                                            </div>  
                                                        </div>
                                                        <!-- <div class="col-sm-6">
                                                            <div class="form-group label-floating is-empty" style="padding: 0px;margin: 0px;">
                                                                <label class="control-label title">How to look this ad on your device.</label>
                                                                &nbsp;<input type="button" class="custom-btn btn btn-primary" value="Rewarded Video Ad View" data-toggle="modal" data-target="#videoAdModal"/>
                                                            </div>
                                                        </div> -->
                                                    </div>
                                                </div>  
                                            </div>
                                        </div>
                                        <div style="padding-top:20px"></div>
                                        <div class="row">
                                            <input type="hidden" name="id" value="<?php echo (isset($id)) ? $id : 0; ?>">
                                            <input type="hidden" name="type" id="setting_type" value="G">
                                            <div class="col-lg-12 col-md-12" <?php if($action == 'view'){ echo "style='display:none'"; }?>>
                                            <button type="submit" class="btn btn-primary">Submit</button>
                                        </div>    
                                    </div> 
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
               </div>
           </div>
       </div>
   </section>
   <div id="bannerAdModal" class="modal fade" role="dialog">
      <div class="modal-dialog" style="width: 405px;">
        <!-- Modal content-->
        <div class="modal-content">
          <div class="modal-body">
            <div class="bannerImg">
               <img src="{{asset('imgs/banner-ad.jpeg')}}" height="800" style="display: table;margin: auto;"/>
           </div>
       </div>
   </div>

</div>
</div>

<div id="interstitialAdModal" class="modal fade" role="dialog">
  <div class="modal-dialog" style="width: 405px;">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-body">
        <div class="bannerImg">
        	<img src="{{asset('default/ins-ad.jpeg')}}" height="800" style="display: table;margin: auto;"/>
        </div>
    </div>
</div>

</div>
</div>

<div id="videoAdModal" class="modal fade" role="dialog">
  <div class="modal-dialog" style="width: 405px;">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-body">
        <div class="bannerImg">
        	<img src="{{asset('imgs/video-ad.jpeg')}}" height="800" style="display: table;margin: auto;"/>
        </div>
    </div>
</div>

</div>
</div>
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