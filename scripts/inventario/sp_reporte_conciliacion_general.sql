CREATE OR REPLACE FUNCTION inventario.sp_reporte_conciliacion_general(
	xtipo_solicitud_id integer,
	tipo_linea_id integer,
	gestion integer,
	fecha_ini text,
    fecha_fin text
    )
    RETURNS TABLE(
        tipo_solicitud_id integer, tipo_solicitud text, nro_solicitud bigint, codigo_solicitud text, estado text, usuario_solicitud text, origen text, destino text
        , nombre_productor text, numero_identificacion_productor text, asociacion text
        , logistica_id bigint, nro_logistica bigint, codigo_logistica text /*usuario_registrado_logistica text, contrato text, tipo_contrato text*/
        , transportadora text, conductor text, conductor_identificacion text, placa_vehiculo text

        , codigo_unico_producto bigint, producto text 
        , fecha_solicitud timestamp without time zone, cantidad_solicitada numeric

        , nombre_origen text, nro_boleta_salida integer, fecha_salida timestamp without time zone, cantidad_salida numeric, cantidad_salida_acumulado numeric, saldo numeric
        --, usuario_registro_salida text
        , nombre_destino text, nro_boleta_ingreso integer, fecha_ingreso timestamp without time zone, cantidad_ingreso numeric, saldo_faltante_ingreso numeric
        --, usuario_registro_ingreso text, saldo numeric
    ) 
    LANGUAGE 'plpgsql'
    COST 100
    VOLATILE PARALLEL UNSAFE
    ROWS 1000

AS $BODY$
BEGIN
 -- SELECT * FROM inventario.sp_reporte_conciliacion_general(3, 8, 2024, '2024-04-01', '2024-04-25');
    RETURN QUERY (
        select t1.tipo_solicitud_id, t9.param_nombre as tipo_solicitud, t1.nro as nro_solicitud, t1.solicitud_id::text as codigo_solicitud, t10.param_nombre as estado
        , t8.name::text as usuario_solicitud, t6.nombre as origen, t7.nombre as destino

        , t1.data->>'cli_razon_social' as nombre_productor, t1.data->>'cliente_numero_identificacion' as numero_identificacion_productor, t1.data->>'asociacion' as asociacion
        --, salida.logistica_id
        
        , t4.id::bigint as logistica_id, t4.nro::bigint as nro_logistica, t4.codigo_boleta as codigo_logistica
        , case when t1.tipo_solicitud_id = 3 then '-' when t1.tipo_solicitud_id !=3 then UPPER(d.nombre::text) end as transportadora
        , case when t1.tipo_solicitud_id = 3 then UPPER(cp.nombre_completo::text) when t1.tipo_solicitud_id !=3 then UPPER(c.nombre_completo::text) end AS conductor
        , case when t1.tipo_solicitud_id = 3 then UPPER(cp.numero_identificacion::text) when t1.tipo_solicitud_id !=3 then UPPER(c.numero_identificacion::text) end AS conductor_identificacion
        , case when t1.tipo_solicitud_id = 3 then UPPER(vp.placa::text) when t1.tipo_solicitud_id !=3 then UPPER(v.placa::text) end AS placa_vehiculo
        , t5.id as codigo_unico_producto
        , t5.nombre_producto as producto
        , t1.created_at as fecha_solicitud
        , t2.cantidad as cantidad_solicitada
        , case when t1.tipo_solicitud_id = 3 then p2.nombre when t1.tipo_solicitud_id !=3 then p1.nombre end AS nombre_origen
        , case when t1.tipo_solicitud_id = 3 then salida_despacho.mv_nro_correlativo when t1.tipo_solicitud_id !=3 then salida.mv_nro_correlativo end AS nro_boleta_salida
        , case when t1.tipo_solicitud_id = 3 then salida_despacho.fecha when t1.tipo_solicitud_id !=3 then salida.fecha end AS fecha_salida
        , case when t1.tipo_solicitud_id = 3 then salida_despacho.cantidad when t1.tipo_solicitud_id !=3 then salida.cantidad end AS cantidad_salida
        , case when t1.tipo_solicitud_id = 3 then salida_despacho.saldo when t1.tipo_solicitud_id !=3 then salida.saldo end AS cantidad_salida_acumulado
        , case when t1.tipo_solicitud_id = 3 then (t2.cantidad - salida_despacho.saldo) when t1.tipo_solicitud_id !=3 then (t2.cantidad - salida.saldo) end AS saldo
        , p3.nombre as nombre_destino
        , ingreso.mv_nro_correlativo as nro_boleta_ingreso
        , ingreso.fecha AS fecha_ingreso
        , ingreso.cantidad AS cantidad_ingreso
        , (salida.cantidad-ingreso.cantidad) as saldo_faltante_ingreso
        from inventario.solicitud_inventarios t1
        left join inventario.solicitud_detalle_inventarios t2 on t1.id = t2.solicitud_id  --and t2.estado= 'A'
        left join logistica.solicitudes_movimiento_logistica t3 on t3.id_orden = t1.id
        left join logistica.solicitud_movimiento_detalle_logistica t4 on t3.id = t4.logistica_id and t4.solicitud_id = t1.id
        inner join insumos.articulos t5 on t5.id = t2.articulo_id
        left join public.sucursals t6 on t6.id = t1.origen_id
        left join public.sucursals t7 on t7.id = t1.destino_id
        left join public.users t8 on t8.id = t1.usr_registrado
        left join acopio.parametricas as t9 ON t1.tipo_solicitud_id = t9.param_valor AND t9.param_tabla = 'TABLA_TIPO_SOLICITUD'
        left join acopio.parametricas as t10 ON t1.estado_id = t10.param_valor AND t10.param_tabla = 'TABLA_TIPO_ESTADO'
        left join acopio.parametricas as t11 ON t5.tipo_material_id = t11.param_valor AND t11.param_tabla = 'TABLA_TIPO_PRODUCTO_INSUMOS'

        left join public.distribuidoras d on d.id = t4.distribuidora_id
        left join public.conductors c on c.id = t4.conductor_id
        left join public.transportes v on v.id = t4.vehiculo_id
        left join (
            --2984 --2985  ejemplo 504
            select m.mv_id, m.mv_nro_correlativo, sld.codigo_boleta as codigo_logitica, m.created_at as fecha, m.mv_solicitud_id as solicitud_id, m.mv_destino_id as origen_id, md.mvd_articulo_id as articulo_id, sld.id as logistica_id, SUM(md.mvd_cantidad) as cantidad
            , (select SUM(mvd.mvd_cantidad) as saldo 
                from inventario.movimiento_inventarios as mv 
                inner join 	inventario.movimiento_detalle_inventarios mvd on mv.mv_id = mvd.mvd_mv_id 
                where mv.mv_tipo = 'SALIDA' and mv.mv_solicitud_id = m.mv_solicitud_id and m.mv_id >= mv.mv_id  and mvd.mvd_articulo_id = md.mvd_articulo_id 
            ) as saldo
            from inventario.movimiento_inventarios m 
            inner join inventario.movimiento_detalle_inventarios md ON m.mv_id = md.mvd_mv_id
            left join logistica.solicitudes_movimiento_logistica sl on sl.id_orden = m.mv_solicitud_id
            left join logistica.solicitud_movimiento_detalle_logistica sld on sl.id = sld.logistica_id and sld.solicitud_id = m.mv_solicitud_id
            where m.mv_estado_id <> 11 and m.mv_estado='A' --and m.deleted_at isnull
            and md.mvd_estado='A' --AND md.deleted_at isnull
            and m.mv_tipo = 'SALIDA' --and EXTRACT(YEAR FROM m.created_at::date) = 2024 
            and sld.id = (m.mv_datos->>'logistica_id')::bigint
            group by m.mv_id, m.mv_nro_correlativo, sld.codigo_boleta, m.created_at, m.mv_solicitud_id, m.mv_destino_id, md.mvd_articulo_id, sld.id 
            order by m.mv_solicitud_id, m.mv_nro_correlativo
        ) as salida on t1.id = salida.solicitud_id and t2.articulo_id = salida.articulo_id and salida.logistica_id = t4.id
        left join (
            --2984
            select m.mv_id, m.mv_nro_correlativo, m.created_at as fecha, m.mv_solicitud_id as solicitud_id, m.mv_destino_id as destino_id, md.mvd_articulo_id as articulo_id, sld.id as logistica_id, SUM(md.mvd_cantidad) as cantidad
            from inventario.movimiento_inventarios m 
            inner join inventario.movimiento_detalle_inventarios md ON m.mv_id = md.mvd_mv_id
            left join logistica.solicitudes_movimiento_logistica sl on sl.id_orden = m.mv_solicitud_id
            left join logistica.solicitud_movimiento_detalle_logistica sld on sl.id = sld.logistica_id and sld.solicitud_id = m.mv_solicitud_id
            where m.mv_estado_id <> 11 and m.mv_estado='A' --and m.deleted_at isnull
            and md.mvd_estado='A' --AND md.deleted_at isnull
            and m.mv_tipo = 'INGRESO' --and EXTRACT(YEAR FROM m.created_at::date) = 2024 
            and sld.id = (m.mv_datos->>'logistica_id')::bigint
            group by m.mv_id, m.mv_nro_correlativo, m.created_at, m.mv_solicitud_id, m.mv_destino_id, md.mvd_articulo_id, sld.id 
        ) as ingreso on t1.id = ingreso.solicitud_id and t2.articulo_id = ingreso.articulo_id and ingreso.logistica_id = t4.id
        left join (
            --2372
            select m.mv_id, m.mv_nro_correlativo, m.created_at as fecha, m.mv_solicitud_id as solicitud_id, m.mv_destino_id as origen_id, md.mvd_articulo_id as articulo_id, m.mv_datos->>'vehiculo_id' as vehiculo_id, m.mv_datos->>'conductor_id' as conductor_id, SUM(md.mvd_cantidad) as cantidad
            , (select SUM(mvd.mvd_cantidad) as saldo 
                from inventario.movimiento_inventarios as mv 
                inner join 	inventario.movimiento_detalle_inventarios mvd on mv.mv_id = mvd.mvd_mv_id 
                where mv.mv_tipo = 'SALIDA' and mv.mv_solicitud_id = m.mv_solicitud_id and m.mv_id >= mv.mv_id  and mvd.mvd_articulo_id = md.mvd_articulo_id 
            ) as saldo
            from inventario.movimiento_inventarios m 
            inner join inventario.movimiento_detalle_inventarios md ON m.mv_id = md.mvd_mv_id
            where m.mv_estado_id <> 11 and m.mv_estado='A' --and m.deleted_at isnull
            and md.mvd_estado='A' --AND md.deleted_at isnull
            and m.mv_tipo = 'SALIDA' --and EXTRACT(YEAR FROM m.created_at::date) = 2024 
            and m.mv_tipo_solicitud_id = 3
            group by m.mv_id, m.mv_nro_correlativo, m.created_at, m.mv_solicitud_id, m.mv_destino_id, md.mvd_articulo_id, m.mv_datos->>'vehiculo_id', m.mv_datos->>'conductor_id'
            order by m.mv_solicitud_id
        ) as salida_despacho on t1.id = salida_despacho.solicitud_id and t2.articulo_id = salida_despacho.articulo_id
        left join public.conductors cp on cp.id = case when salida_despacho.conductor_id ='' then 0 else salida_despacho.conductor_id::bigint end
        left join public.transportes vp on vp.id = case when salida_despacho.vehiculo_id ='' then 0 else salida_despacho.vehiculo_id ::bigint end

        left join public.sucursals p1 on salida.origen_id 			= p1.id
        left join public.sucursals p2 on salida_despacho.origen_id 	= p2.id
        left join public.sucursals p3 on ingreso.destino_id 			= p3.id
        where t1.estado_registro = 'A' and t1.tipo_solicitud_id in (3, 4, 7, 8) 
        and t1.tipo_solicitud_id = xtipo_solicitud_id
		and t5.linea_id = tipo_linea_id
		and t1.created_at::date BETWEEN fecha_ini::date AND fecha_fin::date
        order by 1, t1.nro, t5.id, salida.mv_nro_correlativo

    );
END;
$BODY$;

