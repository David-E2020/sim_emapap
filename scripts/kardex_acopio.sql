
-- FUNCTION: public.sp_calcular_kardex_valorado(integer, integer)

-- DROP FUNCTION IF EXISTS public.sp_calcular_kardex_valorado(integer, integer);

---SELECT * FROM acopio.sp_calcular_kardex_valorado(76, 85);

CREATE OR REPLACE FUNCTION acopio.sp_calcular_kardex_valorado(
	selling_point_id integer,
	product_id integer)
    RETURNS TABLE(o_fecha text, o_tipo text, o_codigo text, o_tipo_detalle text, o_entrada text, o_salida text, o_saldo text) 
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
CREATE TABLE IF NOT EXISTS acopio.temporal_kardex_insumos
(
    o_fecha text,
    o_tipo text,
	o_codigo text,
    o_tipo_det text,
    o_entrada text,
    o_salida text,
	o_saldo text
);
	saldo := 0;
	for kardex in select mv_tipo,mv_tipo_movimiento_id,mv_nro_correlativo,mvd_cantidad,m.created_at::date, param_nombre, s.aco_codigo as solicitud_id ,suc.nombre
			from acopio.movimientos as m
			inner join acopio.movimiento_detalles on mvd_mv_id = m.mv_id and mvd_estado = 'A'
			inner join acopio.acopio as s on s.aco_id = m.mv_acopio_id
			left join public.sucursals as suc on s.aco_silo_destino_id = suc.id
			left join acopio.parametricas on param_valor = m.mv_tipo_movimiento_id
			where mvd_articulo_id = product_id and m.mv_destino_id = selling_point_id
			and m.mv_estado_id not in (11)
			and param_tabla='TABLA_TIPO_MOVIMIENTO'
			and param_estado='A'
			order by m.created_at
			loop
				if(kardex.mv_tipo = 'INGRESO')then
					cantidad := (select split_part((kardex.mvd_cantidad)::text,$$.$$,1) ||$$.$$||substring(split_part((kardex.mvd_cantidad)::text,$$.$$,2),1,2));
					saldo := saldo + (kardex.mvd_cantidad)::numeric;
					--------------------------------------------------------
					saldo_2 := (select split_part((saldo)::text,$$.$$,1) ||$$.$$||substring(split_part((saldo)::text,$$.$$,2),1,2));
					saldo_3 := (saldo);
					if(kardex.solicitud_id is null)then
						INSERT INTO acopio.temporal_kardex_insumos(
						o_fecha, o_tipo, o_codigo, o_tipo_det, o_entrada, o_salida,o_saldo)
						VALUES (kardex.created_at, kardex.mv_tipo||' #'||'('||kardex.mv_nro_correlativo||')', '-',kardex.param_nombre, cantidad, '-',saldo_2);
					else
						INSERT INTO acopio.temporal_kardex_insumos(
						o_fecha, o_tipo, o_codigo, o_tipo_det, o_entrada, o_salida,o_saldo)
						VALUES (kardex.created_at, kardex.mv_tipo||' #'||'('||kardex.mv_nro_correlativo||')', kardex.solicitud_id ,kardex.param_nombre, cantidad, '-',saldo_2);						
					end if;
				else
					cantidad := (select split_part((kardex.mvd_cantidad)::text,$$.$$,1) ||$$.$$||substring(split_part((kardex.mvd_cantidad)::text,$$.$$,2),1,2));
					saldo := saldo - (kardex.mvd_cantidad)::numeric;
					--------------------------------------------------------
					saldo_2 := (select split_part((saldo)::text,$$.$$,1) ||$$.$$||substring(split_part((saldo)::text,$$.$$,2),1,2));
					saldo_3 := (saldo);
					if(kardex.solicitud_id is null)then
						INSERT INTO acopio.temporal_kardex_insumos(
						o_fecha, o_tipo, o_codigo, o_tipo_det, o_entrada, o_salida,o_saldo)
						VALUES (kardex.created_at, kardex.mv_tipo||' #'||'('||kardex.mv_nro_correlativo||')', '-',kardex.param_nombre,'-', cantidad, saldo_2);	
					else
						INSERT INTO acopio.temporal_kardex_insumos(
						o_fecha, o_tipo, o_codigo, o_tipo_det, o_entrada, o_salida,o_saldo)
						VALUES (kardex.created_at, kardex.mv_tipo||' #'||'('||kardex.mv_nro_correlativo||')', '--'||kardex.solicitud_id|| '--'||kardex.nombre, kardex.param_nombre,'-', cantidad, saldo_2);						
					end if;
				end if;
			end loop;
			return query select * from acopio.temporal_kardex_insumos;
			drop table acopio.temporal_kardex_insumos;
END;
$BODY$;



