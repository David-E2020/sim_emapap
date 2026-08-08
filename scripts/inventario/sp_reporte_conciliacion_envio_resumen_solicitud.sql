-- FUNCTION: inventario.sp_reporte_conciliacion_envio_resumen_solicitud(bigint)

-- DROP FUNCTION IF EXISTS inventario.sp_reporte_conciliacion_envio_resumen_solicitud(bigint);

CREATE OR REPLACE FUNCTION inventario.sp_reporte_conciliacion_envio_resumen_solicitud(
	xsolicitud_id bigint)
    RETURNS TABLE(tipo_solicitud_id integer, tipo_solicitud text, nro_solicitud bigint, codigo_solicitud text, estado text, usuario_solicitud text, origen text, destino text, codigo_unico_producto bigint, producto text, fecha_solicitud timestamp without time zone, cantidad_solicitada numeric, nombre_origen text, cantidad_salida numeric, cantidad_salida_acumulado numeric, saldo numeric, producto_comercial text, nombre_destino text, cantidad_ingreso numeric, saldo_faltante_ingreso numeric) 
    LANGUAGE 'plpgsql'
    COST 100
    VOLATILE PARALLEL UNSAFE
    ROWS 1000

AS $BODY$
BEGIN
 -- SELECT * FROM inventario.sp_reporte_conciliacion_envio_resumen_solicitud();
    
	RETURN QUERY (
			select t1.tipo_solicitud_id, t9.param_nombre as tipo_solicitud, t1.nro as nro_solicitud
			, t1.solicitud_id::text as codigo_solicitud, t10.param_nombre as estado
			, t8.name::text as usuario_solicitud
			, t6.nombre as origen, t7.nombre as destino
			, t5.id as codigo_unico_producto
			, t5.nombre_producto as producto
			, t1.created_at as fecha_solicitud
			, round(t2.cantidad, 2) as cantidad_solicitada
			, p1.nombre as nombre_origen
			, round(salida.cantidad, 2) as cantidad_salida
			, round(salida.saldo, 2) as cantidad_salida_acumulado
			, round((t2.cantidad - salida.saldo), 2) as saldo
			, pc.nombre as producto_homologado_comercializacion
			, p3.nombre as nombre_destino
			, round(ingreso.cantidad, 2) as cantidad_ingreso
			, round((salida.cantidad-ingreso.cantidad), 2) as saldo_faltante_ingreso
			from inventario.solicitud_inventarios t1
			inner join inventario.solicitud_detalle_inventarios t2 on t1.id = t2.solicitud_id  --and t2.estado= 'A'
			inner join insumos.articulos t5 on t5.id = t2.articulo_id
			left join public.articulos pc on pc.id = t2.producto_id
			left join public.sucursals t6 on t6.id = t1.origen_id
			left join public.sucursals t7 on t7.id = t1.destino_id
			left join public.users t8 on t8.id = t1.usr_registrado
			left join acopio.parametricas as t9 ON t1.tipo_solicitud_id = t9.param_valor AND t9.param_tabla = 'TABLA_TIPO_SOLICITUD'
			left join acopio.parametricas as t10 ON t1.estado_id = t10.param_valor AND t10.param_tabla = 'TABLA_TIPO_ESTADO'
			left join acopio.parametricas as t11 ON t5.tipo_material_id = t11.param_valor AND t11.param_tabla = 'TABLA_TIPO_PRODUCTO_INSUMOS'
			left join (
				--3294
				select m.mv_solicitud_id as solicitud_id, m.mv_destino_id as origen_id, md.mvd_articulo_id as articulo_id, SUM(md.mvd_cantidad) as cantidad
				, (select SUM(mvd.mvd_cantidad) as saldo 
					from inventario.movimiento_inventarios as mv 
					inner join 	inventario.movimiento_detalle_inventarios mvd on mv.mv_id = mvd.mvd_mv_id 
					where mv.mv_tipo = 'SALIDA' and mv.mv_solicitud_id = m.mv_solicitud_id and mvd.mvd_articulo_id = md.mvd_articulo_id 
				   and mv.mv_estado = 'A' and mvd.mvd_estado = 'A'
				) as saldo
				from inventario.movimiento_inventarios m 
				inner join inventario.movimiento_detalle_inventarios md ON m.mv_id = md.mvd_mv_id
				where m.mv_estado_id <> 11 and m.mv_estado='A' --and m.deleted_at isnull
				and md.mvd_estado='A' --AND md.deleted_at isnull
				and m.mv_tipo = 'SALIDA' --and EXTRACT(YEAR FROM m.created_at::date) = 2024 
				and m.mv_tipo_solicitud_id = 8 and m.mv_solicitud_id = xsolicitud_id
				group by m.mv_solicitud_id, m.mv_destino_id, md.mvd_articulo_id
				order by m.mv_solicitud_id
			) as salida on t1.id = salida.solicitud_id and t2.articulo_id = salida.articulo_id
			left join (
				select m.mv_solicitud_id as solicitud_id, m.mv_destino_id as destino_id, sd.articulo_id as articulo_id, SUM(md.mvd_cantidad) as cantidad
				from public.movimientos m 
				inner join public.movimiento_detalles md ON m.mv_id = md.mvd_mv_id
				left join logistica.solicitud_movimiento_detalle_logistica sld on  sld.solicitud_id = m.mv_solicitud_id and sld.movimiento_ingreso_id = m.mv_id 
				inner join inventario.solicitud_detalle_inventarios sd on sd.solicitud_id = m.mv_solicitud_id and sd.producto_id = md.mvd_articulo_id
				where m.mv_estado_id <> 11 and m.deleted_at isnull
				and md.mvd_estado='A' --AND md.deleted_at isnull
				and m.mv_tipo = 'INGRESO' --and EXTRACT(YEAR FROM m.created_at::date) = 2024 
				and m.mv_tipo_movimiento_id = 98 and m.mv_solicitud_id = xsolicitud_id
				group by m.mv_solicitud_id, m.mv_destino_id, sd.articulo_id
			) as ingreso on t1.id = ingreso.solicitud_id and t2.articulo_id = ingreso.articulo_id
			left join public.sucursals p1 on salida.origen_id = p1.id
			left join public.sucursals p3 on ingreso.destino_id = p3.id
			where t1.estado_registro = 'A' 
			and t1.tipo_solicitud_id = 8
			and t1.id = xsolicitud_id
			order by 1, t1.nro, t5.id--, salida.mv_nro_correlativo
		);

END;
$BODY$;

ALTER FUNCTION inventario.sp_reporte_conciliacion_envio_resumen_solicitud(bigint)
    OWNER TO postgres;
