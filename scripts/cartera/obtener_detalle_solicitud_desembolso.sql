-- FUNCTION: cartera.obtener_detalle_solicitud_desembolso(integer, integer, integer, integer)

-- DROP FUNCTION IF EXISTS cartera.obtener_detalle_solicitud_desembolso(integer, integer, integer, integer);

CREATE OR REPLACE FUNCTION cartera.obtener_detalle_solicitud_desembolso(
	xsolic_desem_id integer,
	xprograma_id integer,
	xcampania_id integer,
	xregional_id integer)
    RETURNS TABLE(solic_desem_id integer, organizacion text, beneficiario text, banco text, nro_cuenta text, monto_bs numeric, tipo_solicitud text, nro_cheque text, fecha_cheque date, deposito_bs numeric, fecha_deposito date, observacion text) 
    LANGUAGE 'plpgsql'
    COST 100
    VOLATILE PARALLEL UNSAFE
    ROWS 1000

AS $BODY$
BEGIN
 -- SELECT * FROM cartera.obtener_detalle_solicitud_desembolso('6270022');
    RETURN QUERY
    SELECT
	cartera.solicitud_desembolso.solic_desem_id, siemc.asociacion.asoc_nombre_organizacion::text as organizacion,
	CONCAT(siemc.productor.prod_nombres, ' ', siemc.productor.prod_paterno, ' ', siemc.productor.prod_materno )  as beneficiario
	, siemc.tbanco.tbanco_banco::text as banco, siemc.fbanco.fbanco_nro_cuenta::text as nro_cuenta
	, cartera.solicdesembolsodetalle.solicdesemdetalle_montotrans as monto_bs
	, cartera.solicdesembolsodetalle.solicdesemdetalle_tiposolic::text as tipo_solicitud
	, cartera.solicdesembolsodetalle.solicdesemdetalle_cheq_prev::text as nro_cheque
	, cartera.solicdesembolsodetalle.solicdesemdetalle_fcheqprev as fecha_cheque
	, cartera.solicdesembolsodetalle.solicdesemdetalle_montotrans as deposito_bs
	, cartera.solicdesembolsodetalle.solicdesemdetalle_fechadesem as fecha_deposito			
	, cartera.solicdesembolsodetalle.solicdesemdetalle_observa ::text as observacion			
    FROM cartera.solicitud_desembolso 
    inner join cartera.solicdesembolsodetalle on cartera.solicitud_desembolso.solic_desem_id = cartera.solicdesembolsodetalle.solic_desem_id
	left join cartera.operaciones on cartera.operaciones.operacion_id = cartera.solicdesembolsodetalle.operacion_id
	LEFT JOIN cartera.impuestos ON cartera.operaciones.operacion_id = cartera.impuestos.operacion_id
	INNER JOIN cartera.valorizaciones ON cartera.operaciones.valorizaciones_id = cartera.valorizaciones.valorizaciones_id
	INNER JOIN acopio.acopio ON acopio.acopio.aco_id = cartera.valorizaciones.aco_id
	INNER JOIN siemc.registro_productor_final ON siemc.registro_productor_final.reprde_final_id = acopio.acopio.reprde_final_id
	INNER JOIN siemc.registro_asociacion ON siemc.registro_productor_final.reg_productor_id = siemc.registro_asociacion.reg_productor_id
	INNER JOIN siemc.productor ON siemc.registro_productor_final.productor_id = siemc.productor.productor_id
	INNER JOIN siemc.asociacion ON siemc.registro_asociacion.asociacion_id = siemc.asociacion.asociacion_id
	left join siemc.fbanco on siemc.fbanco.fbanco_id = cartera.operaciones.ope_fbanco_id
	left join siemc.tbanco on siemc.tbanco.tbanco_id = siemc.fbanco.fbanco_id_tipo_cuenta_banco
	WHERE siemc.registro_asociacion.programa_id = xprograma_id
	AND siemc.registro_asociacion.campania_id=xcampania_id
	AND  siemc.registro_asociacion.regprodcab_regional_id =xregional_id
	and cartera.solicitud_desembolso.solic_desem_id = xsolic_desem_id;
END;
$BODY$;

ALTER FUNCTION cartera.obtener_detalle_solicitud_desembolso(integer, integer, integer, integer)
    OWNER TO postgres;
