<?php
require_once MODELS . '/Producto.php';
require_once INTERFACES . '/IApiUsable.php';

class ProductoController implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $titulo = $parametros['titulo'];
        $tiempoPreparacion = $parametros['tiempoPreparacion'];
        $precio = $parametros['precio'];
        $estado = $parametros['estado'];
        $sector = $parametros['sector'];

        // Creamos el producto
        $producto = new Producto();
        $producto->titulo = $titulo;
        $producto->tiempoPreparacion = (int)$tiempoPreparacion;
        $producto->precio = (float)$precio;
        $producto->estado = $estado;
        $producto->sector = $sector;

        $producto->crearProducto();

        $payload = json_encode(array("mensaje" => "Producto creado con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        $id = $args["producto"];
        $producto = Producto::obtenerProducto($id);
        if (!$producto) {
            $ret = new stdClass();
            $ret->err = "no se encontro el producto con el id solicitado";
            $err_payload = json_encode($ret);
            $response->getBody()->write($err_payload);
        } else {
            $payload = json_encode($producto);
            $response->getBody()->write($payload);
        }

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Producto::obtenerTodos();
        $payload = json_encode(array("listaProducto" => $lista));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function ModificarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $id = $args['producto'];
        $titulo = $parametros['titulo'];
        $tiempoPreparacion = (int)$parametros['tiempoPreparacion'];
        $precio = (float)$parametros['precio'];
        $estado = $parametros['estado'];
        $sector = $parametros['sector'];

        $producto = Producto::obtenerProducto($id);

        $producto->titulo = $titulo;
        $producto->tiempoPreparacion = $tiempoPreparacion;
        $producto->precio = $precio;
        $producto->sector = $sector;
        $producto->estado = $estado;

        Producto::modificarProducto($producto);

        $payload = json_encode(array("mensaje" => "Producto modificado con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $id = $args['producto'];
        $producto = Producto::obtenerProducto($id);
        Producto::borrarProducto($producto);

        $payload = json_encode(array("mensaje" => "Producto borrado con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function CargaMasiva($request, $response, $args)
    {
        $huboErroresDuplicados = false;
        $archivosSubidos = $request->getUploadedFiles();
        $archivoProductos = $archivosSubidos["productos"];
        if($archivoProductos->getClientFilename() === "" || $archivoProductos->getSize() <= 0 || $archivoProductos->getClientMediaType() != "text/csv"){
            throw new Exception("Request no contiene archivo csv con un formato correcto");            
        }
        $parametros = $request->getParsedBody();
        $omitirRepetidos = $parametros['omitirRepetidos'];
        $omitirRepetidos = isset($omitirRepetidos) ? $omitirRepetidos : false;
        // Verificar si se cargo correctamente el archivo
        if ($archivoProductos->getError() === UPLOAD_ERR_OK) {
            $csvContent = $archivoProductos->getStream()->getContents();

            // Convertir el contenido del CSV en un array de filas
            $filas = explode(PHP_EOL, $csvContent);
            $primerFila = true;
            foreach ($filas as $fila) {
                // Omitir la primera fila (encabezado)
                if ($primerFila) {
                    $primerFila = false;
                    continue;
                }
                $datos = str_getcsv($fila);

                // Formato igual al de la tabla de la base de datos
                $obj = (object)[
                    'id_producto' => $datos[0],
                    'titulo' => $datos[1],
                    'tiempo_preparacion' => $datos[2],
                    'precio' => $datos[3],
                    'estado' => $datos[4],
                    'sector' => $datos[5],
                    'fecha_creacion' => $datos[6]
                ];
                try {
                    $producto = Producto::transformarPrototipo($obj);
                    $producto->crearProducto();
                } catch (\PDOException $th) {
                    // SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry
                    if($th->getCode() == 23000 && $omitirRepetidos){
                        $huboErroresDuplicados = true;
                        continue;
                    }
                    else{
                        $payload = json_encode(array("mensaje" => "Error al procesar CSV: Regitros duplicados en destino"));
                        $response->getBody()->write($payload);
                        return $response->withHeader('Content-Type', 'application/json');
                    }
                }
                
            }
            $mensaje = "CSV procesado ";
            if($huboErroresDuplicados){
                $mensaje = $mensaje . "incompleto con errores por registros duplicados en destino";
            }
            else{
                $mensaje = $mensaje . " correctamente";
            }
            $payload = json_encode(array("mensaje" => $mensaje));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            $payload = json_encode(array("mensaje" => "CSV procesado incorrectamete"));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        }
    }
    public function DescargaMasiva($request, $response, $args)
    {
        $productos = Producto::obtenerTodos();

        $csvFile = tmpfile();
        $header = ["id_producto", "titulo", "tiempo_preparacion", "precio", "estado", "sector", "fecha_creacion"];
        fputcsv($csvFile, $header);

        foreach ($productos as $producto) {
            $data = [
                $producto->idProducto,
                $producto->titulo,
                $producto->tiempoPreparacion,
                $producto->precio,
                $producto->estado,
                $producto->sector,
                $producto->fechaCreacion->format('Y-m-d H:i:s')
            ];
            fputcsv($csvFile, $data);
        }

        // Establecer las cabeceras para descargar el archivo
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="productos_exportados.csv"');

        // Rebobinar el puntero del archivo al principio antes de leerlo
        rewind($csvFile);

        // Leer y devolver el contenido del archivo CSV
        $csvContent = stream_get_contents($csvFile);
        fclose($csvFile);

        $response->getBody()->write($csvContent);
        return $response->withHeader('Content-Type', 'application/csv');
    }
}
