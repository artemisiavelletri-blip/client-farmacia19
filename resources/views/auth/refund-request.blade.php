@extends('master')

@section('css')

    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet">
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet">
    <style type="text/css">
        .filepond--credits {
            display: none!important;
        }
    </style>

@endsection

@section('content')

    <main class="main">


        <!-- user dashboard -->
        <div class="user-area bg pt-100 pb-80">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="user-wrapper">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="user-card user-order-detail">
                                        @if(session('success'))
                                            <div class="alert alert-success">
                                                {{ session('success') }}
                                            </div>
                                        @endif

                                        @if ($errors->any())
                                            <div class="alert alert-danger">
                                                <ul>
                                                    @foreach ($errors->all() as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                        <div class="user-card-header">
                                            <h4 class="user-card-title">Richiesta Rimborso Ordine #{{$order->order_number}}</h4>
                                            <div class="user-card-header-right">
                                                <a href="/order-detail/{{$order->order_number}}" class="theme-btn"><span class="fas fa-arrow-left"></span>Torna all'Ordine</a>
                                            </div> 
                                        </div>
                                        <div class="row mt-30">
                                            <div class="col-lg-12">
                                                <form action="/refund-request/{{$order->order_number}}" method="POST" enctype="multipart/form-data">
                                                    @csrf
                                                    <div class="row"> 
                                                        @foreach($order->items as $product)
                                                            <div class="col-md-2 mb-4">
                                                                <div class="card shadow-sm product-card">
                                                                    {{-- Immagine prodotto --}}
                                                                    <img src="{{ asset('/storage-admin/' . $product->product->image) }}" 
                                                                         class="card-img-top" style="height:180px; object-fit:contain; margin-top: 20px;" 
                                                                         alt="{{ $product->name }}">
                                                                    
                                                                    <div class="card-body">
                                                                        <h6 class="card-title">{{ $product->product_name }}</h6>

                                                                        <div class="form-check mb-2">
                                                                            <input type="checkbox" 
                                                                                   class="form-check-input select-product" 
                                                                                   name="products[]" 
                                                                                   value="{{ $product->product_id }}" 
                                                                                   id="product-{{ $product->product_id }}">
                                                                            <label class="form-check-label" for="product-{{ $product->product_id }}">
                                                                                Seleziona per reso
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    <h6>Qual è il motivo del reso? (obbligatorio)</h6>
                                                    <ul class="mt-30">
                                                        @foreach($reason_return as $return)
                                                            <li>
                                                                <input type="radio" name="option" value="{{$return->id}}">
                                                                <span>{{$return->reason}}</span>
                                                            </li>
                                                        @endforeach                                                    
                                                    </ul>
                                                    <div class="mt-30">
                                                        <h6>Commenti (obbligatorio)</h6>
                                                        <textarea class="form-control mt-15" name="message" placeholder="Forniscici maggiori dettagli" required></textarea>
                                                    </div>
                                                    <div class="imgUpload mt-30 hidden">
                                                        <h6>Immagini (min 2 max 3 obbligatorie)</h6>
                                                        <div mv-app="swanFilepond" mv-autosave="3" class="mv-autoedit is-absoluteA" mv-storage="https://github.com/GalinhaLX/hello2" mv-bar="with clear with switch-data" mv-plugins="clear cropper">

                                                            <input type="file" name="images[]" multiple data-max-files="3"/>

                                                            <img property="targetImage" src="https://upload.wikimedia.org/wikipedia/commons/8/89/HD_transparent_picture.png" style="height: 50px!important;" />
                                                        

                                                        </div>
                                                    </div>
                                                    <hr class="mb-30">
                                                    <button class="btn btn-sm theme-btn">Invia Richiesta</button>
                                                </form>
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

    <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.js"></script>

    <script>
        FilePond.registerPlugin(FilePondPluginImagePreview);

        const inputElement = document.querySelector('input[type="file"]');
        const filepondOutput = document.querySelector("#monImage");

        const pond = FilePond.create(inputElement, {
            labelIdle: `Trascina le immagini o <span class="filepond--label-action">Sfoglia</span>`,
            storeAsFile: true,
            onaddfile(err, file) {

                setTimeout(function () {
                    var totalHeight = 0;

                    $('.filepond--item').each(function () {
                        totalHeight += $(this).height();
                    });

                    totalHeight += 70;
                    if(totalHeight > 658){
                        totalHeight = 658;
                    }
                    $('.mv-autoedit').css('margin-bottom', totalHeight + 'px');
                }, 100);                
                filepondOutput.src = URL.createObjectURL(file.file);
            },
            onremovefile(error,file) {
                setTimeout(function () {
                    var totalHeight = 0;

                    $('.filepond--item').each(function () {
                        totalHeight += $(this).height();
                    });

                    if(totalHeight > 0){
                        totalHeight += 70;
                    }

                    $('.mv-autoedit').css('margin-bottom', totalHeight + 'px');
                }, 1000);   
            }
        });

        $('input[type="radio"]').on('change', function () {
            var id = $(this).val();

            if(id == 1 || id == 2 || id == 8 || id == 10){
                $('.imgUpload').removeClass('hidden');
            } else {
                if(!$('.imgUpload').hasClass('hidden')){
                    $('.imgUpload').addClass('hidden');
                }
            }
        });
    </script>

@endsection