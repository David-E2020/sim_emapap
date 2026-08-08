-- FUNCTION: inventario.sp_reporte_conciliacion_despacho_general_solicitud(bigint)

-- DROP FUNCTION IF EXISTS inventario.sp_reporte_conciliacion_despacho_general_solicitud(bigint);

CREATE OR REPLACE FUNCTION inventario.sp_reporte_conciliacion_despacho_general_solicitud(
	xsolicitud_id bigint)
    RETURNS TABLE(tipo_solicitud_id integer, tipo_solicitud text, nro_solicitud bigint, codigo_solicitud text, estado text, tipo_venta text, tipo_orden text, usuario_solicitud text, origen text, destino text, nombre_productor text, numero_identificacion_productor text, asociacion text, conductor text, conductor_identificacion text, placa_vehiculo text, codigo_unico_producto bigint, producto text, fecha_solicitud timestamp without time zone, cantidad_solicitada numeric, nombre_origen text, usuario_origen text, nro_boleta_salida integer, lote_salida text, fecha_salida timestamp without time zone, cantidad_salida numeric, cantidad_salida_acumulado numeric, saldo numeric, producto_comercial text, nombre_destino text, usuario_destino text, nro_boleta_ingreso integer, fecha_ingreso timestamp without time zone, cantidad_ingreso numeric) 
    LANGUAGE 'plpgsql'
    COST 100
    VOLATILE PARALLEL UNSAFE
    ROWS 1000

AS $BODY$
BEGIN
 -- SELECT * FROM inventario.sp_reporte_conciliacion_despacho_general_solicitud(655);
	RETURN QUERY (
	select t1.tipo_solicitud_id, t9.param_nombre as tipo_solicitud, t1.nro as nro_solicitud, t1.solicitud_id::text as codigo_solicitud, t10.param_nombre as estado
			, tv1.param_nombre as tipo_venta, tv2.param_nombre as tipo_orden
			, t8.name::text as usuario_solicitud, t6.nombre as origen, t7.nombre as destino

			, t1.data->>'cli_razon_social' as nombre_productor, t1.data->>'cliente_numero_identificacion' as numero_identificacion_productor, t1.data->>'asociacion' as asociacion
			--, salida.logistica_id
			, UPPER(cp.nombre_completo::text) AS conductor
			, UPPER(cp.numero_identificacion::text) AS conductor_identificacion
			, UPPER(vp.placa::text) AS placa_vehiculo
			, t5.id as codigo_unico_producto
			, t5.nombre_producto as producto
			, t1.created_at as fecha_solicitud
			, round(t2.cantidad, 2) as cantidad_solicitada
			, p2.nombre AS nombre_origen
			, us.name::text as usuario_origen
			, salida_despacho.mv_nro_correlativo AS nro_boleta_salida
			, salida_despacho.lote as lote_salida
			, salida_despacho.fecha AS fecha_salida
			, round(salida_despacho.cantidad, 2) AS cantidad_salida
			, round(salida_despacho.saldo,2) AS cantidad_salida_acumulado
			, round((t2.cantidad - salida_despacho.saldo), 2) AS saldo
			, t3.nombre as producto_homologado_comercializacion
			, p3.nombre as nombre_destino
			, ui.name::text as usuario_destino
			, ingreso_despacho.mv_nro_correlativo as nro_boleta_ingreso
			, ingreso_despacho.fecha AS fecha_ingreso
			, round(ingreso_despacho.cantidad, 2) AS cantidad_ingreso
			from inventario.solicitud_inventarios t1
			left join inventario.solicitud_detalle_inventarios t2 on t1.id = t2.solicitud_id  --and t2.estado= 'A'
			inner join insumos.articulos t5 on t5.id = t2.articulo_id
			left join public.articulos t3 on t3.id = t2.producto_id
			left join public.sucursals t6 on t6.id = t1.origen_id
			left join public.sucursals t7 on t7.id = t1.destino_id
			left join public.users t8 on t8.id = t1.usr_registrado
			left join acopio.parametricas as t9 ON t1.tipo_solicitud_id = t9.param_valor AND t9.param_tabla = 'TABLA_TIPO_SOLICITUD'
			left join acopio.parametricas as t10 ON t1.estado_id = t10.param_valor AND t10.param_tabla = 'TABLA_TIPO_ESTADO'
			left join acopio.parametricas as t11 ON t5.tipo_material_id = t11.param_valor AND t11.param_tabla = 'TABLA_TIPO_PRODUCTO_INSUMOS'
			left join public.parametricas as tv1 ON (t1.data->>'tipo_venta_id')::bigint = tv1.param_valor AND tv1.param_tabla = 'TABLA_TIPO_VENTA_DESPACHO'
			left join public.parametricas as tv2 ON (t1.data->>'tipo_orden_id')::bigint = tv2.param_valor AND tv2.param_tabla = 'TABLA_TIPO_ORDEN_DESPACHO'
			left join (
				--2641
				select m.mv_id, m.mv_nro_correlativo, m.created_at as fecha, m.mv_solicitud_id as solicitud_id, m.mv_destino_id as origen_id, md.mvd_articulo_id as articulo_id, l.nombre as lote, m.mv_datos->>'vehiculo_id' as vehiculo_id, m.mv_datos->>'conductor_id' as conductor_id, m.mv_usr_registrado as usr_registrado, SUM(md.mvd_cantidad) as cantidad
				, (select SUM(mvd.mvd_cantidad) as saldo 
					from inventario.movimiento_inventarios as mv 
					inner join 	inventario.movimiento_detalle_inventarios mvd on mv.mv_id = mvd.mvd_mv_id 
					where mv.mv_tipo = 'SALIDA' and mv.mv_solicitud_id = m.mv_solicitud_id and m.mv_id >= mv.mv_id  and mvd.mvd_articulo_id = md.mvd_articulo_id 
				    and mv.mv_tipo_solicitud_id = 3
				   	and mv.mv_estado = 'A' and mvd.mvd_estado = 'A'
				) as saldo
				from inventario.movimiento_inventarios m 
				inner join inventario.movimiento_detalle_inventarios md ON m.mv_id = md.mvd_mv_id
				left join inventario.lotes as l on l.id = md.mvd_lote_id
				where m.mv_estado_id <> 11 and m.mv_estado='A' --and m.deleted_at isnull
				and md.mvd_estado='A' --AND md.deleted_at isnull
				and m.mv_tipo = 'SALIDA' --and EXTRACT(YEAR FROM m.created_at::date) = 2024 
				and m.mv_tipo_solicitud_id = 3 and m.mv_solicitud_id = xsolicitud_id
				group by m.mv_id, m.mv_nro_correlativo, m.created_at, m.mv_solicitud_id, m.mv_destino_id, md.mvd_articulo_id, l.nombre, m.mv_datos->>'vehiculo_id', m.mv_datos->>'conductor_id', m.mv_usr_registrado
				order by m.mv_solicitud_id
			) as salida_despacho on t1.id = salida_despacho.solicitud_id and t2.articulo_id = salida_despacho.articulo_id
			left join (
				--2523
				select m.mv_id, m.mv_nro_correlativo, m.created_at as fecha, m.mv_solicitud_id as solicitud_id, m.mv_destino_id as destino_id, sd.articulo_id as articulo_id, m.mv_usr_registrado as usr_registrado, SUM(md.mvd_cantidad) as cantidad
				from public.movimientos m 
				inner join public.movimiento_detalles md ON m.mv_id = md.mvd_mv_id
				inner join inventario.solicitud_detalle_inventarios sd on sd.solicitud_id = m.mv_solicitud_id and sd.producto_id = md.mvd_articulo_id
				where m.mv_estado_id <> 11 and m.deleted_at isnull
				and md.mvd_estado='A' --AND md.deleted_at isnull
				and m.mv_tipo = 'INGRESO' --and EXTRACT(YEAR FROM m.created_at::date) = 2024 
				and m.mv_tipo_movimiento_id = 98 and m.mv_solicitud_id = xsolicitud_id
				group by m.mv_id, m.mv_nro_correlativo, m.created_at, m.mv_solicitud_id, m.mv_destino_id, sd.articulo_id, m.mv_usr_registrado
			) as ingreso_despacho on t1.id = ingreso_despacho.solicitud_id and t2.articulo_id = ingreso_despacho.articulo_id
			left join public.conductors cp on cp.id = case when salida_despacho.conductor_id ='' then 0 else salida_despacho.conductor_id::bigint end
			left join public.transportes vp on vp.id = case when salida_despacho.vehiculo_id ='' then 0 else salida_despacho.vehiculo_id ::bigint end
			left join public.sucursals p2 on salida_despacho.origen_id 	= p2.id
			left join public.sucursals p3 on ingreso_despacho.destino_id = p3.id
			left join public.users us on us.id = salida_despacho.usr_registrado
			left join public.users ui on ui.id = ingreso_despacho.usr_registrado
			where t1.estado_registro = 'A' and t1.tipo_solicitud_id in (3) 
			and t1.id = xsolicitud_id
			order by 1, t1.nro, t5.id, salida_despacho.mv_nro_correlativo
			
	);

END;
$BODY$;

ALTER FUNCTION inventario.sp_reporte_conciliacion_despacho_general_solicitud(bigint)
    OWNER TO postgres;
