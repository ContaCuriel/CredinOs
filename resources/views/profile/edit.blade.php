<x-app-layout>
    {{-- Título que aparecerá en la cabecera de tu layout --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mi Perfil') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid">
            <div class="row">
                {{-- Usamos una columna más ancha para el contenido del perfil --}}
                <div class="col-lg-8 mx-auto">

                    {{-- Tarjeta para la Información del Perfil --}}
                    <div class="card mb-4">
                        <div class="card-body">
                            @include('profile.partials.update-profile-information-form')
                        </div>
                    </div>

                    {{-- Tarjeta para Actualizar la Contraseña --}}
                    <div class="card mb-4">
                        <div class="card-body">
                            @include('profile.partials.update-password-form')
                        </div>
                    </div>

                    {{-- Tarjeta para Eliminar la Cuenta (Zona de Peligro) --}}
                    <div class="card border-danger">
                        <div class="card-body">
                            @include('profile.partials.delete-user-form')
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>