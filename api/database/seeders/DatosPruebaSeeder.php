<?php

namespace Database\Seeders;

use App\Models\Condominio;
use App\Models\Cuota;
use App\Models\Pago;
use App\Models\Personal;
use App\Models\Queja;
use App\Models\SolicitudMantenimiento;
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

        $this->cuotasDePrueba();
        $this->reportesDePrueba($condominio);
    }

    /** Cuotas de ejemplo: una pagada, una pendiente, una vencida y una con pago parcial. */
    private function cuotasDePrueba(): void
    {
        $admin = Usuario::where('rol', 'administrador')->first();
        $res = Residente::orderBy('id_residente')->get();
        $maria = $res[0];
        $carlos = $res[1];

        $pagada = Cuota::create(['id_residente' => $maria->id_residente, 'concepto' => 'Mantenimiento', 'monto' => 45.00, 'periodo' => '2026-09', 'fecha_vencimiento' => '2026-09-10', 'estado' => 'pagada']);
        Pago::create(['id_cuota' => $pagada->id_cuota, 'id_usuario_registro' => $admin->id_usuario, 'monto_pagado' => 45.00, 'fecha_pago' => '2026-09-08', 'metodo_pago' => 'transferencia', 'referencia' => 'TRF-0001']);

        Cuota::create(['id_residente' => $maria->id_residente, 'concepto' => 'Mantenimiento', 'monto' => 45.00, 'periodo' => '2026-10', 'fecha_vencimiento' => '2026-10-10', 'estado' => 'pendiente']);
        Cuota::create(['id_residente' => $maria->id_residente, 'concepto' => 'Mantenimiento', 'monto' => 45.00, 'periodo' => '2026-12', 'fecha_vencimiento' => '2026-12-10', 'estado' => 'pendiente']);

        $parcial = Cuota::create(['id_residente' => $carlos->id_residente, 'concepto' => 'Mantenimiento', 'monto' => 45.00, 'periodo' => '2026-11', 'fecha_vencimiento' => '2026-11-30', 'estado' => 'pendiente']);
        Pago::create(['id_cuota' => $parcial->id_cuota, 'id_usuario_registro' => $admin->id_usuario, 'monto_pagado' => 20.00, 'fecha_pago' => '2026-11-03', 'metodo_pago' => 'efectivo', 'referencia' => null]);
    }

    /** Personal, una queja y dos solicitudes de ejemplo. */
    private function reportesDePrueba(Condominio $condominio): void
    {
        $personal = [];
        foreach ([
            ['Luis', 'Ortiz', 'mantenimiento', 'diurno'],
            ['Marta', 'Quintanilla', 'mantenimiento', 'mixto'],
            ['Pedro', 'Rivas', 'seguridad', 'nocturno'],
        ] as [$nombres, $apellidos, $cargo, $turno]) {
            $personal[] = Personal::create([
                'id_condominio' => $condominio->id_condominio,
                'nombres' => $nombres,
                'apellidos' => $apellidos,
                'cargo' => $cargo,
                'turno' => $turno,
                'telefono' => '7100-0000',
                'estado' => 'activo',
            ]);
        }

        $res = Residente::orderBy('id_residente')->get();

        Queja::create([
            'id_residente' => $res[0]->id_residente,
            'asunto' => 'Ruido en el pasillo',
            'categoria' => 'ruido',
            'descripcion' => 'Hay ruido fuerte en el pasillo del segundo nivel después de las 10 de la noche.',
            'estado' => 'pendiente',
        ]);

        SolicitudMantenimiento::create([
            'id_residente' => $res[1]->id_residente,
            'titulo' => 'Fuga en el lavamanos',
            'ubicacion' => 'Baño principal',
            'prioridad' => 'alta',
            'descripcion' => 'El lavamanos gotea constantemente por la parte de abajo.',
            'estado' => 'pendiente',
        ]);

        SolicitudMantenimiento::create([
            'id_residente' => $res[0]->id_residente,
            'id_personal' => $personal[0]->id_personal,
            'titulo' => 'Bombillo quemado',
            'ubicacion' => 'Pasillo del nivel 1',
            'prioridad' => 'baja',
            'descripcion' => 'El bombillo del pasillo frente al apartamento está quemado.',
            'estado' => 'en_proceso',
        ]);
    }
}
