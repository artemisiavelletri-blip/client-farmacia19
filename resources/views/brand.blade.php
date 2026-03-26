@extends('master')

@section('content')

    <main class="main">


        <!-- brand area -->
        <div class="brand-area2 py-90">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 mx-auto">
                        <div class="site-heading text-center">
                            <span class="site-title-tagline">Brand</span>
                            <h2 class="site-title">I Nostri <span>Brand</span></h2>
                        </div>
                    </div>
                </div>
                <div class="row g-4">
                    <div class="col-md-8">
                        <h4>Tutti i brand di Farmacia19</h4>
                    </div>
                    <div class="col-md-4 align-right">
                        <input class="form-control" type="text" id="search" placeholder="Cerca brand...">
                    </div>

                    <div id="brand-results">
                        @include('brands.partials.table')
                    </div>
                </div>
            </div>
        </div>
        <!-- brand area end -->

    </main>

@endsection

@section('js')
    
    <script>
        document.getElementById('search').addEventListener('keyup', function() {
            let search = this.value;

            fetch(`{{ route('brand') }}?search=` + search, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(data => {
                document.getElementById('brand-results').innerHTML = data;
            });
        });
    </script>

@endsection