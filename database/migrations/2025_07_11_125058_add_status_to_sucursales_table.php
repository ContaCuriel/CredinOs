    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        public function up(): void
        {
            Schema::table('sucursales', function (Blueprint $table) {
                // Añadimos la columna 'status' después del nombre
                // Por defecto, todas las nuevas sucursales serán 'Activa'
                $table->string('status', 50)->default('Activa')->after('nombre_sucursal');
            });
        }

        public function down(): void
        {
            Schema::table('sucursales', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    };
    