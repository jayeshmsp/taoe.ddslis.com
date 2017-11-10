<nav class="navbar navbar-default navbar-static-top m-b-0">
    <div class="navbar-header">
        <div class="top-left-part">
            <!-- Logo -->
            <a class="logo" href="{{'/home'}}">
                <b>
                <img src="{{asset('public/plugins/images/admin-logo.png')}}" alt="home" class="dark-logo" />
                <img src="{{asset('public/plugins/images/admin-logo-dark.png')}}" alt="home" class="light-logo" />
                </b>
                <span class="hidden-xs">
                    Art of elysium
                    {{-- <img src="../plugins/images/admin-text.png" alt="home" class="dark-logo" />
                    <img src="../plugins/images/admin-text-dark.png" alt="home" class="light-logo" /> --}}
                </span>
            </a>
        </div>
        <!-- /Logo -->
        <!-- Search input and Toggle icon -->
        <ul class="nav navbar-top-links navbar-left">
            <li><a href="javascript:void(0)" class="open-close waves-effect waves-light visible-xs"><i class="ti-close ti-menu"></i></a></li>
            <!-- /.Megamenu -->
        </ul>
                    
                    <ul class="nav navbar-top-links navbar-right pull-right">
                        {{-- <li>
                            <form role="search" class="app-search hidden-sm hidden-xs m-r-10">
                                <input type="text" placeholder="Search..." class="form-control"> <a href=""><i class="fa fa-search"></i></a> </form>
                            </li> --}}
                            <li class="dropdown">
                                <a class="dropdown-toggle profile-pic" data-toggle="dropdown" href="#"> {{-- <img src="../plugins/images/users/varun.jpg" alt="user-img" width="36" class="img-circle"> --}}<b class="hidden-xs">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</b><span class="caret"></span> </a>
                                <ul class="dropdown-menu dropdown-user animated flipInY">
                                    <li>
                                        <div class="dw-user-box">
                                            <div class="u-img">{{-- <img src="../plugins/images/users/varun.jpg" alt="user" /> --}}</div>
                                            <div class="u-text">
                                                <h4>{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</h4>
                                                <p class="text-muted">{{ Auth::user()->email }}</p><a href="{{ url('user/profile') }}" class="btn btn-rounded btn-danger btn-sm">Edit Profile</a></div>
                                            </div>
                                        </li>
                                        <li role="separator" class="divider"></li>
                                        <li><a href="{{route('logout')}}"><i class="fa fa-power-off"></i> Logout</a></li>
                                    </ul>
                                    <!-- /.dropdown-user -->
                                </li>
                                <!-- /.dropdown -->
                            </ul>
                        </div>
                        <!-- /.navbar-header -->
                        <!-- /.navbar-top-links -->
                        <!-- /.navbar-static-side -->
                    </nav>