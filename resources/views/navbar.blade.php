<!-- header area -->
<header class="header">

    <!-- header middle -->
    <div class="header-middle">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-5 col-lg-3 col-xl-3">
                    <div class="header-middle-logo">
                        <a class="navbar-brand" href="/">
                            <img src="{{ asset('/img/logo/logo.png') }}" alt="logo">
                        </a>
                    </div>
                </div>
                <div class="d-none d-lg-block col-lg-6 col-xl-5">
                    <div class="header-middle-search">
                        <form action="/shop-search">
                            <div class="search-content">
                                <select class="select" name="category">
                                    <option value="">Tutte le Categorie</option>
                                    @foreach($navbarCategories as $category)
                                        <option value="{{$category->token}}">{{$category->name}}</option>
                                    @endforeach
                                </select>
                                <input type="text" class="form-control" name="search" placeholder="Cerca">
                                <button type="submit" class="search-btn"><i class="far fa-search"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-7 col-lg-3 col-xl-4">
                    <div class="header-middle-right">
                        <ul class="header-middle-list">
                            <li class="dropdown-cart">
                                @php
                                    if(Auth::user()){
                                        $view = '#';
                                    } else {
                                        $view = '/login';
                                    }
                                @endphp
                                <a href="{{$view}}" class="list-item">
                                    <div class="list-item-icon">
                                        <i class="mt-15 far fa-user-circle"></i>
                                    </div>
                                    <div class="list-item-info">
                                        @if(Auth::user())
                                            <h6>Ciao</h6>
                                            <h5>{{Auth::user()->name}}</h5>
                                        @else
                                            <h6>Accedi</h6>
                                            <h5>Account</h5>
                                        @endif
                                    </div>
                                </a>
                                @if(Auth::user())
                                    <div class="dropdown-cart-menu">
                                        <span>Ciao {{Auth::user()->name}}</span>
                                        <div class="dropdown-cart-bottom">
                                            <div class="dropdown-cart-total">
                                                <span class="pointer" onclick="window.location.href = '/order-list';">I Miei Ordini</span><br>
                                            </div>
                                            <div class="dropdown-cart-total">
                                                <span class="pointer" onclick="window.location.href = '/settings/user-profile';">Impostazioni Account</span>
                                            </div>
                                            <a href="/logout" class="theme-btn">Esci</a>
                                        </div>
                                    </div>
                                @endif
                            </li>
                            <li id="cart-wrapper" class="dropdown-cart">
                                <a href="#" class="shop-cart list-item">
                                    @if(Auth::user() && auth()->user()->cartItems()->exists())
                                        <div class="list-item-icon">
                                            <i class="mt-15 far fa-shopping-bag"></i><span>{{Auth::user()->cartItems->sum('quantity') ?? ''}}</span>
                                        </div>
                                    @else
                                        <div class="list-item-icon">
                                            <i class="mt-15 far fa-shopping-bag"></i>
                                        </div>
                                    @endif
                                    <div class="list-item-info">
                                        @if(Auth::user() && auth()->user()->cartItems()->exists())
                                            <h6>€{{number_format((float)auth()->user()->cartItems()->with('product')->get()->sum->subtotal, 2, '.', '')}}</h6>                                            
                                        @else
                                            <h6>€0,00</h6>
                                        @endif
                                        <h5>Carrello</h5>
                                    </div>
                                </a>
                                <div class="dropdown-cart-menu">
                                    <div class="dropdown-cart-header">
                                        @if(Auth::user() && Auth::user()->cartItems)
                                            <span>{{Auth::user()->cartItems->count()}} Prodotto/i</span>
                                        @endif
                                        <!-- <a href="#">View Cart</a> -->
                                    </div>
                                    <ul class="dropdown-cart-list">
                                        @if(Auth::user() && auth()->user()->cartItems()->exists())
                                            @foreach(Auth::user()->cartItems as $item)
                                                <li>
                                                    <div class="dropdown-cart-item">
                                                        <div class="cart-img">
                                                            <a href="#"><img src="{{ asset('/storage-admin/' . $item->product()->first()->image ) }}" alt="#"></a>
                                                        </div>
                                                        
                                                        <div class="cart-info">
                                                            <h4><a href="/shop-single/{{ $item->product()->first()->ean ?? $item->product()->first()->minsan }}">{{$item->product()->first()->name}}</a></h4>
                                                            <p class="cart-qty">{{$item->quantity}}x - 
                                                                <span class="cart-amount">
                                                                    @if($item->product()->first()->discountPrice)
                                                                        €{{number_format((float)$item->product()->first()->discountPrice, 2, '.', '')}}
                                                                    @else
                                                                        €{{number_format((float)$item->product()->first()->price, 2, '.', '')}}
                                                                    @endif
                                                                </span>
                                                            </p>
                                                        </div>
                                                        <a href="#" class="cart-remove" title="Rimuovi questo prodotto" data-id="{{$item->id}}"><i
                                                                class="far fa-times-circle"></i></a>
                                                    </div>
                                                </li>   
                                            @endforeach
                                        @else
                                            <li>
                                                <div class="dropdown-cart-item">
                                                    <span>Nessun Prodotto nel Carrello</span>
                                                </div>
                                            </li>
                                        @endif                                  
                                    </ul>
                                    @if(Auth::user() && auth()->user()->cartItems()->exists())
                                        <div class="dropdown-cart-bottom">
                                            <div class="dropdown-cart-total">
                                                <span>Totale</span>
                                                <span class="total-amount">€{{number_format((float)auth()->user()->cartItems()->with('product')->get()->sum->subtotal, 2, '.', '')}}</span>
                                            </div>
                                            <a href="/shop-cart" class="theme-btn">Checkout</a>
                                        </div>
                                    @endif
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- header middle end -->

    <!-- navbar -->
    <div class="main-navigation">
        <nav class="navbar navbar-expand-lg">
            <div class="container position-relative">
                <a class="navbar-brand" href="/">
                    <img src="{{ asset('/img/logo/logo.png') }}" class="logo-scrolled" alt="logo">
                </a>
                <div class="category-all">
                    <button class="category-btn" type="button">
                        <i class="fas fa-list-ul"></i><span>Tutte le Categorie</span>
                    </button>
                    <ul class="main-category">
                        @foreach($navbarCategories as $category)
                            <li><a href="/shop-grid/{{$category->token}}"><img src="{{asset('/storage-admin/' . $category->image) }}" alt=""><span>{{$category->name}}</span></a></li>
                        @endforeach
                    </ul>
                </div>
                <div class="mobile-menu-right">
                    <div class="mobile-menu-btn">
                        <a href="#" class="nav-right-link search-box-outer"><i class="far fa-search"></i></a>
                        <a href="/shop-cart" class="nav-right-link">
                            <i class="far fa-shopping-bag"></i>
                            @if(Auth::user() && Auth::user()->cartItems)
                                <span>{{Auth::user()->cartItems->sum('quantity') ?? 0}}</span>
                            @endif
                        </a>
                    </div>
                    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas"
                        data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar"
                        aria-label="Toggle navigation">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                </div>
                <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasNavbar"
                    aria-labelledby="offcanvasNavbarLabel">
                    <div class="offcanvas-header">
                        <a href="index.html" class="offcanvas-brand" id="offcanvasNavbarLabel">
                            <img src="{{ asset('/img/logo/logo.png') }}" alt="">
                        </a>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"
                            aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body">
                        <ul class="navbar-nav flex-grow-1">
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('index') ? 'active' : '' }}" href="/">Home</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->route('type') === 'offerts' ? 'active' : '' }}" href="/shop-search/offerts">Offerte</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->route('type') === 'new' ? 'active' : '' }}" href="/shop-search/new">Novità</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('brand') ? 'active' : '' }}" href="/brand">Brand</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}" href="/contact">Contatti</a></li>
                        </ul>
                        <!-- nav-right -->
                        <div class="nav-right">
                            <a class="nav-right-link" href="/order-list"><i class="fal fa-truck-fast"></i> Traccia Il Mio Ordine</a>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
    </div>
    <!-- navbar end -->

</header>
<!-- header area end -->
<!-- mobile popup search -->
    <div class="search-popup">
        <button class="close-search"><span class="far fa-times"></span></button>
        <form action="#">
            <div class="form-group">
                <input type="search" name="search-field" class="form-control" placeholder="Cerca" required>
                <button type="submit"><i class="far fa-search"></i></button>
            </div>
        </form>
    </div>
<!-- mobile popup search end -->