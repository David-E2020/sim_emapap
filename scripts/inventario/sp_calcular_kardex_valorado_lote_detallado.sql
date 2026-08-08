CREATE OR REPLACE FUNCTION inventario.sp_calcular_kardex_valorado_lote_detallado(
	selling_point_id integer,
	product_id integer,
	xlote_id integer,
	fecha_ini text,
	fecha_fin text)
    RETURNS TABLE(o_correlativo text, o_fecha text, o_tipo text, o_codigo text, o_tipo_detalle text, o_nombre_origen text, o_nombre_destino text, o_distribuidora text, o_conductor text, o_placa text, o_entrada text, o_salida text, o_saldo text, o_lote text, o_programa text, o_campania text, o_mv_id text) 
    LANGUAGE 'plpgsql'
    COST 100
    VOLATILE PARALLEL UNSAFE
    ROWS 1000

AS $BODY$
DECLARE
	kardex record;
	saldo numeric;
	saldo_2 text;
	saldo_3 text;
	cantidad text;
	total text;
BEGIN
CREATE TABLE IF NOT EXISTS inventario.temporal_kardex_insumos
(
	o_correlativo text,
    o_fecha text,
    o_tipo text,
	o_codigo text,
    o_tipo_det text,
	o_nombre_origen text,
	o_nombre_destino text,
	
	o_distribuidora text,
	o_conductor text,
	o_placa text,
	
    o_entrada text,
    o_salida text,
	o_saldo text,
	o_lote text,
	o_programa text,
	o_campania text,
	o_mv_id text
);
	saldo := 0;
	RAISE NOTICE 'Notice message  %', now();
	-- SELECT * FROM inventario.sp_calcular_kardex_valorado_lote_detallado(76, 227, 1062, '01-02-2024', '20-03-2024');
	
	for kardex in select mv_tipo
			, mv_tipo_movimiento_id
			, mv_nro_correlativo
			, mvd_cantidad
			, m.created_at::date
			, param_nombre
			
			, s1.nombre as nombre_origen
			, s2.nombre as nombre_destino
			
			, d.nombre as distribuidora
			, co.nombre_completo as conductor
			, t.placa as placa
			
			, /*s.aco_codigo*/ '-' as solicitud_id 
			, /*suc.nombre*/ '-' as nombre
			, m.mv_id
			, l.nombre as lote_nombre
			, p.prog_nombre as programa
			, c.camp_nombre as campania
			, s.solicitud_id as solicitud
			, m.mv_id
			from inventario.movimiento_inventarios as m
			inner join inventario.movimiento_detalle_inventarios on mvd_mv_id = m.mv_id and mvd_estado = 'A'
			left join inventario.solicitud_inventarios as s on s.id = m.mv_solicitud_id
			left join inventario.lotes as l on l.id = mvd_lote_id
			left join siemc.programa as p on p.programa_id = l.programa_id
			left join siemc.campania as c on c.campania_id = l.campania_id
			left join acopio.parametricas on param_valor = m.mv_tipo_movimiento_id
			
			left join sucursals s1 on s1.id = m.mv_origen_id
			left join sucursals s2 on s2.id = m.mv_destino_id
			
			left join logistica.solicitud_movimiento_detalle_logistica lg on lg.id = (m.mv_datos->>'logistica_id')::bigint
			left join public.distribuidoras d on d.id = lg.distribuidora_id
			left join public.conductors co on co.id = lg.conductor_id
			left join public.transportes t on t.id = lg.vehiculo_id
			
			where mvd_articulo_id = product_id and m.mv_destino_id = selling_point_id
			and m.mv_estado_id not in (11)
			and param_tabla='TABLA_TIPO_MOVIMIENTO'
			and param_estado='A'
			and mvd_lote_id = xlote_id
			and m.created_at::date BETWEEN fecha_ini::date AND fecha_fin::date
			order by m.created_at
			loop
			--RAISE NOTICE 'ID: %, Nombre: dsdsadsadsdsds%',kardex.mv_id, kardex.solicitud;
				if(kardex.mv_tipo = 'INGRESO')then
					cantidad := (select split_part((kardex.mvd_cantidad)::text,$$.$$,1) ||$$.$$||substring(split_part((kardex.mvd_cantidad)::text,$$.$$,2),1,2));
					saldo := saldo + (kardex.mvd_cantidad)::numeric;
					--------------------------------------------------------
					saldo_2 := (select split_part((saldo)::text,$$.$$,1) ||$$.$$||substring(split_part((saldo)::text,$$.$$,2),1,2));
					saldo_3 := (saldo);
						INSERT INTO inventario.temporal_kardex_insumos(
						o_correlativo, o_fecha, o_tipo, o_codigo, o_tipo_det, o_nombre_origen, o_nombre_destino, o_distribuidora, o_conductor, o_placa, o_entrada, o_salida,o_saldo,o_lote,o_programa,o_campania,o_mv_id)
						VALUES (kardex.mv_nro_correlativo, kardex.created_at, kardex.mv_tipo||' #'||'('||kardex.mv_nro_correlativo||') '|| COALESCE('-' || kardex.solicitud, ''), '-',kardex.param_nombre, kardex.nombre_origen, kardex.nombre_destino, kardex.distribuidora, kardex.conductor, kardex.placa, cantidad, '-',saldo_2,kardex.lote_nombre,kardex.programa,kardex.campania,kardex.mv_id);

				else
					cantidad := (select split_part((kardex.mvd_cantidad)::text,$$.$$,1) ||$$.$$||substring(split_part((kardex.mvd_cantidad)::text,$$.$$,2),1,2));
					saldo := saldo - (kardex.mvd_cantidad)::numeric;
					--------------------------------------------------------
					saldo_2 := (select split_part((saldo)::text,$$.$$,1) ||$$.$$||substring(split_part((saldo)::text,$$.$$,2),1,2));
					saldo_3 := (saldo);
						INSERT INTO inventario.temporal_kardex_insumos(
						o_correlativo, o_fecha, o_tipo, o_codigo, o_tipo_det, o_nombre_origen, o_nombre_destino, o_distribuidora, o_conductor, o_placa, o_entrada, o_salida,o_saldo,o_lote,o_programa,o_campania,o_mv_id)
						VALUES (kardex.mv_nro_correlativo, kardex.created_at, kardex.mv_tipo||' #'||'('||kardex.mv_nro_correlativo||') '|| COALESCE('-' || kardex.solicitud, ''), '-',kardex.param_nombre, kardex.nombre_origen, kardex.nombre_destino, kardex.distribuidora, kardex.conductor, kardex.placa, '-', cantidad, saldo_2,kardex.lote_nombre,kardex.programa,kardex.campania,kardex.mv_id);	
				end if;
			end loop;
			return query select * from inventario.temporal_kardex_insumos;
			drop table inventario.temporal_kardex_insumos;
END;
$BODY$;

ALTER FUNCTION inventario.sp_calcular_kardex_valorado_lote_detallado(integer, integer, integer, text, text)
    OWNER TO postgres;
