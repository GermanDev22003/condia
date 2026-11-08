<?php

namespace Database\Seeders;

use App\Models\Condominio;
use App\Models\Residente;
use App\Models\Unidad;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class DatosPruebaSeeder extends Seeder
{
    public function run(): void
    {
        $condominio = Condominio::create([
            'nombre' => 'Residencial ConDIA',
            'tipo' => 'residencial',
            'direccion' => 'Colonia Escalón, San Salvador',
            'telefono' => '2222-0000',
        ]);

        $unidades = [];
        foreach (['A-101', 'A-102', 'A-201', 'A-202', 'B-101', 'B-102'] as $numero) {
            $unidades[$numero] = Unidad::create([
                'id_condominio' => $condominio->id_condominio,
                'numero' => $numero,
                'torre_bloque' => 'Torre '.substr($numero, 0, 1),
                'nivel' => 'Nivel '.substr($numero, 2, 1),
            ]);
        }

        // Administrador: admin@condia.test / Admin12345
        Usuario::create([
            'correo' => 'admin@condia.test',
            'password' => 'Admin12345',
            'rol' => 'administrador',
            'activo' => true,
        ]);

        // Residentes con acceso: contraseña Residente123
        $residentes = [
            ['A-101', 'María Fernanda', 'López Ramos', '7000-1111', 'maria@condia.test'],
            ['A-102', 'Carlos Alberto', 'Mejía Pineda', '7000-2222', 'carlos@condia.test'],
            ['A-201', 'Ana Lucía', 'Hernández Cruz', '7000-3333', 'ana@condia.test'],
            ['B-101', 'Roberto', 'Martínez Soto', '7000-4444', null], // sin usuario de acceso
        ];

        foreach ($residentes as [$unidad, $nombres, $apellidos, $telefono, $correo]) {
            $idUsuario = $correo
                ? Usuario::create([
                    'correo' => $correo,
                    'password' => 'Residente123',
                    'rol' => 'residente',
                    'activo' => true,
                ])->id_usuario
                : null;

            Residente::create([
                'id_usuario' => $idUsuario,
                'id_unidad' => $unidades[$unidad]->id_unidad,
                'nombres' => $nombres,
                'apellidos' => $apellidos,
                'telefono' => $telefono,
                'fecha_ingreso' => '2026-01-15',
                'estado' => 'activo',
            ]);
        }
    }
}
