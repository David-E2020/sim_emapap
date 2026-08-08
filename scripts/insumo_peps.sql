 CREATE OR REPLACE FUNCTION sp_obtener_precio_unitario_peps(articulo_id INT, planta_id INT, cantidad_solicitada INT)
--RETURNS TABLE (mvd_articulo_id INT, mvd_cantidad INT, mvd_precio_unitario NUMERIC,mvd_fec_vencimiento timestamp) AS $$
RETURNS TABLE (
    o_articulo_id INT,
    o_cantidad INT,
    o_precio_unitario NUMERIC,
    o_fec_vencimiento TIMESTAMP
) AS $$
DECLARE
    saldo INT := 0;
	salidas INT := 0;
    unidades_salida INT := 0;
    precio_unitario_total NUMERIC := 0;
    unidades_restantes INT := cantidad_solicitada;
    movimiento_id INT;
    detalle_id INT;
    cantidad_ingreso INT;
    precio_unitario NUMERIC;
	cantidad_usable INT;
	articulo_idC  INT := 0;
        fec_vencimientoC TIMESTAMP;
        cantidadC  INT := 0;
        precio_unitarioC  NUMERIC := 0;
	ingresos record;
BEGIN
CREATE TABLE IF NOT EXISTS insumos.insumos_peps
(
	o_articulo_id INT,
    o_cantidad INT,
    o_precio_unitario NUMERIC,
    o_fec_vencimiento TIMESTAMP
    
);

  SELECT
        SUM(mvd_cantidad)
    INTO
        saldo
    FROM
        insumos.movimientos mv
    JOIN
        insumos.movimientos_detalles mvd ON mv.mv_id = mvd.mvd_mv_id
    WHERE
        EXTRACT(YEAR FROM mv.created_at) = EXTRACT(YEAR FROM CURRENT_DATE)
        AND mv.mv_planta_id = planta_id
        AND mv.mv_tipo_movimiento_id =  1 -- 1 es ingreso, 0 es salida
        AND mvd.mvd_articulo_id = articulo_id;
 
   SELECT
        SUM(mvd_cantidad)
    INTO
        salidas
    FROM
        insumos.movimientos mv
    JOIN
        insumos.movimientos_detalles mvd ON mv.mv_id = mvd.mvd_mv_id
    WHERE
        EXTRACT(YEAR FROM mv.created_at) = EXTRACT(YEAR FROM CURRENT_DATE)
        AND mv.mv_planta_id = planta_id
        AND mv.mv_tipo_movimiento_id =  0 -- 1 es ingreso, 0 es salida
        AND mvd.mvd_articulo_id = articulo_id;	
	
	   saldo =saldo - salidas;
       unidades_salida = salidas;
       --  $data = [];
	   precio_unitario_total = 0;
       unidades_restantes = cantidad_solicitada;

    -- Cursor para ingresos
    FOR ingresos IN  
      SELECT mvd_articulo_id , mvd_cantidad , mvd_precio_unitario ,mvd_fec_vencimiento 
        FROM insumos.movimientos mv
        JOIN insumos.movimientos_detalles mvd ON mv.mv_id = mvd.mvd_mv_id
        WHERE EXTRACT(YEAR FROM mv.created_at) = EXTRACT(YEAR FROM CURRENT_DATE)
        AND mv.mv_planta_id =  planta_id
        AND mv.mv_tipo_movimiento_id = 1 -- 1 es ingreso, 0 es salida
        AND mvd.mvd_articulo_id = articulo_id 	
    
    LOOP
        articulo_idC := ingresos.mvd_articulo_id;
        fec_vencimientoC := ingresos.mvd_fec_vencimiento;
        cantidadC := ingresos.mvd_cantidad;
        precio_unitarioC := ingresos.mvd_precio_unitario;

        if ( cantidad_ingreso >= unidades_salida) THEN
                    precio_unitario := precio_unitarioC;
                    cantidad_usable = cantidad_ingreso - unidades_salida;
                    if (cantidad_usable >= unidades_restantes) THEN
                        cantidadC = unidades_restantes;
                        INSERT INTO insumos.insumos_peps(
						o_articulo_id ,  o_cantidad , o_precio_unitario , o_fec_vencimiento )
						VALUES (articulo_idC,cantidadC,precio_unitarioC,fec_vencimientoC ); 
						
						return query select * from insumos.insumos_peps;
						drop table insumos.insumos_peps;				
                     ELSE
                        cantidadC = cantidad_usable;
                        unidades_restantes = unidades_restantes - cantidad_usable;
						INSERT INTO insumos.insumos_peps(
						o_articulo_id ,  o_cantidad , o_precio_unitario , o_fec_vencimiento )
						VALUES (articulo_idC,cantidadC,precio_unitarioC,fec_vencimientoC );                        
                        unidades_salida =unidades_salida + cantidad_solicitada;
                    END IF;
         ELSE
            -- Ingreso no cubre todas las unidades restantes
            unidades_salida := unidades_salida - cantidad_ingreso;
            --precio_unitario_total := precio_unitario_total + (cantidad_ingreso * precio_unitario);
         END IF;				
       
    END LOOP;

    IF saldo = 0 THEN
      return query select * from insumos.insumos_peps;
	  drop table insumos.insumos_peps;
    END IF;
END;
$$ LANGUAGE PLPGSQL;

	