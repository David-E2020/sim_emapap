-- FUNCTION: inventario.sp_reporte_movimientos_inventarios(text, text, integer, integer, integer, integer, integer)

-- DROP FUNCTION IF EXISTS inventario.sp_reporte_movimientos_inventarios(text, text, integer, integer, integer, integer, integer);

CREATE OR REPLACE FUNCTION inventario.sp_reporte_movimientos_inventarios(
	fecha_ini text,
	fecha_fin text,
	xtipo_planta_id integer,
	xpunto_id integer,
	xgestion integer,
	xtipo_movimiento_id integer,
	xprograma_id integer)
    RETURNS TABLE(tipo_planta text, plantas text, punto text, tipo_movimiento text, tipo text, codigo text, producto text, cantidad_solicitud numeric, numero_boleta integer, fecha_movimiento timestamp without time zone, cantidad_movimiento numeric, cantidad_acumulado_saldo numeric, saldo numeric, observacion text) 
    LANGUAGE 'plpgsql'
    COST 100
    VOLATILE PARALLEL UNSAFE
    ROWS 1000

AS $BODY$
BEGIN
 -- SELECT * FROM inventario.sp_reporte_movimientos_inventarios('01-02-2024', '31-05-2024', 1000, 61, 2024,3, 4);
    IF (xtipo_planta_id != 1000) THEN
		IF (xtipo_movimiento_id != 1000) THEN
			IF (xprograma_id != 1000) THEN
				RETURN QUERY (
					--9380
					select tp.param_nombre as tipo_planta
					, pl.nombre::text as plantas
					, p.nombre as punto
					, tm.param_nombre as tipo_movimiento
					, m.mv_tipo::text as tipo
					, sp.solicitud_id::text as codigo
					, art.nombre_producto as producto
					, round(spd.cantidad, 2) as cantidad_solicitud
					, m.mv_nro_correlativo as numero_boleta
					, m.created_at fecha_movimiento
					, round(md.mvd_cantidad, 2) as cantidad_movimiento
					, round((select SUM(tmd.mvd_cantidad) as saldo 
								from inventario.movimiento_inventarios as tm 
								inner join 	inventario.movimiento_detalle_inventarios tmd on tm.mv_id = tmd.mvd_mv_id 
								where tm.mv_tipo = m.mv_tipo and tm.mv_solicitud_id = m.mv_solicitud_id and m.mv_id >= tm.mv_id  and md.mvd_articulo_id = tmd.mvd_articulo_id 
							 	and tm.mv_estado = 'A' and tmd.mvd_estado = 'A'
							), 2) as cantidad_movimiento_acumulado
					, round(spd.cantidad - (select SUM(tmd.mvd_cantidad) as saldo 
								from inventario.movimiento_inventarios as tm 
								inner join 	inventario.movimiento_detalle_inventarios tmd on tm.mv_id = tmd.mvd_mv_id 
								where tm.mv_tipo = m.mv_tipo and tm.mv_solicitud_id = m.mv_solicitud_id and m.mv_id >= tm.mv_id  and md.mvd_articulo_id = tmd.mvd_articulo_id 
								and tm.mv_estado = 'A' and tmd.mvd_estado = 'A'
							), 2) as saldo
					, m.observacion
					from inventario.movimiento_inventarios m 
					inner join inventario.movimiento_detalle_inventarios md on m.mv_id = md.mvd_mv_id
					left join inventario.solicitud_inventarios sp on sp.id = m.mv_solicitud_id
					left join inventario.solicitud_detalle_inventarios spd on sp.id = spd.solicitud_id and spd.articulo_id = md.mvd_articulo_id
					inner join insumos.articulos art on art.id = md.mvd_articulo_id
					left join inventario.lotes as l on l.id = md.mvd_lote_id
					left join public.sucursals as p on p.id = m.mv_destino_id 
					left join acopio.plantas as pl on pl.id = p.planta_id
					left join acopio.parametricas as tp ON pl.tipo_acopio_id = tp.param_valor AND tp.param_tabla = 'TABLA_TIPO_PLANTA'
					left join acopio.parametricas as tm ON m.mv_tipo_movimiento_id = tm.param_valor AND tm.param_tabla = 'TABLA_TIPO_MOVIMIENTO'
					where m.mv_estado_id <> 11 and m.mv_estado='A'
					and md.mvd_estado='A' 
					and m.mv_tipo_movimiento_id = xtipo_movimiento_id
					and m.mv_destino_id = xpunto_id
					and l.programa_id = xprograma_id
					and m.created_at::date BETWEEN fecha_ini::date AND fecha_fin::date
					order by 1, 2, 3, 4, 5, 7, 9
				);
			ELSE 
				RETURN QUERY (
					--9380
					select tp.param_nombre as tipo_planta
					, pl.nombre::text as plantas
					, p.nombre as punto
					, tm.param_nombre as tipo_movimiento
					, m.mv_tipo::text as tipo
					, sp.solicitud_id::text as codigo
					, art.nombre_producto as producto
					, round(spd.cantidad, 2) as cantidad_solicitud
					, m.mv_nro_correlativo as numero_boleta
					, m.created_at fecha_movimiento
					, round(md.mvd_cantidad, 2) as cantidad_movimiento
					, round((select SUM(tmd.mvd_cantidad) as saldo 
								from inventario.movimiento_inventarios as tm 
								inner join 	inventario.movimiento_detalle_inventarios tmd on tm.mv_id = tmd.mvd_mv_id 
								where tm.mv_tipo = m.mv_tipo and tm.mv_solicitud_id = m.mv_solicitud_id and m.mv_id >= tm.mv_id  and md.mvd_articulo_id = tmd.mvd_articulo_id 
							 	and tm.mv_estado = 'A' and tmd.mvd_estado = 'A'
							), 2) as cantidad_movimiento_acumulado
					, round(spd.cantidad - (select SUM(tmd.mvd_cantidad) as saldo 
								from inventario.movimiento_inventarios as tm 
								inner join 	inventario.movimiento_detalle_inventarios tmd on tm.mv_id = tmd.mvd_mv_id 
								where tm.mv_tipo = m.mv_tipo and tm.mv_solicitud_id = m.mv_solicitud_id and m.mv_id >= tm.mv_id  and md.mvd_articulo_id = tmd.mvd_articulo_id 
								and tm.mv_estado = 'A' and tmd.mvd_estado = 'A'			
							), 2) as saldo
					, m.observacion
					from inventario.movimiento_inventarios m 
					inner join inventario.movimiento_detalle_inventarios md on m.mv_id = md.mvd_mv_id
					left join inventario.solicitud_inventarios sp on sp.id = m.mv_solicitud_id
					left join inventario.solicitud_detalle_inventarios spd on sp.id = spd.solicitud_id and spd.articulo_id = md.mvd_articulo_id
					inner join insumos.articulos art on art.id = md.mvd_articulo_id
					left join inventario.lotes as l on l.id = md.mvd_lote_id
					left join public.sucursals as p on p.id = m.mv_destino_id 
					left join acopio.plantas as pl on pl.id = p.planta_id
					left join acopio.parametricas as tp ON pl.tipo_acopio_id = tp.param_valor AND tp.param_tabla = 'TABLA_TIPO_PLANTA'
					left join acopio.parametricas as tm ON m.mv_tipo_movimiento_id = tm.param_valor AND tm.param_tabla = 'TABLA_TIPO_MOVIMIENTO'
					where m.mv_estado_id <> 11 and m.mv_estado='A'
					and md.mvd_estado='A' 
					and m.mv_tipo_movimiento_id = xtipo_movimiento_id
					and m.mv_destino_id = xpunto_id
					and m.created_at::date BETWEEN fecha_ini::date AND fecha_fin::date
					order by 1, 2, 3, 4, 5, 7, 9
				);
			END IF;
		ELSE 
			IF (xprograma_id != 1000) THEN
				RETURN QUERY (
					--9380
					select tp.param_nombre as tipo_planta
					, pl.nombre::text as plantas
					, p.nombre as punto
					, tm.param_nombre as tipo_movimiento
					, m.mv_tipo::text as tipo
					, sp.solicitud_id::text as codigo
					, art.nombre_producto as producto
					, round(spd.cantidad, 2) as cantidad_solicitud
					, m.mv_nro_correlativo as numero_boleta
					, m.created_at fecha_movimiento
					, round(md.mvd_cantidad, 2) as cantidad_movimiento
					, round((select SUM(tmd.mvd_cantidad) as saldo 
								from inventario.movimiento_inventarios as tm 
								inner join 	inventario.movimiento_detalle_inventarios tmd on tm.mv_id = tmd.mvd_mv_id 
								where tm.mv_tipo = m.mv_tipo and tm.mv_solicitud_id = m.mv_solicitud_id and m.mv_id >= tm.mv_id  and md.mvd_articulo_id = tmd.mvd_articulo_id 
								and tm.mv_estado = 'A' and tmd.mvd_estado = 'A'
							), 2) as cantidad_movimiento_acumulado
					, round(spd.cantidad - (select SUM(tmd.mvd_cantidad) as saldo 
								from inventario.movimiento_inventarios as tm 
								inner join 	inventario.movimiento_detalle_inventarios tmd on tm.mv_id = tmd.mvd_mv_id 
								where tm.mv_tipo = m.mv_tipo and tm.mv_solicitud_id = m.mv_solicitud_id and m.mv_id >= tm.mv_id  and md.mvd_articulo_id = tmd.mvd_articulo_id 
								and tm.mv_estado = 'A' and tmd.mvd_estado = 'A'
							), 2) as saldo
					, m.observacion
					from inventario.movimiento_inventarios m 
					inner join inventario.movimiento_detalle_inventarios md on m.mv_id = md.mvd_mv_id
					left join inventario.solicitud_inventarios sp on sp.id = m.mv_solicitud_id
					left join inventario.solicitud_detalle_inventarios spd on sp.id = spd.solicitud_id and spd.articulo_id = md.mvd_articulo_id
					inner join insumos.articulos art on art.id = md.mvd_articulo_id
					left join inventario.lotes as l on l.id = md.mvd_lote_id
					left join public.sucursals as p on p.id = m.mv_destino_id 
					left join acopio.plantas as pl on pl.id = p.planta_id
					left join acopio.parametricas as tp ON pl.tipo_acopio_id = tp.param_valor AND tp.param_tabla = 'TABLA_TIPO_PLANTA'
					left join acopio.parametricas as tm ON m.mv_tipo_movimiento_id = tm.param_valor AND tm.param_tabla = 'TABLA_TIPO_MOVIMIENTO'
					where m.mv_estado_id <> 11 and m.mv_estado='A'
					and md.mvd_estado='A' 
					and m.mv_destino_id = xpunto_id
					and l.programa_id = xprograma_id
					and m.created_at::date BETWEEN fecha_ini::date AND fecha_fin::date
					order by 1, 2, 3, 4, 5, 7, 9
				);
			ELSE 
				RETURN QUERY (
					--9380
					select tp.param_nombre as tipo_planta
					, pl.nombre::text as plantas
					, p.nombre as punto
					, tm.param_nombre as tipo_movimiento
					, m.mv_tipo::text as tipo
					, sp.solicitud_id::text as codigo
					, art.nombre_producto as producto
					, round(spd.cantidad, 2) as cantidad_solicitud
					, m.mv_nro_correlativo as numero_boleta
					, m.created_at fecha_movimiento
					, round(md.mvd_cantidad, 2) as cantidad_movimiento
					, round((select SUM(tmd.mvd_cantidad) as saldo 
								from inventario.movimiento_inventarios as tm 
								inner join 	inventario.movimiento_detalle_inventarios tmd on tm.mv_id = tmd.mvd_mv_id 
								where tm.mv_tipo = m.mv_tipo and tm.mv_solicitud_id = m.mv_solicitud_id and m.mv_id >= tm.mv_id  and md.mvd_articulo_id = tmd.mvd_articulo_id 
							 	and tm.mv_estado = 'A' and tmd.mvd_estado = 'A'
							), 2) as cantidad_movimiento_acumulado
					, round(spd.cantidad - (select SUM(tmd.mvd_cantidad) as saldo 
								from inventario.movimiento_inventarios as tm 
								inner join 	inventario.movimiento_detalle_inventarios tmd on tm.mv_id = tmd.mvd_mv_id 
								where tm.mv_tipo = m.mv_tipo and tm.mv_solicitud_id = m.mv_solicitud_id and m.mv_id >= tm.mv_id  and md.mvd_articulo_id = tmd.mvd_articulo_id 
								and tm.mv_estado = 'A' and tmd.mvd_estado = 'A'		
							), 2) as saldo
					, m.observacion
					from inventario.movimiento_inventarios m 
					inner join inventario.movimiento_detalle_inventarios md on m.mv_id = md.mvd_mv_id
					left join inventario.solicitud_inventarios sp on sp.id = m.mv_solicitud_id
					left join inventario.solicitud_detalle_inventarios spd on sp.id = spd.solicitud_id and spd.articulo_id = md.mvd_articulo_id
					inner join insumos.articulos art on art.id = md.mvd_articulo_id
					left join inventario.lotes as l on l.id = md.mvd_lote_id
					left join public.sucursals as p on p.id = m.mv_destino_id 
					left join acopio.plantas as pl on pl.id = p.planta_id
					left join acopio.parametricas as tp ON pl.tipo_acopio_id = tp.param_valor AND tp.param_tabla = 'TABLA_TIPO_PLANTA'
					left join acopio.parametricas as tm ON m.mv_tipo_movimiento_id = tm.param_valor AND tm.param_tabla = 'TABLA_TIPO_MOVIMIENTO'
					where m.mv_estado_id <> 11 and m.mv_estado='A'
					and md.mvd_estado='A' 
					and m.mv_destino_id = xpunto_id
					and m.created_at::date BETWEEN fecha_ini::date AND fecha_fin::date
					order by 1, 2, 3, 4, 5, 7, 9
				);
			END IF;
		END IF;
	ELSE 
		IF (xtipo_movimiento_id != 1000) THEN
			IF (xprograma_id != 1000) THEN
				RETURN QUERY (
					--9380
					select tp.param_nombre as tipo_planta
					, pl.nombre::text as plantas
					, p.nombre as punto
					, tm.param_nombre as tipo_movimiento
					, m.mv_tipo::text as tipo
					, sp.solicitud_id::text as codigo
					, art.nombre_producto as producto
					, round(spd.cantidad, 2) as cantidad_solicitud
					, m.mv_nro_correlativo as numero_boleta
					, m.created_at fecha_movimiento
					, round(md.mvd_cantidad, 2) as cantidad_movimiento
					, round((select SUM(tmd.mvd_cantidad) as saldo 
								from inventario.movimiento_inventarios as tm 
								inner join 	inventario.movimiento_detalle_inventarios tmd on tm.mv_id = tmd.mvd_mv_id 
								where tm.mv_tipo = m.mv_tipo and tm.mv_solicitud_id = m.mv_solicitud_id and m.mv_id >= tm.mv_id  and md.mvd_articulo_id = tmd.mvd_articulo_id 
							 	and tm.mv_estado = 'A' and tmd.mvd_estado = 'A'
							), 2) as cantidad_movimiento_acumulado
					, round(spd.cantidad - (select SUM(tmd.mvd_cantidad) as saldo 
								from inventario.movimiento_inventarios as tm 
								inner join 	inventario.movimiento_detalle_inventarios tmd on tm.mv_id = tmd.mvd_mv_id 
								where tm.mv_tipo = m.mv_tipo and tm.mv_solicitud_id = m.mv_solicitud_id and m.mv_id >= tm.mv_id  and md.mvd_articulo_id = tmd.mvd_articulo_id 
								and tm.mv_estado = 'A' and tmd.mvd_estado = 'A'			
							), 2) as saldo
					, m.observacion
					from inventario.movimiento_inventarios m 
					inner join inventario.movimiento_detalle_inventarios md on m.mv_id = md.mvd_mv_id
					left join inventario.solicitud_inventarios sp on sp.id = m.mv_solicitud_id
					left join inventario.solicitud_detalle_inventarios spd on sp.id = spd.solicitud_id and spd.articulo_id = md.mvd_articulo_id
					inner join insumos.articulos art on art.id = md.mvd_articulo_id
					left join inventario.lotes as l on l.id = md.mvd_lote_id
					left join public.sucursals as p on p.id = m.mv_destino_id 
					left join acopio.plantas as pl on pl.id = p.planta_id
					left join acopio.parametricas as tp ON pl.tipo_acopio_id = tp.param_valor AND tp.param_tabla = 'TABLA_TIPO_PLANTA'
					left join acopio.parametricas as tm ON m.mv_tipo_movimiento_id = tm.param_valor AND tm.param_tabla = 'TABLA_TIPO_MOVIMIENTO'
					where m.mv_estado_id <> 11 and m.mv_estado='A'
					and md.mvd_estado='A' 
					and m.mv_tipo_movimiento_id = xtipo_movimiento_id
					and pl.tipo_acopio_id in (1, 2)
					and l.programa_id = xprograma_id
					and m.created_at::date BETWEEN fecha_ini::date AND fecha_fin::date
					order by 1, 2, 3, 4, 5, 7, 9
				);
			ELSE 
				RETURN QUERY (
					--9380
					select tp.param_nombre as tipo_planta
					, pl.nombre::text as plantas
					, p.nombre as punto
					, tm.param_nombre as tipo_movimiento
					, m.mv_tipo::text as tipo
					, sp.solicitud_id::text as codigo
					, art.nombre_producto as producto
					, round(spd.cantidad, 2) as cantidad_solicitud
					, m.mv_nro_correlativo as numero_boleta
					, m.created_at fecha_movimiento
					, round(md.mvd_cantidad, 2) as cantidad_movimiento
					, round((select SUM(tmd.mvd_cantidad) as saldo 
								from inventario.movimiento_inventarios as tm 
								inner join 	inventario.movimiento_detalle_inventarios tmd on tm.mv_id = tmd.mvd_mv_id 
								where tm.mv_tipo = m.mv_tipo and tm.mv_solicitud_id = m.mv_solicitud_id and m.mv_id >= tm.mv_id  and md.mvd_articulo_id = tmd.mvd_articulo_id 
							 	and tm.mv_estado = 'A' and tmd.mvd_estado = 'A'
							), 2) as cantidad_movimiento_acumulado
					, round(spd.cantidad - (select SUM(tmd.mvd_cantidad) as saldo 
								from inventario.movimiento_inventarios as tm 
								inner join 	inventario.movimiento_detalle_inventarios tmd on tm.mv_id = tmd.mvd_mv_id 
								where tm.mv_tipo = m.mv_tipo and tm.mv_solicitud_id = m.mv_solicitud_id and m.mv_id >= tm.mv_id  and md.mvd_articulo_id = tmd.mvd_articulo_id 
								and tm.mv_estado = 'A' and tmd.mvd_estado = 'A'		
							), 2) as saldo
					, m.observacion
					from inventario.movimiento_inventarios m 
					inner join inventario.movimiento_detalle_inventarios md on m.mv_id = md.mvd_mv_id
					left join inventario.solicitud_inventarios sp on sp.id = m.mv_solicitud_id
					left join inventario.solicitud_detalle_inventarios spd on sp.id = spd.solicitud_id and spd.articulo_id = md.mvd_articulo_id
					inner join insumos.articulos art on art.id = md.mvd_articulo_id
					left join inventario.lotes as l on l.id = md.mvd_lote_id
					left join public.sucursals as p on p.id = m.mv_destino_id 
					left join acopio.plantas as pl on pl.id = p.planta_id
					left join acopio.parametricas as tp ON pl.tipo_acopio_id = tp.param_valor AND tp.param_tabla = 'TABLA_TIPO_PLANTA'
					left join acopio.parametricas as tm ON m.mv_tipo_movimiento_id = tm.param_valor AND tm.param_tabla = 'TABLA_TIPO_MOVIMIENTO'
					where m.mv_estado_id <> 11 and m.mv_estado='A'
					and md.mvd_estado='A' 
					and m.mv_tipo_movimiento_id = xtipo_movimiento_id
					and pl.tipo_acopio_id in (1, 2)
					and m.created_at::date BETWEEN fecha_ini::date AND fecha_fin::date
					order by 1, 2, 3, 4, 5, 7, 9
				);
			END IF;
		ELSE 
			IF (xprograma_id != 1000) THEN
				RETURN QUERY (
					--9380
					select tp.param_nombre as tipo_planta
					, pl.nombre::text as plantas
					, p.nombre as punto
					, tm.param_nombre as tipo_movimiento
					, m.mv_tipo::text as tipo
					, sp.solicitud_id::text as codigo
					, art.nombre_producto as producto
					, round(spd.cantidad, 2) as cantidad_solicitud
					, m.mv_nro_correlativo as numero_boleta
					, m.created_at fecha_movimiento
					, round(md.mvd_cantidad, 2) as cantidad_movimiento
					, round((select SUM(tmd.mvd_cantidad) as saldo 
								from inventario.movimiento_inventarios as tm 
								inner join 	inventario.movimiento_detalle_inventarios tmd on tm.mv_id = tmd.mvd_mv_id 
								where tm.mv_tipo = m.mv_tipo and tm.mv_solicitud_id = m.mv_solicitud_id and m.mv_id >= tm.mv_id  and md.mvd_articulo_id = tmd.mvd_articulo_id
							 	and tm.mv_estado = 'A' and tmd.mvd_estado = 'A'
							), 2) as cantidad_movimiento_acumulado
					, round(spd.cantidad - (select SUM(tmd.mvd_cantidad) as saldo 
								from inventario.movimiento_inventarios as tm 
								inner join 	inventario.movimiento_detalle_inventarios tmd on tm.mv_id = tmd.mvd_mv_id 
								where tm.mv_tipo = m.mv_tipo and tm.mv_solicitud_id = m.mv_solicitud_id and m.mv_id >= tm.mv_id  and md.mvd_articulo_id = tmd.mvd_articulo_id 
								and tm.mv_estado = 'A' and tmd.mvd_estado = 'A'			
							), 2) as saldo
					, m.observacion
					from inventario.movimiento_inventarios m 
					inner join inventario.movimiento_detalle_inventarios md on m.mv_id = md.mvd_mv_id
					left join inventario.solicitud_inventarios sp on sp.id = m.mv_solicitud_id
					left join inventario.solicitud_detalle_inventarios spd on sp.id = spd.solicitud_id and spd.articulo_id = md.mvd_articulo_id
					inner join insumos.articulos art on art.id = md.mvd_articulo_id
					left join inventario.lotes as l on l.id = md.mvd_lote_id
					left join public.sucursals as p on p.id = m.mv_destino_id 
					left join acopio.plantas as pl on pl.id = p.planta_id
					left join acopio.parametricas as tp ON pl.tipo_acopio_id = tp.param_valor AND tp.param_tabla = 'TABLA_TIPO_PLANTA'
					left join acopio.parametricas as tm ON m.mv_tipo_movimiento_id = tm.param_valor AND tm.param_tabla = 'TABLA_TIPO_MOVIMIENTO'
					where m.mv_estado_id <> 11 and m.mv_estado='A'
					and md.mvd_estado='A' 
					and pl.tipo_acopio_id in (1, 2)
					and l.programa_id = xprograma_id
					and m.created_at::date BETWEEN fecha_ini::date AND fecha_fin::date
					order by 1, 2, 3, 4, 5, 7, 9
				);
			ELSE 
				RETURN QUERY (
					--9380
					select tp.param_nombre as tipo_planta
					, pl.nombre::text as plantas
					, p.nombre as punto
					, tm.param_nombre as tipo_movimiento
					, m.mv_tipo::text as tipo
					, sp.solicitud_id::text as codigo
					, art.nombre_producto as producto
					, round(spd.cantidad, 2) as cantidad_solicitud
					, m.mv_nro_correlativo as numero_boleta
					, m.created_at fecha_movimiento
					, round(md.mvd_cantidad, 2) as cantidad_movimiento
					, round((select SUM(tmd.mvd_cantidad) as saldo 
								from inventario.movimiento_inventarios as tm 
								inner join 	inventario.movimiento_detalle_inventarios tmd on tm.mv_id = tmd.mvd_mv_id 
								where tm.mv_tipo = m.mv_tipo and tm.mv_solicitud_id = m.mv_solicitud_id and m.mv_id >= tm.mv_id  and md.mvd_articulo_id = tmd.mvd_articulo_id 
							 	and tm.mv_estado = 'A' and tmd.mvd_estado = 'A'
							), 2) as cantidad_movimiento_acumulado
					, round(spd.cantidad - (select SUM(tmd.mvd_cantidad) as saldo 
								from inventario.movimiento_inventarios as tm 
								inner join 	inventario.movimiento_detalle_inventarios tmd on tm.mv_id = tmd.mvd_mv_id 
								where tm.mv_tipo = m.mv_tipo and tm.mv_solicitud_id = m.mv_solicitud_id and m.mv_id >= tm.mv_id  and md.mvd_articulo_id = tmd.mvd_articulo_id 
								and tm.mv_estado = 'A' and tmd.mvd_estado = 'A'		
							), 2) as saldo
					, m.observacion
					from inventario.movimiento_inventarios m 
					inner join inventario.movimiento_detalle_inventarios md on m.mv_id = md.mvd_mv_id
					left join inventario.solicitud_inventarios sp on sp.id = m.mv_solicitud_id
					left join inventario.solicitud_detalle_inventarios spd on sp.id = spd.solicitud_id and spd.articulo_id = md.mvd_articulo_id
					inner join insumos.articulos art on art.id = md.mvd_articulo_id
					left join inventario.lotes as l on l.id = md.mvd_lote_id
					left join public.sucursals as p on p.id = m.mv_destino_id 
					left join acopio.plantas as pl on pl.id = p.planta_id
					left join acopio.parametricas as tp ON pl.tipo_acopio_id = tp.param_valor AND tp.param_tabla = 'TABLA_TIPO_PLANTA'
					left join acopio.parametricas as tm ON m.mv_tipo_movimiento_id = tm.param_valor AND tm.param_tabla = 'TABLA_TIPO_MOVIMIENTO'
					where m.mv_estado_id <> 11 and m.mv_estado='A'
					and md.mvd_estado='A' 
					and pl.tipo_acopio_id in (1, 2)
					and m.created_at::date BETWEEN fecha_ini::date AND fecha_fin::date
					order by 1, 2, 3, 4, 5, 7, 9
				);
			END IF;
		END IF;
	END IF;
END;
$BODY$;

ALTER FUNCTION inventario.sp_reporte_movimientos_inventarios(text, text, integer, integer, integer, integer, integer)
    OWNER TO postgres;
