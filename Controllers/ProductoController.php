<?php
require_once MODELS . '/Producto.php';
require_once INTERFACES . '/IApiUsable.php';

class ProductoController extends Producto implements IApiUsable
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
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        // Buscamos producto por nombre
        // $queryParams = $request->getQueryParams();
        // $id = $queryParams['idProducto'];
        $id = $args["producto"];
        $producto = Producto::obtenerProducto($id);
        if(!$producto){
            $ret = new stdClass();
            $ret->err = "no se encontro el producto con el id solicitado";
            $err_payload = json_encode($ret);
            $response->getBody()->write($err_payload);
        }
        else{
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
        return $response
          ->withHeader('Content-Type', 'application/json');
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
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        // $parametros = $request->getParsedBody();
        $id = $args['producto'];
        $producto = Producto::obtenerProducto($id);
        Producto::borrarProducto($producto);

        $payload = json_encode(array("mensaje" => "Producto borrado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
}

?>