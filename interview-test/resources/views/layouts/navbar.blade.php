<nav class="navbar navbar-default navbar-fixed-top">
    <div class="container-fluid">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#main-navbar" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="{{ route('home') }}">
                <strong>Easy <span style="color: #87CEEB;">Pickup</span></strong>
            </a>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="main-navbar">
            <!-- Left Side Navigation -->
           <ul class="nav navbar-nav">
                @auth
                    <!-- For logged-in users, Home goes to Dashboard -->
                    <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <a href="{{ route('dashboard') }}">
                            <span class="glyphicon glyphicon-dashboard"></span> Dashboard
                        </a>
                    </li>
                @else
                    <!-- For guests, Home goes to Welcome page -->
                    <li class="{{ request()->routeIs('home') ? 'active' : '' }}">
                        <a href="{{ route('home') }}">
                            <span class="glyphicon glyphicon-home"></span> Home
                        </a>
                    </li>
                @endauth
                
                <li class="{{ request()->routeIs('car*') ? 'active' : '' }}">
                    <a href="{{ route('car') }}">
                        <span class="glyphicon glyphicon-list-alt"></span> Cars
                    </a>
                </li>
                
                <!-- Only show for authenticated users -->
                @auth
                    <li class="{{ request()->routeIs('bookings*') ? 'active' : '' }}">
                        <a href="/bookings">
                            <span class="glyphicon glyphicon-calendar"></span> Bookings
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('invoices*') ? 'active' : '' }}">
                        <a href="#">
                            <span class="glyphicon glyphicon-file"></span> Invoices
                        </a>
                    </li>
                @endauth
                
                <!-- Admin Dropdown - Only for Admin/Manager -->
                @auth
                    @if(in_array(Auth::user()->role, ['admin', 'manager']))
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                <span class="glyphicon glyphicon-cog"></span> Admin <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="#"><span class="glyphicon glyphicon-user"></span> Manage Users</a></li>
                                <li><a href="#"><span class="glyphicon glyphicon-stats"></span> Reports</a></li>
                                <li role="separator" class="divider"></li>
                                <li><a href="#"><span class="glyphicon glyphicon-cog"></span> Settings</a></li>
                            </ul>
                        </li>
                    @endif
                @endauth
            </ul>

            <!-- Right Side - User Profile Dropdown -->
            <ul class="nav navbar-nav navbar-right">
                @auth
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                            <!-- Profile Icon -->
                            <span class="profile-icon">
                                <span class="glyphicon glyphicon-user" style="font-size: 18px; margin-right: 5px;"></span>
                            </span>
                            <span class="user-name">{{ Auth::user()->name }}</span>
                            <span class="badge user-role" style="background-color: #87CEEB; color: #fff; margin-left: 5px;">
                                {{ ucfirst(Auth::user()->role) }}
                            </span>
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-right">
                            <li class="dropdown-header">
                                <strong>{{ Auth::user()->name }}</strong>
                                <br>
                                <small class="text-muted">{{ Auth::user()->email }}</small>
                            </li>
                            <li role="separator" class="divider"></li>
                            <li class="{{ request()->routeIs('profile.edit') ? 'active' : '' }}">
                                <a href="{{ route('profile.edit') }}">
                                    <span class="glyphicon glyphicon-user"></span> My Profile
                                </a>
                            </li>
                            <li role="separator" class="divider"></li>
                            <li>
                                <a href="{{ route('logout') }}" 
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <span class="glyphicon glyphicon-log-out"></span> Logout
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                    @csrf
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <li><a href="{{ route('login') }}"><span class="glyphicon glyphicon-log-in"></span> Login</a></li>
                    <li><a href="{{ route('register') }}"><span class="glyphicon glyphicon-user"></span> Register</a></li>
                @endauth
            </ul>
        </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
</nav>