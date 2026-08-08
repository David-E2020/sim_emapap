CREATE OR REPLACE FUNCTION inventario.sp_reporte_movimiento_productos_almacen(
	planta_id integer,
	almacen_id integer,
	gestion_id integer,
	grano_id integer)
    RETURNS TABLE(sol_codigo text, sol_tipo text, producto text, sol_fecha text, origen text, fecha_origen text, conductor_origen text, placa_origen text, cantidad_origen numeric, destino text, fecha_destino text, placa_destino text, cantidad_destino numeric, diferencia numeric) 
    LANGUAGE 'plpgsql'
    COST 100
    VOLATILE PARALLEL UNSAFE
    ROWS 1000

AS $BODY$
BEGIN
 -- SELECT * FROM inventario.sp_reporte_movimiento_productos_almacen(41, 189, 2024, 8);
    RETURN QUERY (
        SELECT salida.solicitud_id, salida.tipo_solicitud, salida.nombre_producto, salida.fecha_solicitud, salida.alm_origen, salida.fecha_sal, salida.conductor, salida.placa_vehiculo, salida.cant_sal,
                ingreso.alm_destino, ingreso.fecha_ing, ingreso.placa_vehiculo,ingreso.cant_ing, (salida.cant_sal-ingreso.cant_ing) AS diferencia
        FROM (
            SELECT 
                inv.solicitud_id::text,
                par.param_nombre AS tipo_solicitud,
                art.nombre_producto,
                inv.fecha_solicitud::text,
                suc.nombre AS alm_origen,
                mov.created_at::text AS fecha_sal,
                det.mvd_cantidad AS cant_sal,
                UPPER(con.nombre_completo::text) AS conductor,
                UPPER(veh.placa::text) AS placa_vehiculo,
                mov.mv_datos->>'logistica_id' AS logistica_det
            FROM inventario.movimiento_inventarios mov
            INNER JOIN public.transportes veh ON (mov.mv_datos->>'vehiculo_id')::bigint = veh.id
            INNER JOIN public.conductors con ON (mov.mv_datos->>'conductor_id')::bigint = con.id
            INNER JOIN inventario.solicitud_inventarios inv ON mov.mv_solicitud_id = inv.id
            INNER JOIN public.sucursals suc ON mov.mv_origen_id = suc.id AND suc.id = almacen_id
            INNER JOIN acopio.parametricas as par ON mov.mv_tipo_solicitud_id = par.param_valor AND par.param_tabla = 'TABLA_TIPO_SOLICITUD' AND par.param_valor <> 0
            INNER JOIN inventario.movimiento_detalle_inventarios det ON mov.mv_id = det.mvd_mv_id
            INNER JOIN insumos.articulos art ON det.mvd_articulo_id = art.id AND art.linea_id = grano_id
            WHERE mov.mv_tipo = 'SALIDA' AND EXTRACT(YEAR FROM mov.created_at::date) = gestion_id
        ) AS salida
        LEFT JOIN (
            SELECT 
                suc2.nombre AS alm_destino,
                mov.created_at::text AS fecha_ing,
                det.mvd_cantidad AS cant_ing,
                UPPER(con.nombre_completo::text) AS conductor,
                UPPER(veh.placa::text) AS placa_vehiculo,
                mov.mv_datos->>'logistica_id' AS logistica_det
            FROM inventario.movimiento_inventarios mov
            INNER JOIN public.transportes veh ON (mov.mv_datos->>'vehiculo_id')::bigint = veh.id 
            INNER JOIN public.conductors con ON (mov.mv_datos->>'conductor_id')::bigint = con.id
            INNER JOIN inventario.solicitud_inventarios inv ON mov.mv_solicitud_id = inv.id
            INNER JOIN public.sucursals suc ON mov.mv_origen_id = suc.id AND suc.id = almacen_id
            INNER JOIN public.sucursals suc2 ON mov.mv_destino_id = suc2.id
            INNER JOIN acopio.parametricas as par ON mov.mv_tipo_solicitud_id = par.param_valor AND par.param_tabla = 'TABLA_TIPO_SOLICITUD' AND par.param_valor <> 0
            INNER JOIN inventario.movimiento_detalle_inventarios det ON mov.mv_id = det.mvd_mv_id
            INNER JOIN insumos.articulos art ON det.mvd_articulo_id = art.id AND art.linea_id = grano_id
            WHERE mov.mv_tipo = 'INGRESO' AND EXTRACT(YEAR FROM mov.created_at::date) = gestion_id
        ) AS ingreso ON salida.logistica_det = ingreso.logistica_det
        ORDER BY salida.fecha_sal ASC

    );
END;
$BODY$;