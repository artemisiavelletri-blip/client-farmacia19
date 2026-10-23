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
                                            <div class="user-card-header-right f19-shipping-desktop">
                                                <a href="/settings/create-address/shipping" class="theme-btn">
                                                    <span class="far fa-plus-circle"></span> Aggiungi Indirizzo
                                                </a>
                                            </div>
                                        </div>
                                        
                                        {{-- ==========================================
                                             DESKTOP - TABELLA ORIGINALE
                                             ========================================== --}}

                                        <div class="f19-shipping-desktop">
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
                                                                <td data-label="Destinatario">
                                                                    <span class="table-list-code">
                                                                        {{ $address->recipient_name }}
                                                                    </span>
                                                                </td>

                                                                <td data-label="Indirizzo">
                                                                    {{ $address->address }}
                                                                </td>

                                                                <td data-label="Città">
                                                                    {{ $address->city->name }}
                                                                </td>

                                                                <td data-label="Cap">
                                                                    {{ $address->cap }}
                                                                </td>

                                                                <td data-label="Azione">
                                                                    <a href="/settings/edit-address/shipping/{{ $address->id }}"
                                                                       class="btn btn-outline-secondary btn-sm rounded-2"
                                                                       data-tooltip="tooltip"
                                                                       title="Edit">
                                                                        <i class="far fa-pen"></i>
                                                                    </a>

                                                                    <a href="#"
                                                                       class="btn btn-outline-danger btn-sm rounded-2 delete-address"
                                                                       data-tooltip="tooltip"
                                                                       title="Delete"
                                                                       data-id="{{ $address->id }}">
                                                                        <i class="far fa-trash-can"></i>
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>


                                        {{-- ==========================================
                                             MOBILE - NUOVE SCHEDE INDIRIZZI
                                             ========================================== --}}

                                        <div class="f19-shipping-mobile">

                                            <div class="f19-shipping-grid">

                                                @forelse($shipping_address as $address)

                                                    <div class="f19-shipping-card">

                                                        {{-- DESTINATARIO --}}
                                                        <div class="f19-shipping-row">
                                                            <span class="f19-shipping-icon">
                                                                <i class="far fa-user"></i>
                                                            </span>

                                                            <div class="f19-shipping-info">
                                                                <small>DESTINATARIO</small>
                                                                <strong>{{ $address->recipient_name }}</strong>
                                                            </div>
                                                        </div>

                                                        {{-- INDIRIZZO --}}
                                                        <div class="f19-shipping-row">
                                                            <span class="f19-shipping-icon">
                                                                <i class="far fa-house"></i>
                                                            </span>

                                                            <div class="f19-shipping-info">
                                                                <small>INDIRIZZO</small>
                                                                <strong>{{ $address->address }}</strong>
                                                            </div>
                                                        </div>

                                                        {{-- CITTÀ --}}
                                                        <div class="f19-shipping-row">
                                                            <span class="f19-shipping-icon">
                                                                <i class="fas fa-location-dot"></i>
                                                            </span>

                                                            <div class="f19-shipping-info">
                                                                <small>CITTÀ</small>
                                                                <strong>{{ $address->city->name }}</strong>
                                                            </div>
                                                        </div>

                                                        {{-- CAP --}}
                                                        <div class="f19-shipping-row">
                                                            <span class="f19-shipping-icon">
                                                                <i class="far fa-building"></i>
                                                            </span>

                                                            <div class="f19-shipping-info">
                                                                <small>CAP</small>
                                                                <strong>{{ $address->cap }}</strong>
                                                            </div>
                                                        </div>

                                                        {{-- AZIONI --}}
                                                        <div class="f19-shipping-actions">

                                                            <a href="/settings/edit-address/shipping/{{ $address->id }}"
                                                               class="f19-shipping-edit">
                                                                <i class="far fa-pen"></i>
                                                                Modifica indirizzo
                                                            </a>

                                                            <a href="#"
                                                               class="f19-shipping-delete delete-address"
                                                               data-id="{{ $address->id }}"
                                                               data-tooltip="tooltip"
                                                               title="Elimina indirizzo"
                                                               aria-label="Elimina indirizzo">
                                                                <i class="far fa-trash-can"></i>
                                                            </a>

                                                        </div>

                                                    </div>

                                                @empty

                                                    <div class="f19-shipping-empty">
                                                        <i class="far fa-map"></i>
                                                        <h5>Nessun indirizzo salvato</h5>
                                                        <p>Aggiungi un indirizzo per le tue consegne.</p>
                                                    </div>

                                                @endforelse

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

@section('js')

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.querySelectorAll('.delete-address').forEach(btn => {
            btn.addEventListener('click', async () => {

                Swal.fire({
                    title: "Sei sicuro di voler eliminare questo metodo di pagamento?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Elimina"
                }).then(async (result) => {   // 👈 async qui
                    if (result.isConfirmed) {
                        const id = btn.dataset.id;

                        const res = await fetch(`/settings/delete-address/delete/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        });

                        const data = await res.json();

                        if (data.success) {
                            location.reload();
                        } else {
                            alert(data.error);
                        }
                    }
                });
            });
        });
    </script>

@endsection