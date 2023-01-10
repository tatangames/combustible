<?php

namespace Database\Seeders;

use App\Models\TipoCombustible;
use Illuminate\Database\Seeder;

class TipoCombustibleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TipoCombustible::create([
            'nombre' => 'DIESEL',
        ]);

        TipoCombustible::create([
            'nombre' => 'REGULAR',
        ]);

        TipoCombustible::create([
            'nombre' => 'ESPECIAL',
        ]);
    }
}
