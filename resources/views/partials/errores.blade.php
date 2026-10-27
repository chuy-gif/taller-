@if($errors->any())
<div class="aviso error" role="alert">
    <strong>Revisa los siguientes datos:</strong>
    <ul>
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
