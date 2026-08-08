-- FUNCTION: inventario.sp_reporte_conciliacion_carga_general_solicitud(bigint)

-- DROP FUNCTION IF EXISTS inventario.sp_reporte_conciliacion_carga_general_solicitud(bigint);

CREATE OR REPLACE FUNCTION inventario.sp_reporte_conciliacion_carga_general_solicitud(
	xsolicitud_id bigint)
    RETURNS TABLE(tipo_solicitud text, nro_solicitud bigint, codigo_solicitud text, estado text, usuario_solicitud text, origen text, destino text, codigo_boleta_logistica text, estado_logistica text, usuario_logistica text, cantidad_asignado_logistica numeric, contrato text, transportadora text, placa_vehiculo text, marca_vehiculo text, tipo_vehiculo text, color_vehiculo text, conductor text, conductor_identificacion text, codigo_unico_producto bigint, producto text, fecha_solicitud timestamp without time zone, cantidad_solicitada numeric, nombre_origen text, usuario_origen text, nro_boleta_salida integer, fecha_salida timestamp without time zone, lote_salida text, cantidad_salida numeric, cantidad_salida_acumulado numeric, saldo numeric, producto_comercial text, nombre_destino text, usuario_destino text, nro_boleta_ingreso integer, fecha_ingreso timestamp without time zone, cantidad_ingreso numeric, saldo_faltante_ingreso numeric) 
    LANGUAGE 'plpgsql'
    COST 100
    VOLATILE PARALLEL UNSAFE
    ROWS 1000

AS $BODY$
BEGIN
 -- SELECT * FROM inventario.sp_reporte_conciliacion_carga_general_solicitud(2298);
 
	RETURN QUERY (
			select t9.param_nombre as tipo_solicitud, t1.nro as nro_solicitud
			, t1.solicitud_id::text as codigo_solicitud, t10.param_nombre as estado
			, t8.name::text as usuario_solicitud
			, t6.nombre as origen, t7.nombre as destino
		
			, t4.codigo_boleta as codigo_logistica
			, t4.estado as estado_logistica
			, ul.name::text as usuario_logistica
			, t4.cantidad::numeric as cantidad_asignado_logistica
			, cot.nro_proceso_contrato::text as contrato

			, UPPER(d.nombre::text) as transportadora
		
			, UPPER(v.placa::text) as placa_vehiculo
			, v.marca::text AS marca_vehiculo
			, v.type::text AS tipo_vehiculo
            , v.color::text AS color_vehiculo
		
			, UPPER(c.nombre_completo::text) as conductor
			, UPPER(c.numero_identificacion::text) as conductor_identificacion
		
			, t5.id as codigo_unico_producto
			, t5.nombre_producto as producto
			, t1.created_at as fecha_solicitud
			, round(t2.cantidad, 2) as cantidad_solicitada
			, p1.nombre as nombre_origen
			, us.name::text as usuario_origen
			, salida.mv_nro_correlativo as nro_boleta_salida
			, salida.fecha as fecha_salida
			, salida.lote as lote_salida
			, round(salida.cantidad, 2) as cantidad_salida
			, round(salida.saldo, 2) as cantidad_salida_acumulado
			, round((t2.cantidad - salida.saldo), 2) as saldo
			, pc.nombre as producto_homologado_comercializacion
			, p3.nombre as nombre_destino
			, ui.name::text as usuario_destino
			, ingreso.mv_nro_correlativo as nro_boleta_ingreso
			, ingreso.fecha as fecha_ingreso
			, round(ingreso.cantidad,2) as cantidad_ingreso
			, round((salida.cantidad-ingreso.cantidad),2) as saldo_faltante_ingreso
			from inventario.solicitud_inventarios t1
			inner join inventario.solicitud_detalle_inventarios t2 on t1.id = t2.solicitud_id  --and t2.estado= 'A'
			left join logistica.solicitudes_movimiento_logistica t3 on t3.id_orden = t1.id
			left join logistica.solicitud_movimiento_detalle_logistica t4 on t3.id = t4.logistica_id and t4.solicitud_id = t1.id
			inner join insumos.articulos t5 on t5.id = t2.articulo_id
			left join public.articulos pc on pc.id = t2.producto_id
			left join public.sucursals t6 on t6.id = t1.origen_id
			left join public.sucursals t7 on t7.id = t1.destino_id
			left join public.users t8 on t8.id = t1.usr_registrado
			left join acopio.parametricas as t9 ON t1.tipo_solicitud_id = t9.param_valor AND t9.param_tabla = 'TABLA_TIPO_SOLICITUD'
			left join acopio.parametricas as t10 ON t1.estado_id = t10.param_valor AND t10.param_tabla = 'TABLA_TIPO_ESTADO'
			left join acopio.parametricas as t11 ON t5.tipo_material_id = t11.param_valor AND t11.param_tabla = 'TABLA_TIPO_PRODUCTO_INSUMOS'
			left join public.distribuidoras d on d.id = t4.distribuidora_id
			left join public.conductors c on c.id = t4.conductor_id
			left join public.transportes v on v.id = t4.vehiculo_id
			left join public.users ul on ul.id = t4.usr_registrado
			left join inventario.contratos cot on cot.id = t3.contrato_id
			left join (
				--3294
				select m.mv_id, m.mv_nro_correlativo, sld.codigo_boleta as codigo_logitica, m.created_at as fecha, m.mv_solicitud_id as solicitud_id, m.mv_destino_id as origen_id, l.nombre as lote, md.mvd_articulo_id as articulo_id, sld.id as logistica_id, m.mv_usr_registrado as usr_registrado, SUM(md.mvd_cantidad) as cantidad
				, (select SUM(mvd.mvd_cantidad) as saldo 
					from inventario.movimiento_inventarios as mv 
					inner join 	inventario.movimiento_detalle_inventarios mvd on mv.mv_id = mvd.mvd_mv_id 
					where mv.mv_tipo = 'SALIDA' and mv.mv_solicitud_id = m.mv_solicitud_id and m.mv_id >= mv.mv_id  and mvd.mvd_articulo_id = md.mvd_articulo_id 
					and mv.mv_estado = 'A' and mvd.mvd_estado = 'A'
				  ) as saldo
				from inventario.movimiento_inventarios m 
				inner join inventario.movimiento_detalle_inventarios md ON m.mv_id = md.mvd_mv_id
				left join logistica.solicitudes_movimiento_logistica sl on sl.id_orden = m.mv_solicitud_id
				left join logistica.solicitud_movimiento_detalle_logistica sld on sl.id = sld.logistica_id and sld.solicitud_id = m.mv_solicitud_id
				left join inventario.lotes as l on l.id = md.mvd_lote_id
				where m.mv_estado_id <> 11 and m.mv_estado='A' --and m.deleted_at isnull
				and md.mvd_estado='A' --AND md.deleted_at isnull
				and m.mv_tipo = 'SALIDA' --and EXTRACT(YEAR FROM m.created_at::date) = 2024 
				and sld.id = (m.mv_datos->>'logistica_id')::bigint
				and m.mv_tipo_solicitud_id = 7 and m.mv_solicitud_id = xsolicitud_id
				group by m.mv_id, m.mv_nro_correlativo, sld.codigo_boleta, m.created_at, m.mv_solicitud_id, m.mv_destino_id, l.nombre, md.mvd_articulo_id, sld.id, m.mv_usr_registrado
				order by m.mv_solicitud_id, m.mv_nro_correlativo
			) as salida on t1.id = salida.solicitud_id and t2.articulo_id = salida.articulo_id and salida.logistica_id = t4.id
			left join (
				--2984
				select m.mv_id, m.mv_nro_correlativo, m.created_at as fecha, m.mv_solicitud_id as solicitud_id, m.mv_destino_id as destino_id, md.mvd_articulo_id as articulo_id, sld.id as logistica_id, m.mv_usr_registrado as usr_registrado, SUM(md.mvd_cantidad) as cantidad
				from inventario.movimiento_inventarios m 
				inner join inventario.movimiento_detalle_inventarios md ON m.mv_id = md.mvd_mv_id
				left join logistica.solicitudes_movimiento_logistica sl on sl.id_orden = m.mv_solicitud_id
				left join logistica.solicitud_movimiento_detalle_logistica sld on sl.id = sld.logistica_id and sld.solicitud_id = m.mv_solicitud_id
				--left join inventario.lotes as l on l.id = md.mvd_lote_id
				where m.mv_estado_id <> 11 and m.mv_estado='A' --and m.deleted_at isnull
				and md.mvd_estado='A' --AND md.deleted_at isnull
				and m.mv_tipo = 'INGRESO' --and EXTRACT(YEAR FROM m.created_at::date) = 2024 
				and sld.id = (m.mv_datos->>'logistica_id')::bigint
				and m.mv_tipo_solicitud_id = 7 and m.mv_solicitud_id = xsolicitud_id
				group by m.mv_id, m.mv_nro_correlativo, m.created_at, m.mv_solicitud_id, m.mv_destino_id, md.mvd_articulo_id, sld.id, m.mv_usr_registrado
			) as ingreso on t1.id = ingreso.solicitud_id and t2.articulo_id = ingreso.articulo_id and ingreso.logistica_id = t4.id
			left join public.sucursals p1 on salida.origen_id = p1.id
			left join public.sucursals p3 on ingreso.destino_id = p3.id
			left join public.users us on us.id = salida.usr_registrado
			left join public.users ui on ui.id = ingreso.usr_registrado
			where t1.estado_registro = 'A' 
			and t1.tipo_solicitud_id = 7
			AND t1.id = xsolicitud_id
			order by 1, t1.nro, t5.id--, salida.mv_nro_correlativo
		);

END;
$BODY$;

ALTER FUNCTION inventario.sp_reporte_conciliacion_carga_general_solicitud(bigint)
    OWNER TO postgres;
