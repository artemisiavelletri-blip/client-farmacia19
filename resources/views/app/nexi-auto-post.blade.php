<!doctype html>
<html lang="it"><head><meta charset="utf-8"><meta name="referrer" content="no-referrer"><title>Connessione Nexi</title></head>
<body>
<p>Connessione sicura a Nexi in corso…</p>
<form id="nexi" action="{{ $endpoint }}" method="POST">
@foreach ($params as $name => $value)
<input type="hidden" name="{{ $name }}" value="{{ $value }}">
@endforeach
<noscript><button type="submit">Continua su Nexi</button></noscript>
</form>
<script>document.getElementById('nexi').submit();</script>
</body></html>
