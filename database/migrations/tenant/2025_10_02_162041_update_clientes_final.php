<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Añade todas las columnas de la primera migración
        Schema::table('clientes', function (Blueprint $table) {
            $table->date('fecha_nacimiento')->nullable();
            $table->string('genero', 20)->nullable();
            $table->string('estado_nacimiento', 100)->nullable();
            $table->string('nacionalidad', 100)->default('Mexicana')->nullable();
            $table->string('estado_civil', 50)->nullable();
            $table->integer('numero_hijos')->unsigned()->default(0)->nullable();
            $table->integer('dependientes_economicos')->unsigned()->default(0)->nullable();
            $table->date('fecha_comprobante_domicilio')->nullable();
            $table->string('destino_credito')->nullable();
        });

        // Añade las columnas de la segunda migración
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('telefono_fijo')->nullable()->after('telefono_celular');
            $table->integer('anios_domicilio')->unsigned()->nullable()->after('fecha_comprobante_domicilio');
            $table->string('tipo_vivienda')->nullable()->after('anios_domicilio');
        });

        // Crea la tabla de referencias
        Schema::create('cliente_referencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes', 'id_cliente')->onDelete('cascade');
            $table->string('nombre_referencia');
            $table->string('parentesco');
            $table->string('telefono');
            $table->timestamps();
        });

        // Finalmente, modifica la columna vencimiento_ine
        DB::statement('ALTER TABLE clientes ADD COLUMN vencimiento_ine_temp INT NULL;');
        DB::statement('UPDATE clientes SET vencimiento_ine_temp = EXTRACT(YEAR FROM vencimiento_ine);');
        DB::statement('ALTER TABLE clientes DROP COLUMN vencimiento_ine;');
        DB::statement('ALTER TABLE clientes RENAME COLUMN vencimiento_ine_temp TO vencimiento_ine;');
    }

    public function down(): void
    {
        // Lógica para revertir todo (puedes dejarlo así por ahora)
        Schema::dropIfExists('cliente_referencias');
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn([
                'fecha_nacimiento', 'genero', 'estado_nacimiento', 'nacionalidad', 
                'estado_civil', 'numero_hijos', 'dependientes_economicos', 
                'fecha_comprobante_domicilio', 'destino_credito',
                'telefono_fijo', 'anios_domicilio', 'tipo_vivienda'
            ]);
        });
        DB::statement('ALTER TABLE clientes ADD COLUMN vencimiento_ine_temp DATE NULL;');
        DB::statement('UPDATE clientes SET vencimiento_ine_temp = make_date(vencimiento_ine, 12, 31);');
        DB::statement('ALTER TABLE clientes DROP COLUMN vencimiento_ine;');
        DB::statement('ALTER TABLE clientes RENAME COLUMN vencimiento_ine_temp TO vencimiento_ine;');
    }
};