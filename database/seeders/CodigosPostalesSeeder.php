<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CodigosPostalesSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/CPdescarga.txt'); // Verificamos la ruta correcta
        if (!file_exists($path)) {
            $this->command->error("El archivo CPdescarga.txt no se encontró en database/data/");
            return;
        }

        // Limpiamos la tabla antes de importar para evitar duplicados si se corre de nuevo
        DB::table('codigos_postales')->truncate(); 
        
        $file = fopen($path, "r");
        $data = [];
        $batchSize = 1000;
        
        fgets($file); // Omitir la primera línea de encabezado
        fgets($file); // Omitir la segunda línea de encabezado

        while (($line = fgets($file)) !== false) {
            
            // --- ¡LA CORRECCIÓN ESTÁ AQUÍ! ---
            // Convertimos la línea del formato Latin-1 (ISO-8859-1) a UTF-8
            $line_utf8 = mb_convert_encoding($line, 'UTF-8', 'ISO-8859-1');
            // ------------------------------------

            $parts = explode('|', trim($line_utf8));

            // Nos aseguramos de que la línea tenga las partes que esperamos
            if (count($parts) >= 5) {
                $data[] = [
                    'codigo_postal' => $parts[0],
                    'colonia'       => $parts[1],
                    'municipio'     => $parts[3],
                    'estado'        => $parts[4],
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }

            if (count($data) >= $batchSize) {
                DB::table('codigos_postales')->insert($data);
                $data = [];
                $this->command->info('Insertado un lote de ' . $batchSize . ' registros...');
            }
        }

        if (!empty($data)) {
            DB::table('codigos_postales')->insert($data); // Insertar el último lote
            $this->command->info('Insertado el último lote de registros...');
        }

        fclose($file);
        $this->command->info('¡Importación de códigos postales completada!');
    }
}