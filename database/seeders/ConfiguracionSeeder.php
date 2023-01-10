<?php

namespace Database\Seeders;

use App\Models\Configuracion;
use Illuminate\Database\Seeder;

class ConfiguracionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Configuracion::create([
            'nombre1' => 'Blanca Mariela Argueta Mayorga',
            'cargo1' => 'Encargado de Combustible',
            'nombre2' => 'Lic. Darwin Francisco Sandoval Nolasco',
            'cargo2' => 'Gerente de Servicios y Desarrollo Territorial',
        ]);
    }
}
