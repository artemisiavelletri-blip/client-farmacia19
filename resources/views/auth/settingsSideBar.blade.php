<div class="sidebar">
    <div class="sidebar-top">
        <div class="sidebar-profile-img">
            <img src="{{ asset('/img/logo/logo.png') }}" alt="" style="margin-top: 10px;">
        </div>
        <h5>{{Auth::user()->name . ' ' . Auth::user()->surname}}</h5>
        <p>{{Auth::user()->email}}</p>
    </div>
    <ul class="sidebar-list">
        <li><a href="/settings/user-profile"><i class="far fa-user"></i> Profilo</a></li>
        <li><a href="/settings/billing-address"><i class="far fa-location-dot"></i> Indirizzo Fatturazione</a></li>
        <li><a href="/settings/shipping-address"><i class="far fa-location-dot"></i> Indirizzi Spedizione</a></li>
        <li><a href="/settings/payment-method"><i class="far fa-wallet"></i> Metodi di Pagamento</a></li>
        <li><a href="/logout"><i class="far fa-sign-out"></i> Esci</a></li>
    </ul>
</div>