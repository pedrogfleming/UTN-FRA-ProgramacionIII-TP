<?php
require_once MODELS . "/Usuario.php";
require_once INTERFACES . '/IApiUsable.php';

class UsuarioController extends Usuario implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $nombre = $parametros['nombre'];
        $user = $parametros['user'];
        $sector = $parametros['sector'];
        $tipo = $parametros['tipo'];
        $password = $parametros['password'];

        $usr = new Usuario();
        $usr->nombre = $nombre;
        $usr->user = $user;
        $usr->sector = $sector;
        $usr->tipo = $tipo;
        $usr->password = $password;

        $usr->crearUsuario();

        $payload = json_encode(array("mensaje" => "Usuario creado con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        $id = $args["usuario"];
        $usuario = Usuario::obtenerUsuario($id);
        if(!$usuario){
            $ret = new stdClass();
            $ret->err = "no se encontro el usuario con el id solicitado";
            $err_payload = json_encode($ret);
            $response->getBody()->write($err_payload);
        }
        else{
            $payload = json_encode($usuario);
            $response->getBody()->write($payload);
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Usuario::obtenerTodos();
        $payload = json_encode(array("listaUsuario" => $lista));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function ModificarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $id = $args['usuario'];
        $nombre = $parametros['nombre'];
        $user = $parametros['user'];
        $sector = $parametros['sector'];
        $tipo = $parametros['tipo'];
        $password = $parametros['password'];

        $usuario = Usuario::obtenerUsuario($id);

        $usuario->nombre = $nombre;
        $usuario->user = $user;
        $usuario->password = $password;
        $usuario->sector = $sector;
        $usuario->tipo = $tipo;

        Usuario::modificarUsuario($usuario);

        $payload = json_encode(array("mensaje" => "Usuario modificado con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $id = $args['usuario'];
        $usuario = Usuario::obtenerUsuario($id);
        Usuario::borrarUsuario($usuario);

        $payload = json_encode(array("mensaje" => "Usuario borrado con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
