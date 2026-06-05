<form id="nexiForm" method="POST" action="{{ $requestUrl }}">
    @foreach($params as $name => $value)
        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
    @endforeach
</form>

<script>
    document.getElementById('nexiForm').submit();
</script>