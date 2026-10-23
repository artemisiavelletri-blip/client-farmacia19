@extends('master')

@section('content')

    <main class="main">


        <!-- user dashboard -->
        <div class="user-area bg pt-100 pb-80">
            <div class="container">
                <div class="row">
                    <div class="col-lg-3">
                        @include('auth.settingsSideBar')
                    </div>
                    <div class="col-lg-9">
                        <div class="user-wrapper">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="user-card">
                                        <div class="user-card-header mobile-card-header">
                                            <h4 class="user-card-title">Indirizzo di Fatturazione</h4>
                                        </div>
                                        
                                        {{-- VERSIONE DESKTOP: tabella originale --}}
                                        <div class="f19-billing-desktop">
                                            <div class="mobile-table-custom">
                                                <table class="table table-borderless text-nowrap">
                                                    <thead>
                                                        <tr>
                                                            @if(!Auth::user()->user_type)
                                                                <th>Nome e Cognome</th>
                                                            @else
                                                                <th>Ragione Sociale</th>
                                                            @endif
                                                            <th>Indirizzo</th>
                                                            <th>Città</th>
                                                            <th>CAP</th>
                                                            <th>Azione</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            @if(!Auth::user()->user_type)
                                                                <td data-label="Nome e Cognome">
                                                                    <span class="table-list-code">
                                                                        {{ Auth::user()->name . ' ' . Auth::user()->surname }}
                                                                    </span>
                                                                </td>
                                                            @else
                                                                <td data-label="Ragione Sociale">
                                                                    <span class="table-list-code">
                                                                        {{ Auth::user()->company_society }}
                                                                    </span>
                                                                </td>
                                                            @endif

                                                            <td data-label="Indirizzo">{{ $billing_address->address }}</td>
                                                            <td data-label="Città">{{ Auth::user()->billingAddressesCity()->name }}</td>
                                                            <td data-label="CAP">{{ $billing_address->cap }}</td>
                                                            <td data-label="Azione">
                                                                <a href="/settings/edit-address/billing"
                                                                   class="btn btn-outline-secondary btn-sm rounded-2"
                                                                   data-tooltip="tooltip"
                                                                   title="Edit">
                                                                    <i class="far fa-pen"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        {{-- VERSIONE MOBILE: nuova scheda --}}
                                        <div class="f19-billing-mobile">

                                            <div class="f19-billing-card">

                                                <div class="f19-billing-row">
                                                    <span class="f19-billing-icon">
                                                        <i class="far fa-user"></i>
                                                    </span>

                                                    <div class="f19-billing-info">
                                                        <small>
                                                            {{ !Auth::user()->user_type
                                                                ? 'NOME E COGNOME'
                                                                : 'RAGIONE SOCIALE' }}
                                                        </small>
                                                        <strong>
                                                            @if(!Auth::user()->user_type)
                                                                {{ Auth::user()->name . ' ' . Auth::user()->surname }}
                                                            @else
                                                                {{ Auth::user()->company_society }}
                                                            @endif
                                                        </strong>
                                                    </div>
                                                </div>

                                                <div class="f19-billing-row">
                                                    <span class="f19-billing-icon">
                                                        <i class="far fa-house"></i>
                                                    </span>

                                                    <div class="f19-billing-info">
                                                        <small>INDIRIZZO</small>
                                                        <strong>{{ $billing_address->address }}</strong>
                                                    </div>
                                                </div>

                                                <div class="f19-billing-row">
                                                    <span class="f19-billing-icon">
                                                        <i class="fas fa-location-dot"></i>
                                                    </span>

                                                    <div class="f19-billing-info">
                                                        <small>CITTÀ</small>
                                                        <strong>{{ Auth::user()->billingAddressesCity()->name }}</strong>
                                                    </div>
                                                </div>

                                                <div class="f19-billing-row">
                                                    <span class="f19-billing-icon">
                                                        <i class="far fa-building"></i>
                                                    </span>

                                                    <div class="f19-billing-info">
                                                        <small>CAP</small>
                                                        <strong>{{ $billing_address->cap }}</strong>
                                                    </div>
                                                </div>

                                                {{-- Pulsante modifica in basso --}}
                                                <div class="f19-billing-actions">
                                                    <a href="/settings/edit-address/billing"
                                                       class="f19-billing-edit">
                                                        <i class="far fa-pen"></i>
                                                        Modifica indirizzo
                                                    </a>
                                                </div>

                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- user dashboard end -->

    </main>

@endsection