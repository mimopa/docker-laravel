            <div class=" no-pad">
                <!-- <span  class="openmenu" onclick="openNav()">&#9776;</span> -->
                <div class="sidenav overlay mystyle closebar" id="navBar">
                    <!-- <span href="javascript:void(0)" class="closebtn" id="closeNav" style="margin-top:40px; :pointer" onclick="closeNav(this)">&times;</span> -->
                    <div >
                        <div class="new-menu-main">
                        <div class="logo-main">
					<a href="{{route('admin.dashboard')}}"><img src="{{ MyFunctions::getLogo() }}" alt=""  class="logo"/></a>
				</div>
                            <ul class="new-nav">
                                <li>
                                    <a href="{{ route('admin.dashboard')}}" >
                                    <i class="fa fa-home" aria-hidden="true"></i>     Dashboard
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.categories.index')}}" >
                                     <i class="fa fa-list-ul" aria-hidden="true"></i>     Categories
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.sounds.index')}}" >
                                     <i class="fa fa-music" aria-hidden="true"></i>     Sounds
                                    </a>
                                </li>

                                <li data-toggle="collapse" data-target="#users_menu" class="collapsed main_li">
                                  <a href="#"><i class="fa fa-users"></i> Users <span class="arrow"></span></a>
                                    <ul class="sub-menu collapse" id="users_menu">
                                        <li> 
                                            <a href="{{ route('admin.candidates.index')}}/1" >
                                                Active 
                                            </a>
                                        </li>
                                        <li> 
                                            <a href="{{ route('admin.candidates.index')}}/0" >
                                                Inactive 
                                            </a>
                                        </li>
                                    </ul>
                                </li>  
                                <li data-toggle="collapse" data-target="#verify_menu" class="collapsed main_li">
                                  <a href="#"><i class="fa fa-users"></i> Account Verification <span class="arrow"></span></a>
                                    <ul class="sub-menu collapse" id="verify_menu">
                                        <li> 
                                            <a href="{{ route('admin.user-verify.index', ['type'=>'A'])}}" >
                                                Verified 
                                            </a>
                                        </li>
                                        <li> 
                                            <a href="{{ route('admin.user-verify.index', ['type'=>'P'])}}" >
                                                Pending 
                                            </a>
                                        </li>
                                        <li> 
                                            <a href="{{ route('admin.user-verify.index', ['type'=>'R'])}}" >
                                                Rejected 
                                            </a>
                                        </li>
                                    </ul>
                                </li> 
                                <li> 
                                    <a href="{{ route('admin.videos.index')}}" >
                                    <i class="fa fa-video-camera" aria-hidden="true"></i>    Videos
                                    </a>
                                </li>
                                <li >
                                    <a href="{{ route('admin.flagvideos.index')}}" >
                                     <i class="fa fa-video-camera" aria-hidden="true"></i>    Flaged Videos
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.tags.index')}}" >
                                    <i class="fa fa-tag" aria-hidden="true"></i>     Tags
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.reports.index')}}" >
                                     <i class="fa fa-file" aria-hidden="true"></i>     Reports
                                    </a>
                                </li>
                                <li data-toggle="collapse" data-target="#settings" class="collapsed main_li">
                                  <a href="{{ route('admin.settings')}}"><i class="fa fa-gear"></i> Settings <span class="arrow"></span></a>
                                 
                                </li> 
                                <li>
                                    <a href="{{ route('admin.pages.index')}}" >
                                    <i class="fa fa-edit" aria-hidden="true"></i>     Pages
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.sponsors.index')}}" >
                                    <i class="fa fa-handshake-o" aria-hidden="true"></i>     Sponsors
                                    </a>
                                </li>
                                
                                <li data-toggle="collapse" data-target="#eng_menu" class="collapsed main_li">
                                  <a href="#"><i class="fa fa-users"></i> Engagement <span class="arrow"></span></a>
                                    <ul class="sub-menu collapse" id="eng_menu">
                                        <li> 
                                            <a href="{{ route('admin.comments.index')}}" >
                                                Comments 
                                            </a>
                                        </li>
                                        <li> 
                                            <a href="{{ route('admin.chats.index')}}" >
                                                Chat 
                                            </a>
                                        </li>
                                        <li> 
                                            <a href="{{ route('admin.likes.index')}}" >
                                                Likes 
                                            </a>
                                        </li>
                                    </ul>
                                </li> 
                                
                                <li>
                                    <a href="{{ route('admin.logout') }}" >
                                    <i class="fa fa-sign-out" aria-hidden="true"></i>     Logout
                                    </a>
                                </li>
                                <!-- <li>
                                    <a href="{{ route('admin.dashboard')}}" ><i class="nav-icons fa fa-home" aria-hidden="true"></i> Dashboard</a>
                                </li>
                                <li>
                                    <a href="profile.html"><i class="nav-icons fa fa-address-book" aria-hidden="true"></i> Profile</a>
                                </li>
                                <li>
                                    <a href="sale.html"><i class="nav-icons fa fa-tag" aria-hidden="true"></i> Sale</a>
                                </li>
                                <li>
                                    <a href="shopping-cart.html"><i class="nav-icons fa fa-shopping-bag" aria-hidden="true"></i> Shopping Cart</a>
                                </li>
                                <li>
                                    <a href="products-detail-page.html"><i class="nav-icons fa fa-heart" aria-hidden="true"></i> Products Detail</a>
                                    <button class="dropdown-btn"><i class="nav-icons fa fa-puzzle-piece" aria-hidden="true"></i> Components 
                                        <i class="fa fa-caret-down"></i>
                                    </button>
                                    <div class="dropdown-container">
                                        <a href="#">Link 1</a>
                                        <a href="#">Link 2</a>
                                        <a href="#">Link 3</a>
                                    </div>
                                </li>
                                <li>
                                    <a href="#"><i class="nav-icons fa fa-search-plus" aria-hidden="true"></i> Search</a>
                                </li> -->
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
