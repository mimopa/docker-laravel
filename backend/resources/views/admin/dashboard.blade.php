@extends('layouts.admin')
@section('content')

<?php 
$graph=array();
foreach($d_arr as $k => $v){ 
  $graph[]='{
    "month": "'.$k.'",
    "value": '.$v.'
  }';
}
?>
<style type="text/css">
  .widget,
  .widget.widget-note,
  .widget-note {
    background-color: #f3fbf7;
    max-width: 100%;
    padding: 14px;
    margin: 35px 0;
    border-radius: 9px;
    overflow-wrap: break-word;
    border-left: 5px solid #5ac891;
    font-size: 1rem;
    color: rgba(0, 0, 0, .87);
  }

  .widget.widget-warning {
    background-color: #f2564d;
    border-left: 5px solid #6b0d08;
    color: white;
  }

  .widget.widget-important {
    background-color: #fef7ed;
    border-left: 5px solid #f3a12c;
  }
</style>
<script>
  $(document).ready(function() {
  // seo ecommerce start
  $(function() {

    var chart = AmCharts.makeChart("r-barchart", {
      "type": "serial",
      "theme": "light",
      "marginTop": 0,
      "marginRight": 0,
      "dataProvider": [<?php echo implode(', ',$graph); ?>],
      "valueAxes": [{
        "axisAlpha": 0,
        "gridAlpha": 0,
        "dashLength": 6,
        "position": "left"
      }],
      "graphs": [{
        "id": "g1",
        "balloonText": "[[category]]<br><b><span style='font-size:14px;'>[[value]]</span></b>",
        "bullet": "round",
        "bulletSize": 8,
        "fillAlphas": 0.1,
        "lineColor": "#448aff",
        "lineThickness": 2,
        "negativeLineColor": "#ff5252",
        "type": "smoothedLine",
        "valueField": "value"
      }],
      "chartScrollbar": {
        "graph": "g1",
        "gridAlpha": 0,
        "color": "#888888",
        "scrollbarHeight": 55,
        "backgroundAlpha": 0,
        "selectedBackgroundAlpha": 0.1,
        "selectedBackgroundColor": "#888888",
        "graphFillAlpha": 0,
        "autoGridCount": true,
        "selectedGraphFillAlpha": 0,
        "graphLineAlpha": 0.2,
        "graphLineColor": "#c2c2c2",
        "selectedGraphLineColor": "#888888",
        "selectedGraphLineAlpha": 1
      },
      "chartCursor": {
        "categoryBalloonDateFormat": "YYYY-MM",
        "cursorAlpha": 0,
        "valueLineEnabled": true,
        "valueLineBalloonEnabled": true,
        "valueLineAlpha": 0.5,
        "fullWidth": true
      },
      "dataDateFormat": "YYYY-MM",
      "categoryField": "month",
      "categoryAxis": {
        "minPeriod": "YYYY-MM",
        "gridAlpha": 0,
        "parseDates": false,
      },
    });
    chart.zoomToIndexes(Math.round(chart.dataProvider.length * 0.1), Math.round(chart.dataProvider.length * 1));
  });
    // seo ecommerce end
  });
</script>
<section>
  <div class="container-fluid">
    <div class="row" style="width:100%;">
      <div class="col-lg-4 ani">
        <a href="{{ route('admin.candidates.show',[1]) }}">
          <div class="box1 ">
            <div class="box1-dtl blue-text">
              <div class="row">
                <div class="col-md-8">
                  <h4>Total Users</h4>
                </div>
                <div class="col-md-4">
                  <h2 class="">{{$total_users}}</h2>
                </div>
              </div>
              <span> Registered User</span>
            </div>
            <div class="subscribers">
              <div class="row box1-bottom ">
                <div class="col-lg-6">
                  <h4>{{$total_active_candidates}}</h4>
                  <p>Active</p>
                </div>
                <div class="col-lg-6">
                  <h4>{{$total_pending_candidates}}</h4>
                  <p>Inactive</p>
                </div>

              </div>
            </div>
          </div>
          <!-- 
        <div class="card revenue">                          
          <div>
            <h6>Total Users</h6>
            <h2>{{$total_users}}</h2>
            <p>Active</p>
            <i class="fa fa-users" aria-hidden="true"></i>
          </div>                            
        </div>  -->
        </a>
      </div>

      <div class="col-lg-4 ani">
        <a href="{{ route('admin.videos.index') }}">
          <div class="box1 ">
            <div class="box1-dtl green-text">
              <div class="row">
                <div class="col-md-8">
                  <h4>Total Videos</h4>
                </div>
                <div class="col-md-4">
                  <h2 class="">{{$total_videos}}</h2>
                </div>
              </div>
              <span> Published Videos</span>
            </div>
            <div class="followers">
              <div class="row box1-bottom ">
                <div class="col-lg-4">
                  <h4>{{$total_active_videos}}</h4>
                  <p>Active</p>
                </div>
                <div class="col-lg-4">
                  <h4>{{$total_inactive_videos}}</h4>
                  <p>Disabled</p>
                </div>
                <div class="col-lg-4">
                  <h4>{{$total_flagged_videos}}</h4>
                  <p>Flagged</p>
                </div>
              </div>
            </div>
          </div>
        </a>
      </div>

      <div class="col-lg-4 ani">
        <a href="{{ route('admin.sounds.index')}}">
          <div class="box1 ">
            <div class="box1-dtl red-text">
              <div class="row">
                <div class="col-md-8">
                  <h4>User Engagement</h4>
                </div>
                <div class="col-md-4">
                  <h2 class="">{{$total_likes+ $total_comments+ $total_views}}</h2>
                </div>
              </div>
              <span>Engagement</span>
            </div>
            <div class="business">
              <div class="row box1-bottom ">
                <div class="col-lg-4">
                  <h4>{{$total_likes}}</h4>
                  <p>Likes</p>
                </div>
                <div class="col-lg-4">
                  <h4>{{$total_comments}}</h4>
                  <p>Comments</p>
                </div>
                <div class="col-lg-4">
                  <h4>{{$total_views}}</h4>
                  <p>Views</p>
                </div>
              </div>
            </div>
          </div>
        </a>
      </div>
    </div>
  </div>
</section>

<div class="clearfix"></div>
<br>

<section>
  <div class="container-fluid">
    <div class="row">
      <div class="col-lg-6">
        <div class="card table-card">
          <div class="card-header borderless ">
            <h3>Registration Analytics</h3>
          </div>
          <div class="card-body">
            <div id="r-barchart" style="height: 375px"></div>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="card table-card" style="height: 484px;">
          <div class="card-header borderless ">
            <h3>Version Status <i class="fa fa-refresh"></i></h3>
          </div>
          <div class="card-body">
            @if (session('success'))
            <div class="alert alert-success" role="alert">
              {{ session('success') }}
            </div>
            @endif
            <?php Session::forget('success'); ?>
            <div class="row versions">
              <div class="col-md-6 ">
                <div class="card orders ani">
                  <h6>Current Version</h6>
                  <h2>{{$current_version}}</h2>
                </div>
              </div>

              <div class="col-md-6">
                <div class="card revenue ani">
                  <h6>Latest Version</h6>
                  <h2>{{$latest_version}}</h2>
                </div>
              </div>
            </div>
            <br />
            @if($current_version != $latest_version)
            <div class="">
              <div class="row">
                <div class="col-md-8 offset-md-2">
                  <button id="add" class="btn btn-primary btn btn-lg btn-block" data-toggle="modal"
                    data-target="#exampleModal"> <i class="fa fa-refresh"></i> Update Now</button>
                </div>

              </div>
              <!-- Modal -->
              <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                aria-hidden="true">
                <div class="modal-dialog" role="document" style="top:100px">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h3 class="modal-title" id="exampleModalLabel">Check For updates</h3>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <div class="modal-body">
                      Please read the instructions given in the documentation <a
                        href='https://support.unifysofttech.in/topic/13'>here</a> very carefully, before updating.
                      <div class="widget widget-note">
                        Before performing the update process, extract the updates folder in the root of your project
                      </div>
                      <div class="widget widget-warning">
                        We aren't responsible if you loose your customization or your data, please do those steps
                        carefully. Kindly backup all your project files and database before performing updates.
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button id="add" class="btn btn-primary"
                        onclick='document.location.href="{{route("admin.check_updates")}}"'> <i
                          class="fa fa-refresh"></i> Update Admin Panel</button>
                    </div>
                  </div>
                </div>
              </div>
              @else
              <div class="row">
                <div class="col-md-8 offset-md-2">
                  <button class="btn btn-primary btn btn-lg btn-block" disabled> <i class="fa fa-refresh"></i> No
                    Updates Available</button>
                </div>

              </div>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
</section>



<section>
  <div class="container-fluid">
    <div class="row">
      <div class="col-lg-6">
        <div class="card table-card">
          <div class="card-header borderless ">
            <h3>Active Candidates</h3>
          </div>
          <div class="card-body table-responsive">
            <table class="table table-striped table-main">
              <thead>
                <tr>
                  <th>Email</th>
                  <th>Name</th>
                  <!-- <th>Country</th> -->
                  <th>Register Date</th>
                </tr>
              </thead>
              <tbody>
                <?php if($total_active_candidates > 0){
                  foreach($active_candidates as $candidate){ ?>
                <tr>
                  <td>
                    <div class="d-inline-block align-middle">
                      <?php if($candidate->user_dp ==""){ ?>
                      <img src="{{ asset('assets/images/profile.png') }}" alt="user image"
                        class="img-radius img-40 align-top m-r-15">
                      <?php }elseif(stripos($candidate->user_dp,'https://')!==false){ ?>
                      <img src="<?php echo $candidate->user_dp; ?>" alt="user image"
                        class="img-radius img-40 align-top m-r-15">
                      <?php }else{ ?>
                      <img
                        src="<?php echo url(config('app.profile_path')).'/'.$candidate->user_id.'/'.$candidate->user_dp; ?>"
                        alt="user image" class="img-radius img-40 align-top m-r-15">
                      <?php } ?>
                      <div class="d-inline-block">
                        <h6><?php echo $candidate->email; ?>
                      </div>
                    </div>
                  </td>
                  <td><?php echo $candidate->fname." ".$candidate->lname; ?></td>
                  <td><?php echo date('d F Y',strtotime($candidate->created_at)); ?></td>
                </tr>
                <?php } 
             }else{?>
                <tr>
                  <td colspan="6" class="text-center">No Record ...</td>
                </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="card table-card">
          <div class="card-header borderless ">
            <h3>Inactive Candidates</h3>
          </div>
          <div class="card-body table-responsive">
            <table class="table table-striped table-main">
              <thead>
                <tr>
                  <th>Email</th>
                  <th>Name</th>

                  <th>Register Date</th>
                </tr>
              </thead>
              <tbody>
                <?php if($total_pending_candidates > 0){
            foreach($pending_candidates as $candidate){ ?>
                <tr>
                  <td>
                    <div class="d-inline-block align-middle">
                      <?php if($candidate->user_dp ==""){ ?>
                      <img src="{{ asset('assets/images/profile.png') }}" alt="user image"
                        class="img-radius img-40 align-top m-r-15">
                      <?php }elseif(stripos($candidate->user_dp,'https://')!==false){ ?>
                      <img src="<?php echo $candidate->user_dp; ?>" alt="user image"
                        class="img-radius img-40 align-top m-r-15">
                      <?php }else{ ?>
                      <img
                        src="<?php echo url(config('app.profile_path')).'/'.$candidate->user_id.'/'.$candidate->user_dp; ?>"
                        alt="user image" class="img-radius img-40 align-top m-r-15">
                      <?php } ?>
                      <div class="d-inline-block">
                        <h6><?php echo $candidate->email; ?>
                      </div>
                    </div>
                  </td>
                  <td><?php echo $candidate->fname." ".$candidate->lname; ?></td>
                  <td><?php echo date('d F Y',strtotime($candidate->created_at)); ?></td>
                </tr>
                <?php } 
     }else{?>
                <tr>
                  <td colspan="6" class="text-center">No Record ...</td>
                </tr>
                <?php }?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
</div>
</div>
</div>
</div>
</section>
@endsection


