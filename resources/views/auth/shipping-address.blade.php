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
                                        <div class="user-card-header">
                                            <h4 class="user-card-title">Indirizzo di Spedizione</h4>
                                        </div>
                                        <div class="table-responsive">
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
                                                    <tr>
                                                        <td><span class="table-list-code">{{$shipping_address->recipient_name}}</span></td>
                                                        <td>{{$shipping_address->address}}</td>
                                                        <td>{{Auth::user()->shippingAddressesCity()->name}}</td>
                                                        <td>{{$shipping_address->cap}}</td>
                                                        <td>
                                                            <a href="/settings/edit-address/shipping" class="btn btn-outline-secondary btn-sm rounded-2" data-tooltip="tooltip" title="Edit"><i class="far fa-pen"></i></a>
                                                        </td>
                                                    </tr>                                                    
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