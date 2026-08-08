-- FUNCTION: public.sp_calcular_kardex_valorado(integer, integer)

-- DROP FUNCTION IF EXISTS public.sp_calcular_kardex_valorado(integer, integer);

CREATE OR REPLACE FUNCTION insumos.sp_calcular_kardex_valorado(
	selling_point_id integer,
	product_id integer)
    RETURNS TABLE(o_fecha text, o_tipo text, o_tipo_detalle text, o_entrada text, o_costo_entrada text, o_total_entrada text, o_salida text, o_costo_salida text, o_total_salida text, o_saldo text, o_costo_saldo text, o_total_saldo text) 
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
	saldo_4 text;
	cantidad text;
	costo text;
	total text;
BEGIN
CREATE TABLE IF NOT EXISTS insumos.temporal_kardex_insumos
(
    o_fecha text,
    o_tipo text,
    o_tipo_det text,
    o_entrada text,
	o_costo_entrada text,
	o_total_entrada text,
    o_salida text,
	o_costo_salida text,
	o_total_salida text,
	o_saldo text,
	o_costo_saldo text,
	o_total_saldo text
);
	saldo := 0;
	for kardex in select mv_tipo,mv_tipo_movimiento_id,mvd_precio_unitario as mvd_precio_venta,mv_nro_correlativo,mvd_cantidad,m.created_at::date,param_nombre, s.id as solicitud_id,suc.nombre  
			from insumos.movimientos as m
			left join insumos.solicitudes_insumos as s on s.id = m.mv_solicitud_id
			left join public.sucursals as suc on s.planta_id_destino = suc.id
			inner join insumos.movimientos_detalles on mvd_mv_id = m.mv_id and mvd_estado = 'A'
			inner join acopio.parametricas on param_valor = m.mv_tipo_movimiento_id
			where mvd_articulo_id = product_id and m.mv_destino_id = selling_point_id
			and m.mv_estado_id not in (11)
			and param_tabla='TABLA_TIPO_MOVIMIENTO'
			and param_estado='A'
			order by m.created_at
			loop
				if(kardex.mv_tipo = 'INGRESO')then
					cantidad := (select split_part((kardex.mvd_cantidad)::text,$$.$$,1) ||$$.$$||substring(split_part((kardex.mvd_cantidad)::text,$$.$$,2),1,2));
					costo := (select split_part((kardex.mvd_precio_venta)::text,$$.$$,1) ||$$.$$||substring(split_part((kardex.mvd_precio_venta)::text,$$.$$,2),1,2));
					total := (kardex.mvd_cantidad::numeric * kardex.mvd_precio_venta::numeric)::text;
					total := (select split_part((total)::text,$$.$$,1) ||$$.$$||substring(split_part((total)::text,$$.$$,2),1,2));
					saldo := saldo + (kardex.mvd_cantidad)::numeric;
					--------------------------------------------------------
					saldo_2 := (select split_part((saldo)::text,$$.$$,1) ||$$.$$||substring(split_part((saldo)::text,$$.$$,2),1,2));
					saldo_3 := (saldo * kardex.mvd_precio_venta);
					saldo_4 := (select split_part((saldo_3)::text,$$.$$,1) ||$$.$$||substring(split_part((saldo_3)::text,$$.$$,2),1,2));
					if(kardex.solicitud_id is null)then
						INSERT INTO insumos.temporal_kardex_insumos(
						o_fecha, o_tipo, o_tipo_det, o_entrada,o_costo_entrada,o_total_entrada, o_salida,o_costo_salida,o_total_salida,o_saldo,o_costo_saldo,o_total_saldo)
						VALUES (kardex.created_at, kardex.mv_tipo||' #'||'('||kardex.mv_nro_correlativo||')',kardex.param_nombre, cantidad,costo,total, '-','-','-',saldo_2,costo,saldo_4);
					else
						INSERT INTO insumos.temporal_kardex_insumos(
						o_fecha, o_tipo, o_tipo_det, o_entrada,o_costo_entrada,o_total_entrada, o_salida,o_costo_salida,o_total_salida,o_saldo,o_costo_saldo,o_total_saldo)
						VALUES (kardex.created_at, kardex.mv_tipo||' #'||'('||kardex.mv_nro_correlativo||')'|| '--'||kardex.solicitud_id ,kardex.param_nombre, cantidad,costo,total, '-','-','-',saldo_2,costo,saldo_4);						
					end if;
				else
					cantidad := (select split_part((kardex.mvd_cantidad)::text,$$.$$,1) ||$$.$$||substring(split_part((kardex.mvd_cantidad)::text,$$.$$,2),1,2));
					costo := (select split_part((kardex.mvd_precio_venta)::text,$$.$$,1) ||$$.$$||substring(split_part((kardex.mvd_precio_venta)::text,$$.$$,2),1,2));
					total := (kardex.mvd_cantidad::numeric * kardex.mvd_precio_venta::numeric)::text;
					total := (select split_part((total)::text,$$.$$,1) ||$$.$$||substring(split_part((total)::text,$$.$$,2),1,2));
					saldo := saldo - (kardex.mvd_cantidad)::numeric;
					--------------------------------------------------------
					saldo_2 := (select split_part((saldo)::text,$$.$$,1) ||$$.$$||substring(split_part((saldo)::text,$$.$$,2),1,2));
					saldo_3 := (saldo * kardex.mvd_precio_venta);
					saldo_4 := (select split_part((saldo_3)::text,$$.$$,1) ||$$.$$||substring(split_part((saldo_3)::text,$$.$$,2),1,2));
					if(kardex.solicitud_id is null)then
						INSERT INTO insumos.temporal_kardex_insumos(
						o_fecha, o_tipo, o_tipo_det, o_entrada,o_costo_entrada,o_total_entrada, o_salida,o_costo_salida,o_total_salida,o_saldo,o_costo_saldo,o_total_saldo)
						VALUES (kardex.created_at, kardex.mv_tipo||' #'||'('||kardex.mv_nro_correlativo||')' ,kardex.param_nombre,'-','-','-', cantidad,costo,total, saldo_2,costo,saldo_4);	
					else
						INSERT INTO insumos.temporal_kardex_insumos(
						o_fecha, o_tipo, o_tipo_det, o_entrada,o_costo_entrada,o_total_entrada, o_salida,o_costo_salida,o_total_salida,o_saldo,o_costo_saldo,o_total_saldo)
						VALUES (kardex.created_at, kardex.mv_tipo||' #'||'('||kardex.mv_nro_correlativo||')'|| '--'||kardex.solicitud_id|| '--'||kardex.nombre,kardex.param_nombre,'-','-','-', cantidad,costo,total, saldo_2,costo,saldo_4);						
					end if;
				end if;
			end loop;
			return query select * from insumos.temporal_kardex_insumos;
			drop table insumos.temporal_kardex_insumos;
END;
$BODY$;

ALTER FUNCTION insumos.sp_calcular_kardex_valorado(integer, integer)
    OWNER TO postgres;
