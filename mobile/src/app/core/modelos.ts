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
