{{-- resources/views/accounts/edit.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Cuenta Contable</h1>
    <div class="card">
        <div class="card-body">
            <form action="{{ route('accounts.update', $account) }}" method="POST">
                @method('PUT')
                @include('accounts.partials._form', ['account' => $account, 'submitButtonText' => 'Actualizar Cuenta'])
            </form>
        </div>
    </div>
</div>
@endsection