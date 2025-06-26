<?php
// database/seeders/CategoriaSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categoria;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema; // <-- MUY IMPORTANTE: Asegúrate de que esta línea 'use' esté presente.

class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Desactivamos la revisión de llaves foráneas para poder limpiar la tabla.
        Schema::disableForeignKeyConstraints();

        // 2. Vaciamos la tabla. truncate() es rápido y resetea el ID autoincremental.
        Categoria::truncate();

        // 3. Definimos los datos que queremos sembrar.
        $categorias = [
            // Gastos que NO requieren aprobación por defecto
            ['nombre' => 'Nómina y Salarios', 'default_requiere_aprobacion' => false],
            ['nombre' => 'Renta y Alquileres', 'default_requiere_aprobacion' => false],
            ['nombre' => 'Pago de Impuestos', 'default_requiere_aprobacion' => false],
            ['nombre' => 'Servicios Públicos (Luz, Agua, Gas)', 'default_requiere_aprobacion' => false],
            ['nombre' => 'Telefonía e Internet', 'default_requiere_aprobacion' => false],
            
            // Gastos que SÍ requieren aprobación por defecto
            ['nombre' => 'Papelería y Útiles de Oficina', 'default_requiere_aprobacion' => true],
            ['nombre' => 'Mantenimiento y Reparaciones', 'default_requiere_aprobacion' => true],
            ['nombre' => 'Marketing y Publicidad', 'default_requiere_aprobacion' => true],
            ['nombre' => 'Viáticos y Gastos de Viaje', 'default_requiere_aprobacion' => true],
            ['nombre' => 'Transporte y Paquetería', 'default_requiere_aprobacion' => true],
            ['nombre' => 'Comisiones Bancarias', 'default_requiere_aprobacion' => true],
            ['nombre' => 'Insumos de Limpieza', 'default_requiere_aprobacion' => true],
            ['nombre' => 'Otros Gastos', 'default_requiere_aprobacion' => true],
        ];

        // 4. Insertamos todas las categorías en la base de datos.
        foreach ($categorias as $categoria) {
            Categoria::create($categoria);
        }

        // 5. Reactivamos la revisión de llaves foráneas una vez que hemos terminado.
        Schema::enableForeignKeyConstraints();
    }
}