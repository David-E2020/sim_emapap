-- FUNCTION: cartera.obtener_boletas_acopio(integer)

-- DROP FUNCTION IF EXISTS cartera.obtener_boletas_acopio(integer);

CREATE OR REPLACE FUNCTION cartera.obtener_boletas_acopio(
	xid integer)
    RETURNS TABLE(xreprde_final_id integer, aco_id bigint, aco_codigo text, reprde_final_id integer, aco_nro_correlativo integer, planta text, aco_peso_liquido_acopio numeric, peso_pagado numeric, valor_total numeric, deuda numeric, amortizacion_deuda numeric, pago_productor numeric, deduccion numeric, impuestos numeric) 
    LANGUAGE 'plpgsql'
    COST 100
    VOLATILE PARALLEL UNSAFE
    ROWS 1000

AS $BODY$
BEGIN
 -- SELECT * FROM cartera.obtener_boletas_acopio(1053);
    RETURN QUERY
    
	SELECT
	acopio.reprde_final_id, acopio.aco_id, acopio.aco_codigo,acopio.reprde_final_id,acopio.aco_nro_correlativo,plantas.nombre::text as planta,acopio.aco_peso_liquido_acopio,sum(valorizaciones.val_peso) as peso_pagado,
	sum(valorizaciones.val_total_bs) as valor_total, sum(valorizaciones.val_deuda_bs) as deuda,
	sum( CASE operaciones.ope_operacion	WHEN 'AMORTIZACION' THEN operaciones.ope_monto_bs ELSE 0 END ) as amtzt_deuda,
	sum(operaciones.ope_monto_bs ) as Pago_productor,
	sum( CASE operaciones.ope_operacion	WHEN 'DEDUCCION' THEN operaciones.ope_monto_bs ELSE 0 END ) as deduccion,
	sum(valorizaciones.val_impuesto_bs ) as impuestos
FROM
	acopio.acopio 
	INNER JOIN 
	acopio.plantas ON acopio.plantas.id = acopio.acopio.aco_planta_origen_id
	left join 
	cartera.valorizaciones on cartera.valorizaciones.aco_id = acopio.acopio.aco_id
	left JOIN
	cartera.operaciones on cartera.valorizaciones.valorizaciones_id  =  cartera.operaciones.valorizaciones_id

WHERE acopio.reprde_final_id = xid
GROUP BY
acopio.reprde_final_id, acopio.aco_id, acopio.aco_codigo,acopio.reprde_final_id,acopio.aco_nro_correlativo,plantas.nombre,acopio.aco_peso_liquido_acopio;

	
	
END;
$BODY$;

ALTER FUNCTION cartera.obtener_boletas_acopio(integer)
    OWNER TO postgres;
