export interface Condominio {
  id_condominio: number;
  nombre: string;
}

export interface Unidad {
  id_unidad: number;
  numero: string;
  torre_bloque: string | null;
  nivel: string | null;
  condominio?: Condominio;
}

export interface Residente {
  id_residente: number;
  nombres: string;
  apellidos: string;
  telefono: string | null;
  unidad: Unidad;
}

export interface Usuario {
  id_usuario: number;
  correo: string;
  rol: 'administrador' | 'residente';
  residente: Residente | null;
}

export interface RespuestaLogin {
  token: string;
  tipo: string;
  usuario: Usuario;
}

export interface Pago {
  id_pago: number;
  id_cuota: number;
  monto_pagado: string;
  fecha_pago: string;
  metodo_pago: 'efectivo' | 'transferencia' | 'cheque';
  referencia: string | null;
}

export type EstadoCuota = 'pendiente' | 'pagada' | 'vencida';

export interface Cuota {
  id_cuota: number;
  concepto: string;
  monto: string;
  periodo: string;
  fecha_vencimiento: string;
  estado: EstadoCuota;
  total_pagado: string | null;
  pagos: Pago[];
}

export interface MisCuotas {
  pendiente_total: string;
  cuotas: Cuota[];
}
