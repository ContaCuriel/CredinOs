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
        Schema::create('empleados', function (Blueprint $table) {
            $table->id('id_empleado'); // PK
            $table->string('nombre_completo', 255);
            $table->unsignedBigInteger('id_puesto'); // FK
            $table->unsignedBigInteger('id_sucursal'); // FK
            $table->date('fecha_ingreso');
            $table->date('fecha_nacimiento');
            $table->string('direccion', 500);
            $table->string('curp', 18)->unique();
            $table->string('rfc', 13)->unique();
            $table->string('nss', 11)->unique();
            $table->boolean('imss')->default(false);
            $table->string('cuenta_bancaria', 20)->nullable(); // Hecho opcional
            $table->string('banco', 100)->nullable();          // Hecho opcional
            $table->string('telefono', 15);                   // Requerido (como estaba)
            $table->string('contacto_emerg_nombre', 255)->nullable(); // Hecho opcional
            $table->string('contacto_emerg_telefono', 15)->nullable(); // Hecho opcional
            $table->string('status', 10)->default('Alta');
            $table->date('fecha_baja')->nullable();
            $table->string('motivo_baja', 500)->nullable();
            $table->timestamps();

            // Definimos las Llaves Foráneas (FK)
            $table->foreign('id_puesto')->references('id_puesto')->on('puestos');
            $table->foreign('id_sucursal')->references('id_sucursal')->on('sucursales');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empleados');
    }
};
