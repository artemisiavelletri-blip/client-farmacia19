@extends('master')

@section('css')

    <style type="text/css">
        
    </style>

@endsection

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
                                            <h4 class="user-card-title">Indirizzo di Spedizione</h4>
                                            <div class="user-card-header-right">
                                                <a href="/settings/create-address/shipping" class="theme-btn">
                                                    <span class="far fa-plus-circle"></span> Aggiungi Indirizzo
                                                </a>
                                            </div>
                                        </div>
                                        <div class="mobile-table-custom">
                                            <table class="table table-borderless text-nowrap">
                                                <thead>
                                                    <tr>
                                                        <th>Destinatario</th>
                                                        <th>Indirizzo</th>
                                                        <th>Città</th>
                                                        <th>CAP</th>
                                                        <th>Azione</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($shipping_address as $address)
                                                        <tr>
                                                            <td data-label="Destinatario"><span class="table-list-code">{{$address->recipient_name}}</span></td>
                                                            <td data-label="Indirizzo">{{$address->address}}</td>
                                                            <td data-label="Città">{{$address->city->name}}</td>
                                                            <td data-label="Cap">{{$address->cap}}</td>
                                                            <td data-label="Azione">
                                                                <a href="/settings/edit-address/shipping/{{$address->id}}" class="btn btn-outline-secondary btn-sm rounded-2" data-tooltip="tooltip" title="Edit"><i class="far fa-pen"></i></a>
                                                            </td>
                                                        </tr>
                                                    @endforeach                                                    
                                                </tbody>
                                            </table>
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