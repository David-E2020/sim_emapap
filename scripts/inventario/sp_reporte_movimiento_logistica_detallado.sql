-- FUNCTION: inventario.sp_reporte_movimiento_logistica_detallado(integer, integer, integer, integer)

-- DROP FUNCTION IF EXISTS inventario.sp_reporte_movimiento_logistica_detallado(integer, integer, integer, integer);

CREATE OR REPLACE FUNCTION inventario.sp_reporte_movimiento_logistica_detallado(
	planta_id integer,
	almacen_id integer,
	gestion_id integer,
	grano_id integer)
    RETURNS TABLE(sol_codigo text, sol_nro_solicitud text, sol_codigo_logistica text, sol_estado_logistica text, sol_tipo text, sol_fecha_solicitud text, sol_estado text, sol_origen text, sol_destino text, tipo_producto text, codigo_unico_producto bigint, producto text, cantidad_solicitada numeric, usuario_registrado_solicitud text, cantidad_asignado_logistica numeric, usuario_registrado_logistica text, contrato text, tipo_contrato text, transportadora text, placa text, marca_vehiculo text, tipo_vehiculo text, color_vehiculo text, conductor text, conductor_identificacion text, nro_boleta_salida integer, origen text, fecha_origen text, cantidad_origen numeric, usuario_registro_salida text, nro_boleta_ingreso integer, destino text, fecha_destino text, cantidad_destino numeric, usuario_registro_ingreso text, saldo numeric) 
    LANGUAGE 'plpgsql'
    COST 100
    VOLATILE PARALLEL UNSAFE
    ROWS 1000

AS $BODY$
BEGIN
 -- SELECT * FROM inventario.sp_reporte_movimiento_logistica_detallado(11, 170, 2024, 8);
 -- SELECT * FROM inventario.sp_reporte_movimiento_logistica_detallado(7, 61, 2024, 131);
    RETURN QUERY (
        SELECT salida.solicitud_id, salida.nro_solicitud, salida.codigo_boleta_logistica,
               salida.estado_logistica, salida.tipo_solicitud, salida.fecha_solicitud,
               salida.estado, salida.sol_origen, salida.sol_destino
		, salida.tipo_producto, salida.codigo_producto, salida.nombre_producto,
		salida.cantidad_solicitada, salida.usuario_registrado_solicitud,
		salida.cantidad_asignado, salida.usuario_registrado_logistica
		, salida.contrato, salida.tipo_contrato, salida.transportadora, salida.placa_vehiculo
		, salida.marca_vehiculo, salida.tipo_vehiculo,salida.color_vehiculo , salida.conductor, salida.conductor_identificacion
		, salida.nro_boleta_salida, salida.alm_origen, salida.fecha_sal,
		  salida.cant_sal, salida.usuario_registro_salida
		, ingreso.nro_boleta_ingreso, ingreso.alm_destino, ingreso.fecha_ing,ingreso.cant_ing,
		  ingreso.usuario_registro_ingreso, (salida.cant_sal-ingreso.cant_ing) AS saldo
        FROM (
            SELECT
				inv.id
                , inv.solicitud_id::text
				, inv.nro::text as nro_solicitud
			    , lg.codigo_boleta as codigo_boleta_logistica
				, lg.estado::text as estado_logistica
				, par.param_nombre AS tipo_solicitud
				, inv.fecha_solicitud::text
				, pr1.param_nombre as estado
			    , s1.nombre as sol_origen
				, s2.nombre as sol_destino
				, pr2.param_nombre as tipo_producto
				, art.id as codigo_producto
				, art.nombre_producto as nombre_producto
                , invdet.cantidad as cantidad_solicitada
				, u1.name::text as usuario_registrado_solicitud
				, lg.cantidad as cantidad_asignado
				, u3.name::text as usuario_registrado_logistica

				, cot.nro_proceso_contrato::text as contrato
				, par_cot.param_nombre::text as tipo_contrato
				, UPPER(d.nombre::text) AS transportadora
			    , UPPER(veh.placa::text) AS placa_vehiculo
				, veh.marca::text AS marca_vehiculo
				, veh.type::text AS tipo_vehiculo
                , veh.color::text AS color_vehiculo
			    , UPPER(con.nombre_completo::text) AS conductor
				, con.numero_identificacion::text as conductor_identificacion

                , 'observacion solictud' as observacion_solicitud

				, mov.mv_nro_correlativo as nro_boleta_salida
                , suc.nombre AS alm_origen
                , mov.created_at::text AS fecha_sal
                , det.mvd_cantidad AS cant_sal
				, u2.name::text as usuario_registro_salida
				--'observacion salida',
				--'responsable salida',

                , mov.mv_datos->>'logistica_id' AS logistica_det
            FROM inventario.solicitud_inventarios inv
			left join inventario.solicitud_detalle_inventarios invdet on inv.id = invdet.solicitud_id  and invdet.estado= 'A'
			left join logistica.solicitudes_movimiento_logistica ml on ml.id_orden = inv.id
			left join logistica.solicitud_movimiento_detalle_logistica lg on lg.solicitud_id = inv.id
            LEFT JOIN inventario.movimiento_inventarios mov ON mov.mv_solicitud_id = inv.id and mov.mv_estado_id not in (11) and mov.mv_estado='A' and mov.mv_tipo = 'SALIDA' and EXTRACT(YEAR FROM mov.created_at::date) = 2024 and lg.id = (mov.mv_datos->>'logistica_id')::bigint
			LEFT JOIN inventario.movimiento_detalle_inventarios det ON mov.mv_id = det.mvd_mv_id and det.mvd_estado='A' AND det.mvd_articulo_id = invdet.articulo_id
            LEFT JOIN public.sucursals suc ON mov.mv_origen_id = suc.id --AND suc.id = almacen_id
            left JOIN acopio.parametricas as par ON inv.tipo_solicitud_id = par.param_valor AND par.param_tabla = 'TABLA_TIPO_SOLICITUD'
            LEFT JOIN insumos.articulos art ON invdet.articulo_id = art.id --AND art.linea_id = grano_id
			left join public.distribuidoras d on d.id = lg.distribuidora_id
			left join public.conductors con on con.id = lg.conductor_id
			left join public.transportes veh on veh.id = lg.vehiculo_id
			left join public.sucursals s1 on s1.id = inv.origen_id
			left join public.sucursals s2 on s2.id = inv.destino_id
			left join inventario.contratos cot on cot.id = ml.contrato_id
			left join acopio.parametricas as par_cot ON cot.tipo_contrato_id = par_cot.param_valor AND par_cot.param_tabla = 'TABLA_TIPO_CONTRATO' AND par.param_valor <> 0
            left join public.users u1 on u1.id = inv.usr_registrado
			left join public.users u2 on u2.id = mov.mv_usr_registrado
			left join public.users u3 on u3.id = lg.usr_registrado
			left JOIN acopio.parametricas as pr1 ON inv.estado_id = pr1.param_valor AND pr1.param_tabla = 'TABLA_TIPO_ESTADO'
			left JOIN acopio.parametricas as pr2 ON art.tipo_material_id = pr2.param_valor AND pr2.param_tabla = 'TABLA_TIPO_PRODUCTO_INSUMOS'
			where par.param_valor in (4,7,8) and par.param_estado='A'
        ) AS salida
        LEFT JOIN (
            SELECT
			    inv.id,
				mov.mv_nro_correlativo as nro_boleta_ingreso,
                suc2.nombre AS alm_destino,
                mov.created_at::text AS fecha_ing,
                det.mvd_cantidad AS cant_ing,
				u2.name::text as usuario_registro_ingreso,
                mov.mv_datos->>'logistica_id' AS logistica_det,
			    art.id as codigo_producto
            FROM inventario.solicitud_inventarios inv
			left join inventario.solicitud_detalle_inventarios invdet on inv.id = invdet.solicitud_id and invdet.estado= 'A'
			left join logistica.solicitud_movimiento_detalle_logistica lg on lg.solicitud_id = inv.id
            LEFT JOIN inventario.movimiento_inventarios mov ON mov.mv_solicitud_id = inv.id and mov.mv_estado_id not in (11) and mov.mv_estado='A'  AND mov.mv_tipo = 'INGRESO' and EXTRACT(YEAR FROM mov.created_at::date) = 2024 and lg.id = (mov.mv_datos->>'logistica_id')::bigint
			LEFT JOIN inventario.movimiento_detalle_inventarios det ON mov.mv_id = det.mvd_mv_id and det.mvd_estado='A'
            LEFT JOIN public.sucursals suc2 ON mov.mv_origen_id = suc2.id --AND suc2.id = almacen_id
            left JOIN acopio.parametricas as par ON inv.tipo_solicitud_id = par.param_valor AND par.param_tabla = 'TABLA_TIPO_SOLICITUD' AND par.param_valor <> 0 and par.param_estado='A'
            LEFT JOIN insumos.articulos art ON invdet.articulo_id = art.id --AND art.linea_id = grano_id
			left join public.distribuidoras d on d.id = lg.distribuidora_id
			left join public.conductors con on con.id = lg.conductor_id
			left join public.transportes veh on veh.id = lg.vehiculo_id
			left join public.users u2 on u2.id = mov.mv_usr_registrado

        ) AS ingreso ON salida.id = ingreso.id and salida.logistica_det = ingreso.logistica_det and salida.codigo_producto = ingreso.codigo_producto
        ORDER BY salida.fecha_sal ASC

    );
END;
$BODY$;

ALTER FUNCTION inventario.sp_reporte_movimiento_logistica_detallado(integer, integer, integer, integer)
    OWNER TO postgres;
