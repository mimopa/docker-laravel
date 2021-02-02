<div class="tab-setting">
    <a href="{{ route('admin.settings') }}" class="btn btn-tab-setting col-md-12"><i class="fa fa-gear"></i> Global Settings</a>
    <a href="{{ route('admin.ad_settings') }}" class="btn btn-tab-setting col-md-12"><i class="fa fa-dollar"></i> Ads Settings</a>
    <a href="{{ route('admin.mail_settings') }}" class="btn btn-tab-setting col-md-12"><i class="fa fa-envelope"></i> Mail Settings </a>
    <a href="{{route('admin.nsfw_settings')}}" class="btn btn-tab-setting col-md-12"><i class="fa fa-user-secret" style="font-size: 18px;"></i> Video Moderation Settings</a>
    <a href="{{ route('admin.change_password.index') }}" class="btn btn-tab-setting col-md-12"><i class="fa fa-lock"></i> Change Password</a>
    <a href="{{ route('admin.home_settings') }}" class="btn btn-tab-setting col-md-12"><i class="fa fa-home"></i> Home Screen</a>
    <a href="{{ route('admin.social_settings') }}" class="btn btn-tab-setting col-md-12"><i class="fa fa-globe"></i> Social Login</a>
    <a href="{{ route('admin.pusher_settings') }}" class="btn btn-tab-setting col-md-12"><i class="fa fa-bell"></i> Notification Settings</a>
    <a href="{{ route('admin.social_media_links') }}" class="btn btn-tab-setting col-md-12"><i class="fa fa-link"></i> Social Media Links</a>
    <a href="{{ route('admin.app_settings') }}" class="btn btn-tab-setting col-md-12"><i class="fa fa-cogs"></i> App Settings</a>
    <a href="{{ route('admin.storage_settings') }}" class="btn btn-tab-setting col-md-12"><i class="fa fa-database"></i> Storage Settings</a>
</div>
<script type="text/javascript">
    $(document).ready(function(){
        var current = location.pathname;
        // var current_arr=current.split('/');
        // alert(current);
        // var url=current_arr[1]+'/'+current_arr[2];
        $('.tab-setting a').each(function(){
            var $this = $(this);
            // if the current path is like this link, make it active
            if($this.attr('href').indexOf(current) !== -1){
                $this.addClass('active');
            }
        })
    });
</script>
<style type="text/css">
    .btn-tab-setting {
        background-color: white !important;
        /* border: 2px solid black !important; */
        color: black !important;
        /* padding:10px 20px; */
        padding: 13px 20px;
        border-bottom: 1px solid #ccc;
        line-height: 20px;
        text-align:left;
        /* margin: 10px; */

    }
    .btn-tab-setting.active {
        background-color: black !important;
        border: 2px solid black !important;
        color: white !important;
    }
    .tab-setting{
    box-shadow: 0 2px 10px -1px rgba(69, 90, 100, 0.3);
    border: none !important;
    padding: 10px 10px;
    border-radius: 8px;
    background: white;
    height:100%;
}
</style>