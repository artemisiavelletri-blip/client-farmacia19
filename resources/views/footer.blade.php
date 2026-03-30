<!-- footer area -->
    <footer class="footer-area">
        <div class="footer-widget">
            <div class="container">
                <div class="row footer-widget-wrapper pt-100 pb-40">
                    <div class="col-md-6 col-lg-3">
                        <div class="footer-widget-box about-us">
                            <a href="/" class="footer-logo">
                                <img src="{{ asset('/img/logo/logo.png') }}" alt="">
                            </a>
                            <ul class="footer-contact">
                                <li><a href="tel:+393500337318"><i class="far fa-phone"></i>+39 350 0337 318</a></li>
                                <li><a href="mailto:info@farmacia19.it"><i
                                            class="far fa-envelope"></i>info@farmacia19.it</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <div class="footer-widget-box list">
                            <h4 class="footer-widget-title">Quick Links</h4>
                            <ul class="footer-list">
                                <li><a href="/privacy-policy">Privacy policy</a></li>
                                <li><a href="/terms-of-service">Condizioni d'uso</a></li>
                                <li><a href="/terms-of-sell">Condizioni di vendita</a></li>
                                <li><a href="/modalita-costi-spedizione">Modalità e costi di spedizione</a></li>
                                <li><a href="/pagamenti-accettati">Pagamenti accettati</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <div class="footer-widget-box list">
                            <h4 class="footer-widget-title">Categorie</h4>
                            <ul class="footer-list">
                                @foreach($navbarCategories as $category)
                                    <li><a href="/shop-grid/{{$category->token}}">{{$category->name}}</a></li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <div class="footer-widget-box list">
                            <h4 class="footer-widget-title">Supporto</h4>
                            <ul class="footer-list">
                                <li><a href="/contact">Centro di supporto</a></li>
                                <li><a href="/order-list">Traccia il tuo ordine</a></li>
                                <li><a href="/returns-policy">Politiche di reso</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="footer-widget-box list">
                            <h4 class="footer-widget-title">Pagamenti Accettati</h4>
                            <div class="footer-payment mt-20">
                                <img src="{{ asset('/img/payment/visa.svg') }}" alt="">
                                <img src="{{ asset('/img/payment/mastercard.svg') }}" alt="">
                                <!-- <img src="{{ asset('/img/payment/paypal.svg') }}" alt=""> -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="copyright">
            <div class="container">
                <div class="copyright-wrap">
                    <div class="row">
                        <div class="col-12 col-lg-6 align-self-center">
                            <p class="copyright-text">
                                &copy; Copyright <span id="date"></span> <a href="/"> Farmacia19 </a> tutti i diritti sono riservati.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <!-- footer area end -->
    <!-- scroll-top -->
    <a href="#" id="scroll-top"><i class="mt-15 far fa-arrow-up-from-arc"></i></a>
    <!-- scroll-top end -->