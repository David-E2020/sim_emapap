-- FUNCTION: cartera.obtener_datos_productor_pago(text)

-- DROP FUNCTION IF EXISTS cartera.obtener_datos_productor_pago(text);

CREATE OR REPLACE FUNCTION cartera.obtener_datos_productor_pago(
	ci_busqueda text)
    RETURNS TABLE(reg_final_id bigint, region_id integer, regi_nombre text, programa_id integer, prog_nombre text, campania_id integer, camp_nombre text, reprfin_cod_seguimiento text, prod_nombres text, prod_paterno text, prod_materno text, nombre_productor text, prod_nro_documento_identidad text, prod_exp text, depa_nombre text, provi_nombre text, muni_nombre text, comu_nombre text, asoc_nombre_organizacion text, tiap_nombre text, tiap_tipo_prod text, superficieacopio numeric, rendimientoacopio numeric, cupo numeric, saldocupo numeric, estado_acopio text, peso_acopio numeric, deupro_importe_deuda numeric, observacion text, credito_id text, has numeric, total_colocado numeric, total_subvencion numeric, "tamaño_productor" smallint, rau_asignado numeric, rau_total numeric, rau_saldo numeric, tipo_apoyo_id integer) 
    LANGUAGE 'plpgsql'
    COST 100
    VOLATILE PARALLEL UNSAFE
    ROWS 1000

AS $BODY$
BEGIN
 -- SELECT * FROM cartera.obtener_datos_productor_pago('6270022');
    RETURN QUERY
    SELECT 
		rp.reprde_final_id::bigint as reg_final_id,
		reg.regional_id as region_id,
        reg.regi_nombre::text, 
		prog.programa_id, 
        prog.prog_nombre::text, 
		camp.campania_id, 
        camp.camp_nombre::text,
        rp.reprfin_cod_seguimiento::text,
        prod.prod_nombres::text, 
        prod.prod_paterno::text, 
        prod.prod_materno::text, 
		CONCAT(prod.prod_nombres, ' ', prod.prod_paterno, ' ', prod.prod_materno )::text as nombre_productor , 
        prod.prod_nro_documento_identidad::text, 
        prod.prod_exp::text, 
        d.depa_nombre::text, 
        pro.provi_nombre::text, 
        mu.muni_nombre::text, 
        co.comu_nombre::text,
        aso.asoc_nombre_organizacion::text as nombre_organizacion, 
        ta.tiap_nombre::text,
        ta.tiap_tipo_prod::text,
        rp.superficieacopio as has_acopio,
        rp.rendimientoacopio, 
        round(rp.superficieacopio * rp.rendimientoacopio, 3) as cupo,
        rp.saldocupo,
        (CASE 
            WHEN rp.ESTADOACOPIO_ID=1 THEN 'NO HABILITADO'  
            WHEN rp.ESTADOACOPIO_ID=2 THEN 'HABILITADO'  
            WHEN rp.ESTADOACOPIO_ID=3 THEN 'OBSERVADO' 
            WHEN RP.ESTADOACOPIO_ID=4 THEN 'BLOQUEADO' 
            WHEN RP.ESTADOACOPIO_ID=5 THEN 'CERRADO' 
            ELSE 'SIN ESTADO'
        END)::text AS estado_ACOPIO,
        COALESCE(ACO.PESO_ACOPIO, 0) AS PESO_ACOPIO,
        dp.deupro_importe_deuda,
        rp.reprdet_final_observacion::text as observacion,
		rp.mgr_credito_id::text as credito_id,
		(rp.has_propio + rp.has_otros + rp.superficieejecutada) as has,
		dep.deupro_credito as total_colocado,
		dep.deupro_subvencion as total_subvencion,
		rp.reprdet_final_prod_tamanio as tamaño_productor,
		rau.rau_asigna_saldo as rau_asignado,
		(rau.rau_asigna_asg_exthas * rau.rau_asigna_rendimiento) as rau_total,
		rau.rau_asigna_saldo as rau_saldo,
		ta.tipo_apoyo_id
    FROM 
        siemc.registro_asociacion ra
    INNER JOIN 
        siemc.programa prog ON prog.programa_id = ra.programa_id
    INNER JOIN 
        siemc.regional reg ON reg.regional_id = ra.regprodcab_regional_id
    INNER JOIN 
        siemc.campania camp ON camp.campania_id = ra.campania_id
    INNER JOIN 
        siemc.asociacion aso ON aso.asociacion_id = ra.asociacion_id
    INNER JOIN 
        siemc.registro_productor_final rp ON rp.reg_productor_id = ra.reg_productor_id
    LEFT JOIN 
        siemc.deuda_productor dp ON rp.reprde_final_id = dp.reprde_final_id 
    INNER JOIN 
        siemc.productor prod ON rp.productor_id = prod.productor_id
    INNER JOIN 
        siemc.departamento d ON rp.reprdefin_departamento_id = d.departamento_id
    INNER JOIN 
        siemc.provincia pro ON rp.reprdefin_provincia_id = pro.provincia_id
    INNER JOIN 
        siemc.municipio mu ON rp.reprdefin_municipio_id = mu.municipio_id
    INNER JOIN 
        siemc.comunidad co ON rp.reprdefin_comunidad_id = co.comunidad_id
    INNER JOIN 
        siemc.tipo_apoyo ta ON rp.regproddetfin_tipo_apoyo_id = ta.tipo_apoyo_id
    LEFT JOIN 
        (SELECT 
            reprde_final_id, 
            SUM(aco_peso_liquido_acopio) as peso_acopio 
        FROM 
            acopio.acopio 
        WHERE  
            estado_id IN (3, 10, 4, 5, 12) 
        GROUP BY 
            reprde_final_id) aco 
    ON 
        rp.reprde_final_id = aco.reprde_final_id
	left join siemc.deuda_productor  dep on dep.reprde_final_id= rp.reprde_final_id
	left join siemc.rau_asigna rau on rau.reprde_final_id=rp.reprde_final_id
    WHERE 
        prod.prod_nro_documento_identidad = ci_busqueda;
END;
$BODY$;

ALTER FUNCTION cartera.obtener_datos_productor_pago(text)
    OWNER TO postgres;
