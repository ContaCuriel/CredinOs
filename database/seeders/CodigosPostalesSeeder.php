<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CodigosPostalesSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/CPdescarga.txt'); // <-- ¡DEBES PONER EL ARCHIVO .txt AQUÍ!
        if (!file_exists($path)) {
            $this->command->error("El archivo CPdescarga.txt no se encontró en storage/app/");
            return;
        }

        DB::table('codigos_postales')->truncate(); // Limpiar la tabla antes de importar

        $file = fopen($path, "r");
        $data = [];
        $batchSize = 1000; // Importar en lotes de 1000

        fgets($file); // Omitir la primera línea de encabezado
        fgets($file); // Omitir la segunda línea de encabezado

        while (($line = fgets($file)) !== false) {
            $parts = explode('|', trim($line));
            $data[] = [
                'codigo_postal' => $parts[0],
                'colonia'       => $parts[1],
                'municipio'     => $parts[3],
                'estado'        => $parts[4],
                'created_at'    => now(),
                'updated_at'    => now(),
            ];

            if (count($data) >= $batchSize) {
                DB::table('codigos_postales')->insert($data);
                $data = [];
                $this->command->info('Insertado un lote de ' . $batchSize . ' registros...');
            }
        }

        if (!empty($data)) {
            DB::table('codigos_postales')->insert($data); // Insertar el último lote
        }

        fclose($file);
        $this->command->info('¡Importación de códigos postales completada!');
    }
}