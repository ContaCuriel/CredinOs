{{-- resources/views/accounts/create.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Nueva Cuenta Contable</h1>
    <div class="card">
        <div class="card-body">
            <form action="{{ route('accounts.store') }}" method="POST">
                @include('accounts.partials._form', ['submitButtonText' => 'Crear Cuenta'])
            </form>
        </div>
    </div>
</div>
@endsection