-- FUNCTION: inventario.sp_reporte_conciliacion_despacho_resumen(integer, integer, integer, text, text)

-- DROP FUNCTION IF EXISTS inventario.sp_reporte_conciliacion_despacho_resumen(integer, integer, integer, text, text);

CREATE OR REPLACE FUNCTION inventario.sp_reporte_conciliacion_despacho_resumen(
	xtipo_producto_id integer,
	tipo_linea_id integer,
	gestion integer,
	fecha_ini text,
	fecha_fin text)
    RETURNS TABLE(tipo_solicitud_id integer, tipo_solicitud text, nro_solicitud bigint, codigo_solicitud text, estado text, tipo_venta text, tipo_orden text, usuario_solicitud text, origen text, destino text, nombre_productor text, numero_identificacion_productor text, asociacion text, codigo_unico_producto bigint, producto text, fecha_solicitud timestamp without time zone, cantidad_solicitada numeric, nombre_origen text, cantidad_salida numeric, cantidad_salida_acumulado numeric, saldo numeric, producto_comercial text, nombre_destino text, cantidad_ingreso numeric) 
    LANGUAGE 'plpgsql'
    COST 100
    VOLATILE PARALLEL UNSAFE
    ROWS 1000

AS $BODY$
BEGIN
 -- SELECT * FROM inventario.sp_reporte_conciliacion_despacho_resumen(1, 9, 2024, '2024-03-01', '2024-03-31');
    
	RETURN QUERY (
			with ingreso_despacho as (
				select m.mv_solicitud_id as solicitud_id, m.mv_destino_id as destino_id, sd.articulo_id as articulo_id, SUM(md.mvd_cantidad) as cantidad
				from public.movimientos m 
				inner join public.movimiento_detalles md ON m.mv_id = md.mvd_mv_id
				inner join inventario.solicitud_detalle_inventarios sd on sd.solicitud_id = m.mv_solicitud_id and sd.producto_id = md.mvd_articulo_id
				inner join insumos.articulos art on art.id = sd.articulo_id
				where m.mv_estado_id <> 11 and m.deleted_at isnull
				and md.mvd_estado='A' --AND md.deleted_at isnull
				and m.mv_tipo = 'INGRESO' --and EXTRACT(YEAR FROM m.created_at::date) = 2024 
				and m.mv_tipo_movimiento_id = 98
				--and art.tipo_material_id = xtipo_producto_id and art.linea_id = tipo_linea_id 
				group by m.mv_solicitud_id, m.mv_destino_id, sd.articulo_id
			), 
		salida_despacho as (
				--2372
				select m.mv_solicitud_id as solicitud_id, m.mv_destino_id as origen_id, md.mvd_articulo_id as articulo_id, SUM(md.mvd_cantidad) as cantidad
				, (select SUM(mvd.mvd_cantidad) as saldo 
					from inventario.movimiento_inventarios as mv 
					inner join 	inventario.movimiento_detalle_inventarios mvd on mv.mv_id = mvd.mvd_mv_id 
					where mv.mv_tipo_solicitud_id = 3 and mv.mv_tipo = 'SALIDA' and mv.mv_solicitud_id = m.mv_solicitud_id and mvd.mvd_articulo_id = md.mvd_articulo_id 
					and mv.mv_estado = 'A' and mvd.mvd_estado = 'A'
				  ) as saldo
				from inventario.movimiento_inventarios m 
				inner join inventario.movimiento_detalle_inventarios md ON m.mv_id = md.mvd_mv_id
				inner join insumos.articulos art on art.id = md.mvd_articulo_id 
				where m.mv_estado_id <> 11 and m.mv_estado='A' --and m.deleted_at isnull
				and md.mvd_estado='A' --AND md.deleted_at isnull
				and m.mv_tipo = 'SALIDA' --and EXTRACT(YEAR FROM m.created_at::date) = 2024 
				and m.mv_tipo_solicitud_id = 3
				--and art.tipo_material_id = xtipo_producto_id and art.linea_id = tipo_linea_id 
				group by m.mv_solicitud_id, m.mv_destino_id, md.mvd_articulo_id
			)
	select t1.tipo_solicitud_id, t9.param_nombre as tipo_solicitud, t1.nro as nro_solicitud, t1.solicitud_id::text as codigo_solicitud, t10.param_nombre as estado
			, tv1.param_nombre as tipo_venta, tv2.param_nombre as tipo_orden	
			, t8.name::text as usuario_solicitud, t6.nombre as origen, t7.nombre as destino
			, t1.data->>'cli_razon_social' as nombre_productor, t1.data->>'cliente_numero_identificacion' as numero_identificacion_productor, t1.data->>'asociacion' as asociacion
			, art.id as codigo_unico_producto
			, art.nombre_producto as producto
			, t1.created_at as fecha_solicitud
			, round(t2.cantidad,2) as cantidad_solicitada
			, p2.nombre AS nombre_origen
			, round(salida_despacho.cantidad,2) AS cantidad_salida
			, round(salida_despacho.saldo,2) AS cantidad_salida_acumulado
			, round((t2.cantidad - salida_despacho.saldo),2) AS saldo
			, t3.nombre as producto_homologado_comercializacion
			, p3.nombre as nombre_destino
			, round(ingreso_despacho.cantidad,2) AS cantidad_ingreso
			from inventario.solicitud_inventarios t1
			inner join inventario.solicitud_detalle_inventarios t2 on t1.id = t2.solicitud_id  --and t2.estado= 'A'
			inner join insumos.articulos art on art.id = t2.articulo_id  and art.tipo_material_id = xtipo_producto_id and art.linea_id = tipo_linea_id 
			left join public.articulos t3 on t3.id = t2.producto_id
			left join public.sucursals t6 on t6.id = t1.origen_id
			left join public.sucursals t7 on t7.id = t1.destino_id
			left join public.users t8 on t8.id = t1.usr_registrado
			left join acopio.parametricas as t9 ON t1.tipo_solicitud_id = t9.param_valor AND t9.param_tabla = 'TABLA_TIPO_SOLICITUD'
			left join acopio.parametricas as t10 ON t1.estado_id = t10.param_valor AND t10.param_tabla = 'TABLA_TIPO_ESTADO'
			left join public.parametricas as tv1 ON (t1.data->>'tipo_venta_id')::bigint = tv1.param_valor AND tv1.param_tabla = 'TABLA_TIPO_VENTA_DESPACHO'
			left join public.parametricas as tv2 ON (t1.data->>'tipo_orden_id')::bigint = tv2.param_valor AND tv2.param_tabla = 'TABLA_TIPO_ORDEN_DESPACHO'
			left join salida_despacho on t1.id = salida_despacho.solicitud_id and t2.articulo_id = salida_despacho.articulo_id
			left join ingreso_despacho on t1.id = ingreso_despacho.solicitud_id and t2.articulo_id = ingreso_despacho.articulo_id
			left join public.sucursals p2 on salida_despacho.origen_id 	= p2.id
			left join public.sucursals p3 on ingreso_despacho.destino_id = p3.id
			where t1.estado_registro = 'A' and t1.tipo_solicitud_id in (3) --
		and t1.created_at::date >= fecha_ini::date 
		--and art.linea_id = tipo_linea_id 
			order by 1, t1.nro, art.id
			
	);

END;
$BODY$;

ALTER FUNCTION inventario.sp_reporte_conciliacion_despacho_resumen(integer, integer, integer, text, text)
    OWNER TO postgres;
