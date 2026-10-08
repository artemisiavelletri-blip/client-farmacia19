@extends('master')

@section('content')

<style>

/* =========================================================
   PAGINA
========================================================= */

.coupon-page {
    background: #f5f9fc;
    padding: 70px 0 90px;
    min-height: 600px;
}

.coupon-page-header {
    margin-bottom: 32px;
}

.coupon-page-header h3 {
    color: #003b5c;
    font-size: 32px;
    font-weight: 800;
    margin-bottom: 8px;
}

.coupon-page-header p {
    margin: 0;
    color: #718096;
    font-size: 16px;
}


/* =========================================================
   CARD COUPON
========================================================= */

.coupon-card {
    --coupon-color: #087c66;
    --coupon-soft: #ebfaf5;

    position: relative;
    display: flex;
    flex-direction: column;

    width: 100%;
    height: 100%;

    background: #fff;

    border: 1px solid #e6edf2;
    border-radius: 20px;

    overflow: hidden;
    cursor: pointer;

    box-shadow: 0 6px 20px rgba(0, 59, 92, .04);

    transition:
        transform .25s ease,
        box-shadow .25s ease;
}

.coupon-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 18px 40px rgba(0, 59, 92, .12);
}


/* =========================================================
   TEMI COUPON
========================================================= */

.coupon-card.theme-pink {
    --coupon-color: #b4144b;
    --coupon-soft: #fff0f5;
}

.coupon-card.theme-blue {
    --coupon-color: #1167ad;
    --coupon-soft: #edf7ff;
}

.coupon-card.theme-green {
    --coupon-color: #087c66;
    --coupon-soft: #ebfaf5;
}

.coupon-card.theme-orange {
    --coupon-color: #c65708;
    --coupon-soft: #fff5e7;
}


/* =========================================================
   HERO CARD
========================================================= */

.coupon-hero {
    position: relative;

    min-height: 180px;

    padding: 24px;

    background:
        radial-gradient(
            circle at 88% 20%,
            rgba(255,255,255,.85) 0,
            rgba(255,255,255,.85) 35px,
            transparent 36px
        ),
        var(--coupon-soft);

    overflow: hidden;
}

.coupon-hero::before {
    content: "";

    position: absolute;

    width: 110px;
    height: 110px;

    border-radius: 50%;

    right: -35px;
    top: -35px;

    background: var(--coupon-color);

    opacity: .04;
}

.coupon-hero::after {
    content: "%";

    position: absolute;

    right: 15px;
    bottom: -42px;

    font-size: 150px;
    font-weight: 900;
    line-height: 1;

    color: var(--coupon-color);

    opacity: .055;

    pointer-events: none;
}

.coupon-card.fixed-discount .coupon-hero::after {
    content: "€";
}


/* =========================================================
   BADGE
========================================================= */

.coupon-badge {
    position: relative;
    z-index: 2;

    display: inline-flex;
    align-items: center;

    gap: 7px;

    padding: 6px 11px;

    border-radius: 50px;

    background: rgba(255,255,255,.75);

    color: var(--coupon-color);

    font-size: 11px;
    font-weight: 800;

    text-transform: uppercase;
    letter-spacing: .4px;
}


/* =========================================================
   SCONTO CARD
========================================================= */

.coupon-discount {
    position: relative;
    z-index: 2;

    margin-top: 18px;

    color: var(--coupon-color);

    font-size: 52px;
    line-height: .95;

    font-weight: 900;
    letter-spacing: -2px;
}

.coupon-discount-label {
    position: relative;
    z-index: 2;

    margin-top: 8px;

    color: var(--coupon-color);

    font-size: 17px;
    font-weight: 800;

    text-transform: uppercase;
}


/* =========================================================
   BODY CARD
========================================================= */

.coupon-body {
    display: flex;
    flex-direction: column;

    flex: 1;

    padding: 22px;
}

.coupon-title {
    color: #003b5c;

    font-size: 18px;
    line-height: 1.25;

    font-weight: 800;

    margin-bottom: 10px;
}

.coupon-description {
    color: #66788a;

    font-size: 14px;
    line-height: 1.55;

    margin-bottom: 18px;

    flex-grow: 1;
}


/* =========================================================
   INFO CARD
========================================================= */

.coupon-info {
    padding-top: 15px;

    border-top: 1px solid #edf1f4;
}

.coupon-info-row {
    display: flex;
    align-items: center;

    gap: 10px;

    margin-bottom: 11px;
}

.coupon-info-icon {
    width: 34px;
    height: 34px;

    flex: 0 0 34px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 50%;

    background: var(--coupon-soft);

    color: var(--coupon-color);

    font-size: 15px;
    font-weight: 700;
}

.coupon-info-text {
    min-width: 0;
}

.coupon-info-text span {
    display: block;

    color: #8290a0;

    font-size: 12px;
    line-height: 1.2;
}

.coupon-info-text strong {
    display: block;

    margin-top: 2px;

    color: #003b5c;

    font-size: 13px;
    line-height: 1.35;

    font-weight: 700;

    overflow-wrap: anywhere;
}


/* =========================================================
   BOTTONE CARD
========================================================= */

.coupon-button {
    width: 100%;

    margin-top: 12px;

    padding: 13px 16px;

    border: 0;
    border-radius: 11px;

    background: #078d6a;

    color: #fff;

    font-size: 13px;
    font-weight: 800;

    text-transform: uppercase;

    transition: .2s ease;
}

.coupon-card:hover .coupon-button {
    background: #05775a;
}

.coupon-button-arrow {
    margin-left: 7px;
    font-size: 17px;
}


/* =========================================================
   NESSUN COUPON
========================================================= */

.coupon-empty {
    padding: 50px;

    background: #fff;

    border-radius: 18px;

    text-align: center;

    color: #718096;

    border: 1px solid #e6edf2;
}


/* =========================================================
   PAGINAZIONE
========================================================= */

.coupon-pagination {
    margin-top: 35px;
}


/* =========================================================
   BOX FINALI
========================================================= */

.coupon-benefits {
    margin-top: 55px;
    padding-top: 10px;
}

.coupon-benefits-heading {
    margin-bottom: 20px;
}

.coupon-benefits-heading h4 {
    margin: 0 0 5px;

    color: #003b5c;

    font-size: 24px;
    font-weight: 800;
}

.coupon-benefits-heading p {
    margin: 0;

    color: #718096;

    font-size: 14px;
}


/* =========================================================
   SINGOLO BOX
========================================================= */

.benefit-box {
    --benefit-color: #087c66;
    --benefit-soft: #ebfaf5;

    position: relative;

    display: flex;
    align-items: center;

    gap: 16px;

    width: 100%;
    min-height: 125px;

    padding: 20px;

    background: var(--benefit-soft);

    border: 1px solid rgba(0, 59, 92, .04);
    border-radius: 16px;

    overflow: hidden;

    transition:
        transform .2s ease,
        box-shadow .2s ease;
}

.benefit-box:hover {
    transform: translateY(-3px);

    box-shadow:
        0 12px 30px rgba(0, 59, 92, .08);
}


/* =========================================================
   COLORI BOX
========================================================= */

.benefit-green {
    --benefit-color: #087c66;
    --benefit-soft: #ebfaf5;
}

.benefit-blue {
    --benefit-color: #1167ad;
    --benefit-soft: #edf7ff;
}

.benefit-pink {
    --benefit-color: #b4144b;
    --benefit-soft: #fff0f5;
}

.benefit-orange {
    --benefit-color: #c65708;
    --benefit-soft: #fff5e7;
}


/* =========================================================
   ICONA BOX
========================================================= */

.benefit-icon {
    position: relative;
    z-index: 2;

    flex: 0 0 64px;

    width: 64px;
    height: 64px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 50%;

    background: rgba(255,255,255,.65);

    color: var(--benefit-color);

    font-size: 29px;
    line-height: 1;

    font-weight: 800;
}


/* =========================================================
   CONTENUTO BOX
========================================================= */

.benefit-content {
    position: relative;
    z-index: 2;

    min-width: 0;
}

.benefit-content h5 {
    margin: 0 0 6px;

    color: #003b5c;

    font-size: 14px;
    line-height: 1.3;

    font-weight: 800;
}

.benefit-content p {
    margin: 0;

    color: #66788a;

    font-size: 12px;
    line-height: 1.45;
}


/* =========================================================
   DECORAZIONE BOX
========================================================= */

.benefit-box::after {
    content: "";

    position: absolute;

    width: 100px;
    height: 100px;

    right: -45px;
    bottom: -50px;

    border-radius: 50%;

    background: var(--benefit-color);

    opacity: .035;
}


/* =========================================================
   MODAL - TEMI
========================================================= */

.coupon-modal {
    --modal-color: #087c66;
    --modal-soft: #ebfaf5;
}

.coupon-modal.theme-pink {
    --modal-color: #b4144b;
    --modal-soft: #fff0f5;
}

.coupon-modal.theme-blue {
    --modal-color: #1167ad;
    --modal-soft: #edf7ff;
}

.coupon-modal.theme-green {
    --modal-color: #087c66;
    --modal-soft: #ebfaf5;
}

.coupon-modal.theme-orange {
    --modal-color: #c65708;
    --modal-soft: #fff5e7;
}


/* =========================================================
   MODAL
========================================================= */

.coupon-modal .modal-dialog {
    max-width: 460px;
}

.coupon-modal .modal-content {
    border: 0;

    border-radius: 22px;

    overflow: hidden;

    box-shadow: 0 25px 80px rgba(0,0,0,.28);
}


/* =========================================================
   HEADER MODAL
========================================================= */

.coupon-modal .modal-header {
    position: relative;

    display: flex;
    align-items: center;

    padding: 16px 22px;

    background: #fff;

    border-bottom: 1px solid #edf1f4;
}

.coupon-modal .modal-title {
    margin: 0;

    color: #003b5c;

    font-size: 18px;
    font-weight: 800;
}

.coupon-modal-close {
    position: absolute;

    right: 13px;
    top: 50%;

    transform: translateY(-50%);

    padding: 5px 10px;

    border: 0;

    background: transparent;

    color: #536273;

    font-size: 28px;
    line-height: 1;

    cursor: pointer;
}


/* =========================================================
   BODY MODAL
========================================================= */

.coupon-modal-body {
    position: relative;

    padding: 0;

    overflow: hidden;

    background: #fff;
}


/* =========================================================
   AREA COLORATA MODAL
========================================================= */

.modal-theme-area {
    position: relative;

    padding: 20px 25px 18px;

    background:
        radial-gradient(
            circle at 90% 15%,
            rgba(255,255,255,.65) 0,
            rgba(255,255,255,.65) 38px,
            transparent 39px
        ),
        var(--modal-soft);

    overflow: hidden;
}

.modal-theme-area::before {
    content: "";

    position: absolute;

    width: 125px;
    height: 125px;

    border-radius: 50%;

    right: -50px;
    top: -50px;

    background: var(--modal-color);

    opacity: .04;
}

.modal-theme-area::after {
    content: "%";

    position: absolute;

    right: 5px;
    bottom: -38px;

    font-size: 125px;
    font-weight: 900;
    line-height: 1;

    color: var(--modal-color);

    opacity: .035;

    pointer-events: none;
}

.coupon-modal.fixed-discount .modal-theme-area::after {
    content: "€";
}

.modal-theme-area > * {
    position: relative;
    z-index: 2;
}


/* =========================================================
   ICONA MODAL
========================================================= */

.modal-coupon-icon {
    width: 46px;
    height: 46px;

    margin: 0 auto 7px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 50%;

    background: rgba(255,255,255,.72);

    color: var(--modal-color);

    font-size: 20px;
    font-weight: 800;
}


/* =========================================================
   SCONTO MODAL
========================================================= */

.modal-discount {
    color: var(--modal-color);

    font-size: 44px;
    line-height: 1;

    font-weight: 900;

    letter-spacing: -2px;
}

.modal-discount-label {
    margin-top: 3px;

    color: var(--modal-color);

    font-size: 14px;
    font-weight: 800;

    text-transform: uppercase;
}

.modal-coupon-title {
    margin-top: 14px;

    color: #003b5c;

    font-size: 18px;
    font-weight: 800;
}

.modal-description {
    max-width: 390px;

    margin: 6px auto 0;

    color: #617386;

    font-size: 13px;
    line-height: 1.45;
}


/* =========================================================
   AREA BIANCA MODAL
========================================================= */

.modal-white-area {
    padding: 17px 25px 22px;

    background: #fff;
}


/* =========================================================
   CODICE
========================================================= */

.coupon-code-box {
    position: relative;

    padding: 13px 48px 13px 18px;

    background: var(--modal-soft);

    border: 1px dashed var(--modal-color);

    border-radius: 12px;
}

#couponCode {
    margin: 0;

    color: #003b5c;

    font-size: 19px;
    font-weight: 900;

    letter-spacing: .8px;

    overflow-wrap: anywhere;
}

.coupon-code-copy-icon {
    position: absolute;

    right: 17px;
    top: 50%;

    transform: translateY(-50%);

    color: var(--modal-color);

    font-size: 19px;
}


/* =========================================================
   COPIA
========================================================= */

#copyCoupon {
    width: 100%;

    margin-top: 10px;

    padding: 11px 15px;

    border: 0;
    border-radius: 10px;

    background: var(--modal-color);

    color: #fff;

    cursor: pointer;

    font-size: 13px;
    font-weight: 700;

    transition: .2s ease;
}

#copyCoupon:hover {
    filter: brightness(.90);
}

#copySuccess {
    display: none;

    margin-top: 9px;

    padding: 8px;

    background: var(--modal-soft);

    border-radius: 9px;

    color: var(--modal-color);

    font-size: 12px;
    font-weight: 700;
}


/* =========================================================
   INFO MODAL
========================================================= */

.modal-coupon-info {
    margin-top: 16px;

    padding-top: 14px;

    border-top: 1px solid #edf1f4;

    text-align: left;
}

.modal-info-row {
    display: flex;
    align-items: center;

    gap: 10px;

    margin-bottom: 10px;
}

.modal-info-icon {
    width: 34px;
    height: 34px;

    flex: 0 0 34px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 50%;

    background: var(--modal-soft);

    color: var(--modal-color);

    font-size: 15px;
    font-weight: 700;
}

.modal-info-text {
    min-width: 0;

    color: #8290a0;

    font-size: 12px;
}

.modal-info-text span {
    display: block;
}

.modal-info-text strong {
    display: block;

    margin-top: 2px;

    color: #003b5c;

    font-size: 13px;
    font-weight: 700;

    line-height: 1.35;

    overflow-wrap: anywhere;
}


/* =========================================================
   LINK MODAL
========================================================= */

.coupon-application-link {
    display: flex;
    align-items: center;
    justify-content: space-between;

    width: 100%;

    margin: 4px 0 14px;

    padding: 12px 15px;

    border-radius: 10px;

    background: var(--modal-soft);

    color: var(--modal-color) !important;

    font-size: 13px;
    font-weight: 800;

    text-decoration: none !important;

    transition:
        background .2s ease,
        color .2s ease,
        transform .2s ease;
}

.coupon-application-link:hover {
    background: var(--modal-color);

    color: #fff !important;

    text-decoration: none !important;

    transform: translateY(-1px);
}

.coupon-application-link-arrow {
    margin-left: 15px;

    font-size: 18px;
    line-height: 1;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 1199px) {

    .coupon-discount {
        font-size: 46px;
    }

}


@media (max-width: 767px) {

    .coupon-page {
        padding: 45px 0 60px;
    }

    .coupon-page-header h3 {
        font-size: 27px;
    }

    .coupon-hero {
        min-height: 160px;
    }

    .coupon-discount {
        font-size: 48px;
    }

    .coupon-benefits {
        margin-top: 40px;
    }

    .coupon-benefits-heading h4 {
        font-size: 21px;
    }

    .benefit-box {
        min-height: 110px;
        padding: 17px;
    }

    .benefit-icon {
        flex: 0 0 55px;

        width: 55px;
        height: 55px;

        font-size: 25px;
    }

    .coupon-modal .modal-dialog {
        max-width: calc(100% - 24px);

        margin-left: auto;
        margin-right: auto;
    }

    .modal-theme-area {
        padding: 18px 18px 16px;
    }

    .modal-white-area {
        padding: 16px 18px 20px;
    }

    .modal-discount {
        font-size: 42px;
    }

}

</style>


<main class="main">

    <div class="coupon-page">

        <div class="container">


            {{-- =====================================================
                 HEADER
            ====================================================== --}}

            <div class="coupon-page-header">

                <h3>
                    Coupon per te
                </h3>

                <p>
                    Approfitta delle nostre promozioni e risparmia sui tuoi acquisti.
                </p>

            </div>



            {{-- =====================================================
                 LISTA COUPON
            ====================================================== --}}

            <div class="row g-4">

                @forelse($promotion as $coupon)

                    @php

                        $themes = [
                            'theme-pink',
                            'theme-blue',
                            'theme-green',
                            'theme-orange'
                        ];

                        $globalIndex =
                            (($promotion->currentPage() - 1)
                            * $promotion->perPage())
                            + $loop->index;

                        $theme =
                            $themes[
                                $globalIndex % count($themes)
                            ];

                        $isFixed =
                            !is_null($coupon->fixDiscount)
                            &&
                            (float) $coupon->fixDiscount > 0;

                        $startDate =
                            $coupon->start_date
                                ? \Carbon\Carbon::parse($coupon->start_date)
                                : null;

                        $endDate =
                            $coupon->end_date
                                ? \Carbon\Carbon::parse($coupon->end_date)
                                : null;

                    @endphp


                    <div class="col-md-6 col-xl-3 d-flex">

                        <div
                            class="
                                coupon-card
                                box-cliccabile
                                {{ $theme }}
                                {{ $isFixed ? 'fixed-discount' : 'percentage-discount' }}
                            "
                            data-id="{{ $coupon->token }}"
                            data-theme="{{ $theme }}"
                        >


                            {{-- HERO --}}

                            <div class="coupon-hero">

                                <div class="coupon-badge">

                                    <span>🏷</span>

                                    Codice sconto

                                </div>


                                <div class="coupon-discount">

                                    @if($isFixed)

                                        -{{ number_format(
                                            $coupon->fixDiscount,
                                            0,
                                            ',',
                                            '.'
                                        ) }}€

                                    @elseif(
                                        !is_null($coupon->percentage)
                                        &&
                                        (float) $coupon->percentage > 0
                                    )

                                        -{{ number_format(
                                            $coupon->percentage,
                                            0,
                                            ',',
                                            '.'
                                        ) }}%

                                    @endif

                                </div>


                                <div class="coupon-discount-label">
                                    di sconto
                                </div>

                            </div>



                            {{-- BODY --}}

                            <div class="coupon-body">


                                <div class="coupon-title">
                                    Un vantaggio pensato per te
                                </div>


                                <div class="coupon-description">
                                    {{ $coupon->description }}
                                </div>



                                {{-- INFO --}}

                                <div class="coupon-info">


                                    {{-- APPLICAZIONE --}}

                                    @if(
                                        !empty($coupon->application_type)
                                        &&
                                        !empty($coupon->application_label)
                                    )

                                        <div class="coupon-info-row">


                                            <div class="coupon-info-icon">

                                                @switch($coupon->application_type)

                                                    @case('product')
                                                        📦
                                                        @break

                                                    @case('brand')
                                                        🏷
                                                        @break

                                                    @case('category')
                                                        ▦
                                                        @break

                                                    @case('subcategory')
                                                        ▦
                                                        @break

                                                    @case('all_products')
                                                        🛒
                                                        @break

                                                    @default
                                                        🛒

                                                @endswitch

                                            </div>


                                            <div class="coupon-info-text">

                                                <span>

                                                    @switch($coupon->application_type)

                                                        @case('product')
                                                            Prodotto
                                                            @break

                                                        @case('brand')
                                                            Brand
                                                            @break

                                                        @case('category')
                                                            Categoria
                                                            @break

                                                        @case('subcategory')
                                                            Categoria
                                                            @break

                                                        @case('all_products')
                                                            Valido su
                                                            @break

                                                        @default
                                                            Valido su

                                                    @endswitch

                                                </span>


                                                <strong>
                                                    {{ $coupon->application_label }}
                                                </strong>

                                            </div>

                                        </div>

                                    @endif



                                    {{-- ACQUISTO MINIMO --}}

                                    @if(
                                        !is_null($coupon->minimum_purchase)
                                        &&
                                        (float) $coupon->minimum_purchase > 0
                                    )

                                        <div class="coupon-info-row">

                                            <div class="coupon-info-icon">
                                                €
                                            </div>

                                            <div class="coupon-info-text">

                                                <span>
                                                    Acquisto minimo
                                                </span>

                                                <strong>

                                                    € {{ number_format(
                                                        $coupon->minimum_purchase,
                                                        2,
                                                        ',',
                                                        '.'
                                                    ) }}

                                                </strong>

                                            </div>

                                        </div>

                                    @endif



                                    {{-- VALIDITÀ --}}

                                    @if($startDate || $endDate)

                                        <div class="coupon-info-row">

                                            <div class="coupon-info-icon">
                                                📅
                                            </div>

                                            <div class="coupon-info-text">

                                                <span>
                                                    Validità
                                                </span>


                                                <strong>

                                                    @if(
                                                        $startDate
                                                        &&
                                                        $endDate
                                                        &&
                                                        $startDate->isSameDay($endDate)
                                                    )

                                                        Valido il
                                                        {{ $startDate->format('d/m/Y') }}

                                                    @elseif($startDate && $endDate)

                                                        {{ $startDate->format('d/m/Y') }}
                                                        —
                                                        {{ $endDate->format('d/m/Y') }}

                                                    @elseif($startDate)

                                                        Dal
                                                        {{ $startDate->format('d/m/Y') }}

                                                    @elseif($endDate)

                                                        Fino al
                                                        {{ $endDate->format('d/m/Y') }}

                                                    @endif

                                                </strong>

                                            </div>

                                        </div>

                                    @endif


                                </div>



                                {{-- BOTTONE --}}

                                <button
                                    type="button"
                                    class="coupon-button"
                                >

                                    Scopri il codice

                                    <span class="coupon-button-arrow">
                                        →
                                    </span>

                                </button>


                            </div>

                        </div>

                    </div>


                @empty


                    <div class="col-12">

                        <div class="coupon-empty">

                            <h5>
                                Nessun coupon disponibile
                            </h5>

                            <p class="mb-0">
                                Al momento non ci sono promozioni attive.
                            </p>

                        </div>

                    </div>


                @endforelse

            </div>



            {{-- =====================================================
                 PAGINAZIONE
            ====================================================== --}}

            @if($promotion->hasPages())

                <div class="coupon-pagination">

                    {{ $promotion->links() }}

                </div>

            @endif



            {{-- =====================================================
                 BOX INFORMATIVI FINALI
            ====================================================== --}}

            <section class="coupon-benefits">


                <div class="coupon-benefits-heading">

                    <h4>
                        Codici sconto esclusivi
                    </h4>

                    <p>
                        Approfitta delle nostre promozioni e risparmia sui tuoi acquisti.
                    </p>

                </div>



                <div class="row g-3">


                    {{-- =============================================
                         CODICI SCONTO
                    ============================================== --}}

                    <div class="col-12 col-md-6 col-xl-3 d-flex">

                        <div class="benefit-box benefit-green">

                            <div class="benefit-icon">
                                %
                            </div>

                            <div class="benefit-content">

                                <h5>
                                    Codici sconto esclusivi
                                </h5>

                                <p>
                                    Approfitta delle nostre promozioni
                                    e risparmia sui tuoi acquisti.
                                </p>

                            </div>

                        </div>

                    </div>



                    {{-- =============================================
                         ACQUISTI SICURI
                    ============================================== --}}

                    <div class="col-12 col-md-6 col-xl-3 d-flex">

                        <div class="benefit-box benefit-blue">

                            <div class="benefit-icon">
                                ✓
                            </div>

                            <div class="benefit-content">

                                <h5>
                                    Acquisti sicuri
                                </h5>

                                <p>
                                    Pagamenti protetti e spedizioni
                                    veloci e affidabili.
                                </p>

                            </div>

                        </div>

                    </div>



                    {{-- =============================================
                         SEMPRE CON TE
                    ============================================== --}}

                    <div class="col-12 col-md-6 col-xl-3 d-flex">

                        <div class="benefit-box benefit-green">

                            <div class="benefit-icon">
                                🚚
                            </div>

                            <div class="benefit-content">

                                <h5>
                                    Sempre con te
                                </h5>

                                <p>
                                    La tua farmacia online,
                                    sempre a portata di click.
                                </p>

                            </div>

                        </div>

                    </div>



                    {{-- =============================================
                         CATALOGO
                    ============================================== --}}

                    <div class="col-12 col-md-6 col-xl-3 d-flex">

                        <div class="benefit-box benefit-orange">

                            <div class="benefit-icon">
                                🛍
                            </div>

                            <div class="benefit-content">

                                <h5>
                                    Oltre 10.000 prodotti
                                </h5>

                                <p>
                                    Un vasto catalogo di farmaci,
                                    integratori e prodotti per il benessere.
                                </p>

                            </div>

                        </div>

                    </div>


                </div>

            </section>


        </div>

    </div>

</main>



{{-- =============================================================
     MODAL COUPON
============================================================= --}}

<div
    class="modal fade coupon-modal"
    id="exampleModal"
    tabindex="-1"
    role="dialog"
    aria-hidden="true"
>

    <div
        class="modal-dialog modal-dialog-centered"
        role="document"
    >

        <div class="modal-content">


            {{-- HEADER --}}

            <div class="modal-header">

                <h5 class="modal-title">
                    Il tuo coupon
                </h5>


                <button
                    type="button"
                    id="closeScontoModal"
                    class="coupon-modal-close"
                    aria-label="Chiudi"
                >
                    &times;
                </button>

            </div>



            {{-- BODY --}}

            <div class="modal-body coupon-modal-body">


                {{-- AREA COLORATA --}}

                <div class="modal-theme-area text-center">


                    <div
                        class="modal-coupon-icon"
                        id="modalDiscountIcon"
                    >
                        %
                    </div>


                    <div
                        class="modal-discount"
                        id="modalDiscount"
                    ></div>


                    <div class="modal-discount-label">
                        di sconto
                    </div>


                    <div class="modal-coupon-title">
                        Un vantaggio pensato per te
                    </div>


                    <div
                        id="description"
                        class="modal-description"
                    ></div>


                </div>



                {{-- AREA BIANCA --}}

                <div class="modal-white-area text-center">


                    {{-- CODICE --}}

                    <div class="coupon-code-box">

                        <h6 id="couponCode"></h6>

                        <span class="coupon-code-copy-icon">
                            ⧉
                        </span>

                    </div>



                    {{-- COPIA --}}

                    <button
                        type="button"
                        id="copyCoupon"
                    >
                        Copia codice
                    </button>


                    <div id="copySuccess">
                        ✓ Codice copiato!
                    </div>



                    {{-- INFO --}}

                    <div class="modal-coupon-info">


                        {{-- APPLICAZIONE --}}

                        <div
                            class="modal-info-row"
                            id="modalApplicationRow"
                            style="display:none;"
                        >

                            <div
                                class="modal-info-icon"
                                id="modalApplicationIcon"
                            >
                                🛒
                            </div>


                            <div class="modal-info-text">

                                <span id="modalApplicationType">
                                    Valido su
                                </span>

                                <strong id="modalApplicationLabel"></strong>

                            </div>

                        </div>



                        {{-- LINK PRODOTTO / BRAND / CATEGORIA --}}

                        <div
                            id="modalApplicationLinkRow"
                            style="display:none;"
                        >

                            <a
                                href="#"
                                id="modalApplicationLink"
                                class="coupon-application-link"
                            >

                                <span id="modalApplicationLinkText">
                                    Scopri i prodotti
                                </span>

                                <span class="coupon-application-link-arrow">
                                    →
                                </span>

                            </a>

                        </div>



                        {{-- ACQUISTO MINIMO --}}

                        <div
                            class="modal-info-row"
                            id="modalMinimumRow"
                            style="display:none;"
                        >

                            <div class="modal-info-icon">
                                €
                            </div>


                            <div class="modal-info-text">

                                <span>
                                    Acquisto minimo
                                </span>

                                <strong id="modalMinimum"></strong>

                            </div>

                        </div>



                        {{-- VALIDITÀ --}}

                        <div
                            class="modal-info-row"
                            id="modalValidityRow"
                            style="display:none;"
                        >

                            <div class="modal-info-icon">
                                📅
                            </div>


                            <div class="modal-info-text">

                                <span>
                                    Validità
                                </span>

                                <strong id="modalValidity"></strong>

                            </div>

                        </div>



                        {{-- UTILIZZO --}}

                        <div class="modal-info-row">

                            <div class="modal-info-icon">
                                🛒
                            </div>


                            <div class="modal-info-text">

                                <span>
                                    Come utilizzarlo
                                </span>

                                <strong>
                                    Inserisci il codice al checkout
                                </strong>

                            </div>

                        </div>


                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection



@section('js')

<script>

$(document).ready(function () {


    /* =========================================================
       FORMATTA DATA
    ========================================================= */

    function formatCouponDate(date) {

        if (!date) {
            return null;
        }

        const cleanDate =
            date.toString().substring(0, 10);

        const parts =
            cleanDate.split('-');

        if (parts.length !== 3) {
            return cleanDate;
        }

        return (
            parts[2]
            + '/'
            + parts[1]
            + '/'
            + parts[0]
        );
    }



    /* =========================================================
       CLICK COUPON
    ========================================================= */

    $(document).on(
        'click',
        '.box-cliccabile',
        function () {


            const token =
                $(this).data('id');


            const theme =
                $(this).data('theme')
                || 'theme-green';


            /* =================================================
               RESET MODAL
            ================================================== */

            $('#exampleModal')
                .removeClass(
                    'theme-pink ' +
                    'theme-blue ' +
                    'theme-green ' +
                    'theme-orange ' +
                    'fixed-discount ' +
                    'percentage-discount'
                )
                .addClass(theme);


            $('#modalApplicationLinkRow')
                .hide();


            $('#modalApplicationLink')
                .attr('href', '#');



            /* =================================================
               AJAX
            ================================================== */

            $.ajax({

                url: "{{ route('coupon.get') }}",

                type: "POST",

                data: {

                    token: token,

                    _token: "{{ csrf_token() }}"

                },


                success: function(response) {


                    if (!response.success) {

                        alert(
                            response.message
                            ??
                            'Coupon non disponibile.'
                        );

                        return;
                    }


                    const coupon =
                        response.coupon;



                    /* =========================================
                       CODICE
                    ========================================== */

                    $('#couponCode')
                        .text(
                            coupon.name ?? ''
                        );



                    /* =========================================
                       DESCRIZIONE
                    ========================================== */

                    $('#description')
                        .text(
                            coupon.description ?? ''
                        );



                    /* =========================================
                       SCONTO FISSO €
                    ========================================== */

                    if (
                        coupon.fixDiscount !== null
                        &&
                        coupon.fixDiscount !== undefined
                        &&
                        parseFloat(coupon.fixDiscount) > 0
                    ) {


                        const discount =
                            parseFloat(
                                coupon.fixDiscount
                            )
                            .toLocaleString(
                                'it-IT',
                                {
                                    maximumFractionDigits: 2
                                }
                            );


                        $('#modalDiscount')
                            .text(
                                '-' + discount + '€'
                            );


                        $('#modalDiscountIcon')
                            .text('€');


                        $('#exampleModal')
                            .removeClass(
                                'percentage-discount'
                            )
                            .addClass(
                                'fixed-discount'
                            );

                    }



                    /* =========================================
                       SCONTO PERCENTUALE
                    ========================================== */

                    else if (
                        coupon.percentage !== null
                        &&
                        coupon.percentage !== undefined
                        &&
                        parseFloat(coupon.percentage) > 0
                    ) {


                        const percentage =
                            parseFloat(
                                coupon.percentage
                            )
                            .toLocaleString(
                                'it-IT',
                                {
                                    maximumFractionDigits: 2
                                }
                            );


                        $('#modalDiscount')
                            .text(
                                '-' + percentage + '%'
                            );


                        $('#modalDiscountIcon')
                            .text('%');


                        $('#exampleModal')
                            .removeClass(
                                'fixed-discount'
                            )
                            .addClass(
                                'percentage-discount'
                            );

                    }


                    else {


                        $('#modalDiscount')
                            .text('');


                        $('#modalDiscountIcon')
                            .text('🏷');


                        $('#exampleModal')
                            .removeClass(
                                'fixed-discount ' +
                                'percentage-discount'
                            );

                    }



                    /* =========================================
                       APPLICAZIONE
                    ========================================== */

                    if (
                        coupon.application_type
                        &&
                        coupon.application_label
                    ) {


                        let applicationTitle =
                            'Valido su';


                        let applicationIcon =
                            '🛒';


                        switch (
                            coupon.application_type
                        ) {


                            case 'product':

                                applicationTitle =
                                    'Prodotto';

                                applicationIcon =
                                    '📦';

                                break;


                            case 'brand':

                                applicationTitle =
                                    'Brand';

                                applicationIcon =
                                    '🏷';

                                break;


                            case 'category':

                                applicationTitle =
                                    'Categoria';

                                applicationIcon =
                                    '▦';

                                break;


                            case 'subcategory':

                                applicationTitle =
                                    'Categoria';

                                applicationIcon =
                                    '▦';

                                break;


                            case 'all_products':

                                applicationTitle =
                                    'Valido su';

                                applicationIcon =
                                    '🛒';

                                break;

                        }


                        $('#modalApplicationType')
                            .text(
                                applicationTitle
                            );


                        $('#modalApplicationIcon')
                            .text(
                                applicationIcon
                            );


                        $('#modalApplicationLabel')
                            .text(
                                coupon.application_label
                            );


                        $('#modalApplicationRow')
                            .show();

                    }


                    else {


                        $('#modalApplicationLabel')
                            .text('');


                        $('#modalApplicationRow')
                            .hide();

                    }



                    /* =========================================
                       LINK DESTINAZIONE
                    ========================================== */

                    if (
                        coupon.application_url
                        &&
                        coupon.application_type !== 'all_products'
                    ) {


                        let linkText =
                            'Scopri i prodotti';


                        switch (
                            coupon.application_type
                        ) {


                            case 'product':

                                linkText =
                                    'Vai al prodotto';

                                break;


                            case 'brand':

                                linkText =
                                    'Scopri i prodotti del brand';

                                break;


                            case 'category':

                                linkText =
                                    'Scopri i prodotti';

                                break;


                            case 'subcategory':

                                linkText =
                                    'Scopri i prodotti';

                                break;

                        }


                        $('#modalApplicationLink')
                            .attr(
                                'href',
                                coupon.application_url
                            );


                        $('#modalApplicationLinkText')
                            .text(
                                linkText
                            );


                        $('#modalApplicationLinkRow')
                            .show();

                    }


                    else {


                        $('#modalApplicationLink')
                            .attr(
                                'href',
                                '#'
                            );


                        $('#modalApplicationLinkText')
                            .text(
                                'Scopri i prodotti'
                            );


                        $('#modalApplicationLinkRow')
                            .hide();

                    }



                    /* =========================================
                       ACQUISTO MINIMO
                    ========================================== */

                    if (
                        coupon.minimum_purchase !== null
                        &&
                        coupon.minimum_purchase !== undefined
                        &&
                        parseFloat(
                            coupon.minimum_purchase
                        ) > 0
                    ) {


                        const minimum =
                            parseFloat(
                                coupon.minimum_purchase
                            )
                            .toLocaleString(
                                'it-IT',
                                {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                }
                            );


                        $('#modalMinimum')
                            .text(
                                '€ ' + minimum
                            );


                        $('#modalMinimumRow')
                            .show();

                    }


                    else {


                        $('#modalMinimum')
                            .text('');


                        $('#modalMinimumRow')
                            .hide();

                    }



                    /* =========================================
                       VALIDITÀ
                    ========================================== */

                    const startDate =
                        coupon.start_date
                            ?
                            coupon.start_date
                                .toString()
                                .substring(0, 10)
                            :
                            null;


                    const endDate =
                        coupon.end_date
                            ?
                            coupon.end_date
                                .toString()
                                .substring(0, 10)
                            :
                            null;


                    let validityText =
                        '';


                    if (
                        startDate
                        &&
                        endDate
                    ) {


                        if (
                            startDate === endDate
                        ) {


                            validityText =
                                'Valido il '
                                +
                                formatCouponDate(
                                    startDate
                                );

                        }


                        else {


                            validityText =
                                'Dal '
                                +
                                formatCouponDate(
                                    startDate
                                )
                                +
                                ' al '
                                +
                                formatCouponDate(
                                    endDate
                                );

                        }

                    }


                    else if (startDate) {


                        validityText =
                            'Dal '
                            +
                            formatCouponDate(
                                startDate
                            );

                    }


                    else if (endDate) {


                        validityText =
                            'Fino al '
                            +
                            formatCouponDate(
                                endDate
                            );

                    }



                    if (validityText) {


                        $('#modalValidity')
                            .text(
                                validityText
                            );


                        $('#modalValidityRow')
                            .show();

                    }


                    else {


                        $('#modalValidity')
                            .text('');


                        $('#modalValidityRow')
                            .hide();

                    }



                    /* =========================================
                       RESET COPIA
                    ========================================== */

                    $('#copySuccess')
                        .hide();


                    $('#copyCoupon')
                        .text(
                            'Copia codice'
                        );



                    /* =========================================
                       APERTURA MODAL
                    ========================================== */

                    $('#exampleModal')
                        .modal('show');

                },


                /* =============================================
                   ERRORE
                ============================================== */

                error: function(xhr) {


                    alert(
                        xhr.responseJSON?.message
                        ??
                        'Errore durante il recupero del coupon.'
                    );

                }

            });

        }
    );



    /* =========================================================
       CHIUSURA MODAL
    ========================================================= */

    $('#closeScontoModal').on(
        'click',
        function () {

            $('#exampleModal')
                .modal('hide');

        }
    );



    /* =========================================================
       COPIA CODICE
    ========================================================= */

    $('#copyCoupon').on(
        'click',
        function () {


            const code =
                $('#couponCode')
                    .text()
                    .trim();


            if (!code) {
                return;
            }


            if (
                navigator.clipboard
                &&
                window.isSecureContext
            ) {


                navigator.clipboard
                    .writeText(code)
                    .then(function () {

                        showCopiedMessage();

                    })
                    .catch(function () {

                        fallbackCopy(code);

                    });

            }


            else {


                fallbackCopy(code);

            }

        }
    );



    /* =========================================================
       FALLBACK COPIA
    ========================================================= */

    function fallbackCopy(code) {


        const textarea =
            document.createElement(
                'textarea'
            );


        textarea.value =
            code;


        textarea.style.position =
            'fixed';


        textarea.style.opacity =
            '0';


        textarea.style.pointerEvents =
            'none';


        document.body.appendChild(
            textarea
        );


        textarea.focus();

        textarea.select();


        try {


            document.execCommand(
                'copy'
            );


            showCopiedMessage();

        }


        catch (error) {


            alert(
                'Impossibile copiare automaticamente il codice.'
            );

        }


        textarea.remove();

    }



    /* =========================================================
       MESSAGGIO COPIATO
    ========================================================= */

    function showCopiedMessage() {


        $('#copyCoupon')
            .text(
                'Codice copiato ✓'
            );


        $('#copySuccess')
            .stop(true, true)
            .fadeIn();


        setTimeout(
            function () {


                $('#copyCoupon')
                    .text(
                        'Copia codice'
                    );


                $('#copySuccess')
                    .fadeOut();

            },
            2000
        );

    }


});

</script>

@endsection