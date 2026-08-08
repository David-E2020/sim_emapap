create table logistica.solicitud_movimiento_detalle_logistica(
    id serial PRIMARY KEY,
    logistica_id integer not null,
    solicitud_id bigint not null,
	cantidad numeric(18, 2) not null,
    nro integer not null,
    codigo_boleta text,
    distribuidora_id integer not null,
    vehiculo_id integer not null,
    conductor_id integer not null,
    data_adicional jsonb,
    usr_registrado integer not null,
    usr_modificado integer,
    estado text default 'A',
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


----------------------------
ALTER TABLE logistica.solicitudes_movimiento_logistica
ADD transporte_multiple BOOLEAN DEFAULT false;

---------------------------------------
alter table logistica.solicitud_movimiento_detalle_logistica add column fecha_despacho text;
alter table logistica.solicitud_movimiento_detalle_logistica add column hora_despacho text;
alter table logistica.solicitud_movimiento_detalle_logistica add column fecha_est_llegada text;
alter table logistica.solicitud_movimiento_detalle_logistica add column hora_est_llegada text;
---------------------------------------------------------------
ALTER TABLE inventario.contratos RENAME COLUMN estado_baja TO estado_uso;
ALTER TABLE inventario.contratos add column archivo text;