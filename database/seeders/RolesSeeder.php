<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // encargado administrador
        $role1 = Role::create(['name' => 'Encargado-Administrador']);

        // encargado de crear factura
        $role2 = Role::create(['name' => 'Encargado-Factura']);

        // revision de reportes
        $role3 = Role::create(['name' => 'Encargado-Reporte']);

        // --- CREAR PERMISOS ---

        // visualizar roles y permisos
        Permission::create(['name' => 'vista.roles.index', 'description' => 'Crear roles y permisos'])->syncRoles($role1);

        Permission::create(['name' => 'vista.factura.index', 'description' => 'Visualiza facturas'])->syncRoles($role2);

        Permission::create(['name' => 'vista.reporte.index', 'description' => 'Visualiza crear reportes'])->syncRoles($role3);
    }
}
