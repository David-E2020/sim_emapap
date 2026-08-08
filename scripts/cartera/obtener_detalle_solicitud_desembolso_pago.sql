-- FUNCTION: cartera.obtener_detalle_solicitud_desembolso_pago(integer, integer, integer, integer)

-- DROP FUNCTION IF EXISTS cartera.obtener_detalle_solicitud_desembolso_pago(integer, integer, integer, integer);

CREATE OR REPLACE FUNCTION cartera.obtener_detalle_solicitud_desembolso_pago(
	xnro_pago integer,
	xprograma_id integer,
	xcampania_id integer,
	xregional_id integer)
    RETURNS TABLE(operacion_id integer, asociacion_id integer, organizacion text, campania_id integer, campania_nombre text, programa_id integer, programa_nombre text, departamento_id integer, departamento_nombre text, beneficiario text, ope_clasf_num integer, fecha_solicitud date, monto_solicitado numeric, monto_desembolso numeric, banco text, nro_cuenta text) 
    LANGUAGE 'plpgsql'
    COST 100
    VOLATILE PARALLEL UNSAFE
    ROWS 1000

AS $BODY$
BEGIN
 -- SELECT * FROM cartera.obtener_detalle_solicitud_desembolso_pago('6270022');
    RETURN QUERY
    SELECT
	cartera.operaciones.operacion_id as operacion_id
	, siemc.asociacion.asociacion_id as asociacion_id
	, siemc.asociacion.asoc_nombre_organizacion::text as organizacion
	, acopio.acopio.aco_camp_id as campania_id
	, siemc.campania.camp_nombre::text as campania_nombre
	, acopio.acopio.aco_programa_id as programa_id
	, siemc.programa.prog_nombre::text as programa_nombre
	, d.departamento_id as departamento_id
	, d.depa_nombre::text as departamento_nombre
	, CONCAT(siemc.productor.prod_nombres, ' ', siemc.productor.prod_paterno, ' ', siemc.productor.prod_materno )  as beneficiario
	, cartera.operaciones.ope_clasf_num as ope_clasf_num
	, now()::date as solic_desem_fechasolic
	, cartera.operaciones.ope_monto_bs as monto_solicitado
	, cartera.operaciones.ope_monto_bs as monto_depositado
	, siemc.tbanco.tbanco_banco::text as banco, siemc.fbanco.fbanco_nro_cuenta::text as nro_cuenta
    FROM cartera.operaciones 
	LEFT JOIN cartera.impuestos ON cartera.operaciones.operacion_id = cartera.impuestos.operacion_id
	INNER JOIN cartera.valorizaciones ON cartera.operaciones.valorizaciones_id = cartera.valorizaciones.valorizaciones_id
	INNER JOIN acopio.acopio ON acopio.acopio.aco_id = cartera.valorizaciones.aco_id
	INNER JOIN siemc.registro_productor_final ON siemc.registro_productor_final.reprde_final_id = acopio.acopio.reprde_final_id
	INNER JOIN siemc.registro_asociacion ON siemc.registro_productor_final.reg_productor_id = siemc.registro_asociacion.reg_productor_id
	INNER JOIN siemc.productor ON siemc.registro_productor_final.productor_id = siemc.productor.productor_id
	INNER JOIN siemc.asociacion ON siemc.registro_asociacion.asociacion_id = siemc.asociacion.asociacion_id
	left join siemc.fbanco on siemc.fbanco.fbanco_id = cartera.operaciones.ope_fbanco_id
	left join siemc.tbanco on siemc.tbanco.tbanco_id = siemc.fbanco.fbanco_id_tipo_cuenta_banco
	left JOIN siemc.departamento d ON siemc.registro_productor_final.reprdefin_departamento_id = d.departamento_id
	left join siemc.campania on siemc.campania.campania_id = acopio.acopio.aco_camp_id
	left join siemc.programa on siemc.programa.programa_id = acopio.acopio.aco_programa_id
	WHERE siemc.registro_asociacion.programa_id = xprograma_id
	AND siemc.registro_asociacion.campania_id=xcampania_id
	AND  siemc.registro_asociacion.regprodcab_regional_id =xregional_id
	and CARTERA.operaciones.ope_clasf_num = xnro_pago;
END;
$BODY$;

ALTER FUNCTION cartera.obtener_detalle_solicitud_desembolso_pago(integer, integer, integer, integer)
    OWNER TO postgres;
