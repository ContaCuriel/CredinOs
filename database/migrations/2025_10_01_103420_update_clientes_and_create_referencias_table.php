<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Añadimos las nuevas columnas a la tabla de clientes
        Schema::table('clientes', function (Blueprint $table) {
            // Sección 1: Datos Personales
            $table->date('fecha_nacimiento')->nullable()->after('apellido_materno');
            $table->string('genero', 20)->nullable()->after('fecha_nacimiento');
            $table->date('vencimiento_ine')->nullable()->after('curp');
            $table->string('estado_nacimiento', 100)->nullable()->after('vencimiento_ine');
            $table->string('nacionalidad', 100)->default('Mexicana')->nullable()->after('estado_nacimiento');
            $table->string('estado_civil', 50)->nullable()->after('nacionalidad');
            $table->integer('numero_hijos')->unsigned()->default(0)->nullable()->after('estado_civil');
            $table->integer('dependientes_economicos')->unsigned()->default(0)->nullable()->after('numero_hijos');
            $table->date('fecha_comprobante_domicilio')->nullable()->after('estado'); // 'estado' ya existe en tu form

            // Sección 2: Datos Laborales (Algunos ya los tenías)
            // 'nombre_negocio' ya existe
            $table->string('destino_credito')->nullable()->after('gastos_mensuales');
        });

        // Creamos una nueva tabla para las referencias del cliente
        Schema::create('cliente_referencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes', 'id_cliente')->onDelete('cascade');
            $table->string('nombre_referencia');
            $table->string('parentesco');
            $table->string('telefono');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cliente_referencias');

        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn([
                'fecha_nacimiento', 'genero', 'vencimiento_ine', 'estado_nacimiento',
                'nacionalidad', 'estado_civil', 'numero_hijos', 'dependientes_economicos',
                'fecha_comprobante_domicilio', 'destino_credito'
            ]);
        });
    }
};