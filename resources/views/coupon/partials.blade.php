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
       CARD
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
       TEMI
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
       BUTTON
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
       EMPTY
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
       MODAL THEMES
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
        max-width: 510px;
    }

    .coupon-modal .modal-content {
        border: 0;

        border-radius: 22px;

        overflow: hidden;

        box-shadow: 0 25px 80px rgba(0,0,0,.28);
    }


    /* =========================================================
       MODAL HEADER
    ========================================================= */

    .coupon-modal .modal-header {
        position: relative;

        display: flex;
        align-items: center;

        padding: 20px 25px;

        background: #fff;

        border-bottom: 1px solid #edf1f4;
    }

    .coupon-modal .modal-title {
        margin: 0;

        color: #003b5c;

        font-size: 19px;
        font-weight: 800;
    }

    .coupon-modal-close {
        position: absolute;

        right: 15px;
        top: 50%;

        transform: translateY(-50%);

        padding: 5px 10px;

        border: 0;

        background: transparent;

        color: #536273;

        font-size: 30px;
        line-height: 1;

        cursor: pointer;
    }


    /* =========================================================
       MODAL BODY
    ========================================================= */

    .coupon-modal-body {
        position: relative;

        padding: 0;

        overflow: hidden;

        background: #fff;
    }


    /* =========================================================
       MODAL HERO
    ========================================================= */

    .modal-theme-area {
        position: relative;

        padding: 32px 30px 25px;

        background:
            radial-gradient(
                circle at 90% 15%,
                rgba(255,255,255,.65) 0,
                rgba(255,255,255,.65) 45px,
                transparent 46px
            ),
            var(--modal-soft);

        overflow: hidden;
    }

    .modal-theme-area::before {
        content: "";

        position: absolute;

        width: 150px;
        height: 150px;

        border-radius: 50%;

        right: -60px;
        top: -60px;

        background: var(--modal-color);

        opacity: .04;
    }

    .modal-theme-area::after {
        content: "%";

        position: absolute;

        right: 5px;
        bottom: -45px;

        font-size: 160px;
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
       MODAL DISCOUNT
    ========================================================= */

    .modal-coupon-icon {
        width: 65px;
        height: 65px;

        margin: 0 auto 12px;

        display: flex;
        align-items: center;
        justify-content: center;

        border-radius: 50%;

        background: rgba(255,255,255,.70);

        color: var(--modal-color);

        font-size: 28px;
        font-weight: 800;
    }

    .modal-discount {
        color: var(--modal-color);

        font-size: 60px;
        line-height: 1;

        font-weight: 900;

        letter-spacing: -2px;
    }

    .modal-discount-label {
        margin-top: 5px;

        color: var(--modal-color);

        font-size: 18px;
        font-weight: 800;

        text-transform: uppercase;
    }

    .modal-coupon-title {
        margin-top: 25px;

        color: #003b5c;

        font-size: 20px;
        font-weight: 800;
    }

    .modal-description {
        max-width: 410px;

        margin: 10px auto 0;

        color: #617386;

        font-size: 14px;
        line-height: 1.6;
    }


    /* =========================================================
       MODAL WHITE AREA
    ========================================================= */

    .modal-white-area {
        padding: 22px 30px 30px;

        background: #fff;
    }


    /* =========================================================
       COUPON CODE
    ========================================================= */

    .coupon-code-box {
        position: relative;

        padding: 17px 50px 17px 20px;

        background: var(--modal-soft);

        border: 1px dashed var(--modal-color);

        border-radius: 12px;
    }

    #couponCode {
        margin: 0;

        color: #003b5c;

        font-size: 21px;
        font-weight: 900;

        letter-spacing: .8px;

        overflow-wrap: anywhere;
    }

    .coupon-code-copy-icon {
        position: absolute;

        right: 18px;
        top: 50%;

        transform: translateY(-50%);

        color: var(--modal-color);

        font-size: 20px;
    }


    /* =========================================================
       COPY BUTTON
    ========================================================= */

    #copyCoupon {
        width: 100%;

        margin-top: 13px;

        padding: 13px 15px;

        border: 0;

        border-radius: 10px;

        background: var(--modal-color);

        color: #fff;

        cursor: pointer;

        font-weight: 700;

        transition: .2s ease;
    }

    #copyCoupon:hover {
        filter: brightness(.90);
    }

    #copySuccess {
        display: none;

        margin-top: 12px;

        padding: 11px;

        background: var(--modal-soft);

        border-radius: 9px;

        color: var(--modal-color);

        font-size: 13px;
        font-weight: 700;
    }


    /* =========================================================
       INFO MODAL
    ========================================================= */

    .modal-coupon-info {
        margin-top: 23px;

        padding-top: 18px;

        border-top: 1px solid #edf1f4;

        text-align: left;
    }

    .modal-info-row {
        display: flex;
        align-items: center;

        gap: 11px;

        margin-bottom: 12px;
    }

    .modal-info-icon {
        width: 36px;
        height: 36px;

        flex: 0 0 36px;

        display: flex;
        align-items: center;
        justify-content: center;

        border-radius: 50%;

        background: var(--modal-soft);

        color: var(--modal-color);

        font-size: 16px;
        font-weight: 700;
    }

    .modal-info-text {
        min-width: 0;

        font-size: 12px;

        color: #8290a0;
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
       PAGINATION
    ========================================================= */

    .coupon-pagination {
        margin-top: 35px;
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

        .modal-theme-area {
            padding: 27px 20px 22px;
        }

        .modal-white-area {
            padding: 20px;
        }

        .modal-discount {
            font-size: 50px;
        }

    }

</style>


<main class="main">

    <div class="coupon-page">

        <div class="container">

            {{-- HEADER --}}

            <div class="coupon-page-header">

                <h3>
                    Coupon per te
                </h3>

                <p>
                    Approfitta delle nostre promozioni e risparmia sui tuoi acquisti.
                </p>

            </div>


            {{-- =====================================================
                 COUPON
            ====================================================== --}}

            <div class="row g-4">

                @forelse($promotion as $coupon)

                    @php

                        /*
                         * TEMI
                         */

                        $themes = [
                            'theme-pink',
                            'theme-blue',
                            'theme-green',
                            'theme-orange'
                        ];


                        /*
                         * Mantiene alternanza colori
                         * anche tra le pagine.
                         */

                        $globalIndex =
                            (($promotion->currentPage() - 1)
                            * $promotion->perPage())
                            + $loop->index;


                        $theme =
                            $themes[
                                $globalIndex % count($themes)
                            ];


                        /*
                         * TIPO SCONTO
                         */

                        $isFixed =
                            !is_null($coupon->fixDiscount)
                            &&
                            (float)$coupon->fixDiscount > 0;


                        /*
                         * DATE
                         */

                        $startDate =
                            $coupon->start_date
                                ? \Carbon\Carbon::parse(
                                    $coupon->start_date
                                )
                                : null;


                        $endDate =
                            $coupon->end_date
                                ? \Carbon\Carbon::parse(
                                    $coupon->end_date
                                )
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


                            {{-- =================================================
                                 HERO
                            ================================================== --}}

                            <div class="coupon-hero">


                                <div class="coupon-badge">

                                    <span>
                                        🏷
                                    </span>

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
                                        (float)$coupon->percentage > 0
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


                            {{-- =================================================
                                 BODY
                            ================================================== --}}

                            <div class="coupon-body">


                                <div class="coupon-title">

                                    Un vantaggio pensato per te

                                </div>


                                <div class="coupon-description">

                                    {{ $coupon->description }}

                                </div>


                                {{-- =================================================
                                     INFO
                                ================================================== --}}

                                <div class="coupon-info">


                                    {{-- =============================================
                                         DOVE SI APPLICA
                                    ============================================== --}}

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

                                                        {{-- Compatibilità con eventuale
                                                             vecchio application_type --}}

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

                                                            {{-- Anche se arriva ancora
                                                                 "subcategory" dal backend,
                                                                 visualizziamo Categoria --}}

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


                                    {{-- =============================================
                                         ACQUISTO MINIMO
                                    ============================================== --}}

                                    @if(
                                        !is_null($coupon->minimum_purchase)
                                        &&
                                        (float)$coupon->minimum_purchase > 0
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


                                    {{-- =============================================
                                         VALIDITÀ
                                    ============================================== --}}

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


                                                    {{-- STESSO GIORNO --}}

                                                    @if(
                                                        $startDate
                                                        &&
                                                        $endDate
                                                        &&
                                                        $startDate->isSameDay(
                                                            $endDate
                                                        )
                                                    )

                                                        Valido il
                                                        {{ $startDate->format('d/m/Y') }}


                                                    {{-- INTERVALLO --}}

                                                    @elseif(
                                                        $startDate
                                                        &&
                                                        $endDate
                                                    )

                                                        {{ $startDate->format('d/m/Y') }}

                                                        —

                                                        {{ $endDate->format('d/m/Y') }}


                                                    {{-- SOLO INIZIO --}}

                                                    @elseif($startDate)

                                                        Dal
                                                        {{ $startDate->format('d/m/Y') }}


                                                    {{-- SOLO FINE --}}

                                                    @elseif($endDate)

                                                        Fino al
                                                        {{ $endDate->format('d/m/Y') }}


                                                    @endif


                                                </strong>


                                            </div>


                                        </div>

                                    @endif


                                </div>


                                {{-- =================================================
                                     BUTTON
                                ================================================== --}}

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


        </div>

    </div>

</main>



{{-- =============================================================
     MODAL
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


            {{-- =================================================
                 HEADER
            ================================================== --}}

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


            {{-- =================================================
                 BODY
            ================================================== --}}

            <div class="modal-body coupon-modal-body">


                {{-- =================================================
                     HERO
                ================================================== --}}

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
                    >
                    </div>


                    <div class="modal-discount-label">

                        di sconto

                    </div>


                    <div class="modal-coupon-title">

                        Un vantaggio pensato per te

                    </div>


                    <div
                        id="description"
                        class="modal-description"
                    >
                    </div>


                </div>


                {{-- =================================================
                     WHITE AREA
                ================================================== --}}

                <div class="modal-white-area text-center">


                    {{-- =================================================
                         CODICE
                    ================================================== --}}

                    <div class="coupon-code-box">


                        <h6 id="couponCode">
                        </h6>


                        <span class="coupon-code-copy-icon">

                            ⧉

                        </span>


                    </div>


                    <button
                        type="button"
                        id="copyCoupon"
                    >

                        Copia codice

                    </button>


                    <div id="copySuccess">

                        ✓ Codice copiato!

                    </div>


                    {{-- =================================================
                         INFO
                    ================================================== --}}

                    <div class="modal-coupon-info">


                        {{-- =============================================
                             DOVE SI APPLICA
                        ============================================== --}}

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


                                <strong id="modalApplicationLabel">
                                </strong>


                            </div>


                        </div>


                        {{-- =============================================
                             ACQUISTO MINIMO
                        ============================================== --}}

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


                                <strong id="modalMinimum">
                                </strong>


                            </div>


                        </div>


                        {{-- =============================================
                             VALIDITÀ
                        ============================================== --}}

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


                                <strong id="modalValidity">
                                </strong>


                            </div>


                        </div>


                        {{-- =============================================
                             UTILIZZO
                        ============================================== --}}

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
            date
                .toString()
                .substring(0, 10);


        const parts =
            cleanDate.split('-');


        if (parts.length !== 3) {

            return cleanDate;

        }


        return (
            parts[2]
            +
            '/'
            +
            parts[1]
            +
            '/'
            +
            parts[0]
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
                ||
                'theme-green';


            /* =================================================
               TEMA MODAL
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


                /* =============================================
                   SUCCESS
                ============================================== */

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
                            coupon.name
                            ??
                            ''
                        );



                    /* =========================================
                       DESCRIZIONE
                    ========================================== */

                    $('#description')
                        .text(
                            coupon.description
                            ??
                            ''
                        );



                    /* =========================================
                       SCONTO FISSO
                    ========================================== */

                    if (
                        coupon.fixDiscount !== null
                        &&
                        coupon.fixDiscount !== undefined
                        &&
                        parseFloat(
                            coupon.fixDiscount
                        ) > 0
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
                                '-'
                                +
                                discount
                                +
                                '€'
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
                       PERCENTUALE
                    ========================================== */

                    else if (
                        coupon.percentage !== null
                        &&
                        coupon.percentage !== undefined
                        &&
                        parseFloat(
                            coupon.percentage
                        ) > 0
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
                                '-'
                                +
                                percentage
                                +
                                '%'
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
                       DOVE SI APPLICA
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



                            /*
                             * Compatibilità:
                             *
                             * se il backend restituisce
                             * ancora "subcategory",
                             * mostriamo comunque
                             * "Categoria".
                             */

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
                                '€ '
                                +
                                minimum
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



                    /*
                     * INIZIO + FINE
                     */

                    if (
                        startDate
                        &&
                        endDate
                    ) {


                        /*
                         * STESSO GIORNO
                         */

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


                        /*
                         * DATE DIVERSE
                         */

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


                    /*
                     * SOLO INIZIO
                     */

                    else if (startDate) {


                        validityText =
                            'Dal '
                            +
                            formatCouponDate(
                                startDate
                            );

                    }


                    /*
                     * SOLO FINE
                     */

                    else if (endDate) {


                        validityText =
                            'Fino al '
                            +
                            formatCouponDate(
                                endDate
                            );

                    }



                    /* =========================================
                       MOSTRA VALIDITÀ
                    ========================================== */

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
                       RESET COPY
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
                   ERROR
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
       CHIUDI MODAL
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
                    .then(
                        function () {

                            showCopiedMessage();

                        }
                    )
                    .catch(
                        function () {

                            fallbackCopy(code);

                        }
                    );

            }


            else {


                fallbackCopy(code);

            }

        }
    );



    /* =========================================================
       FALLBACK COPY
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
       FEEDBACK COPIA
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