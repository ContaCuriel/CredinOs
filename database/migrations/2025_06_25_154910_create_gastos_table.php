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
    Schema::create('gastos', function (Blueprint $table) {
        $table->id();

        // ---- INICIO DE LA CORRECCIÓN FINAL ----
        // 1. Creamos la columna
        $table->unsignedBigInteger('sucursal_id'); 
        
        // 2. Apuntamos la llave foránea a la columna CORRECTA: 'id_sucursal'
        $table->foreign('sucursal_id')->references('id_sucursal')->on('sucursales');
        // ---- FIN DE LA CORRECCIÓN FINAL ----
        
        $table->foreignId('proveedor_id')->constrained('proveedores');
        $table->foreignId('categoria_id')->constrained('categorias');
        $table->foreignId('usuario_registra_id')->constrained('users');

        $table->unsignedBigInteger('corte_caja_id')->nullable();
        $table->date('fecha_gasto');
        $table->text('descripcion')->nullable();
        $table->decimal('monto_subtotal', 10, 2);
        $table->decimal('monto_iva', 10, 2)->default(0);
        $table->decimal('monto_total', 10, 2);
        $table->string('moneda', 10)->default('MXN');
        $table->string('nombre_archivo_comprobante')->nullable();
        $table->string('estado', 50);
        $table->boolean('requiere_aprobacion');
        $table->text('comentarios_rechazo')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gastos');
    }
};
