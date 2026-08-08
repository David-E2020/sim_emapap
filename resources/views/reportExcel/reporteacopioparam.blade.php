
<html>

<table >
    @php 
    if (count($acopio) <=0)
    {
       echo '<tr><td>Datos no encontrados</td></tr><tr><td>Busqueda no tiene resultados</td></tr>'; 
    }

    $cuerpo ='';
    $head ='';
    $i=1;
        foreach ($acopio as $index => $item)
        { $cuerpo=$cuerpo.'<tr>';
               foreach($item as $key => $dato)
                {if ($i == 1)
                   { $head=$head.'<th>'.strtoupper($key).'</th>' ;
                   }
                 $cuerpo=$cuerpo.'<td>'.$dato.'</td>';                
                }
          $i++;
          $cuerpo=$cuerpo.'</tr>';
        }
    $cuerpo ='<tbody>'.$cuerpo.'</tbody>';
    $head ='<thead><tr>'.$head.'</tr></thead >';
    echo $head.$cuerpo;
    @endphp  
</table>
</html>