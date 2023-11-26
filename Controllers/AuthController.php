<?php
require_once MODELS . "/Usuario.php";
require_once UTILS . "/AutentificadorJWT.php";
class AuthController
{
    public function SignUp($request, $response, $args){
        try {
            $parametros = $request->getParsedBody();
    
            $nombre = $parametros['nombre'];
            $user = $parametros['usuario'];
            $password = $parametros['contraseña'];
    
            $usr = new Usuario();
            $usr->nombre = $nombre;
            $usr->user = $user;
            $usr->sector = Usuario::SECTOR_CLIENTE;
            $usr->tipo = Usuario::TIPO_CLIENTE;
            $usr->password = $password;
    
            $existeNombreUsuario = Usuario::obtenerUsuarioByName($usr->nombre);
            if($existeNombreUsuario) {
                throw new Exception("Ya existe el nombre de usuario suministrado, elija otro");
            }
            $existeUsername = Usuario::obtenerUsuarioByUsername($user);
            if($existeUsername) {
                throw new Exception("Ya existe el username suministrado, elija otro");
            }
            $usr->crearUsuario();
    
            $payload = json_encode(array("mensaje" => "Usuario creado con exito"));
    
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Throwable $th) {
            $payload = json_encode(array("error" => $th->getMessage()));    
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        }
    }
    public function GenerarToken($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $usuario = $parametros['usuario'];
        $contraseña = $parametros['contraseña'];
        $usuarioExiste = Usuario::obtenerUsuarioByUsername($usuario);
        $payload = json_encode(array('error' => 'Usuario o contraseña incorrectos'));
        if ($usuarioExiste) {
            $contraseñaCorrecta = password_verify($contraseña, $usuarioExiste->password);
            if ($contraseñaCorrecta) {
                $datos = array(
                    'usuario' => $usuarioExiste->user,
                    'tipo' => $usuarioExiste->tipo,
                    'sector' => $usuarioExiste->sector
                );
                $token = AutentificadorJWT::CrearToken($datos);
                $payload = json_encode(array('jwt' => $token));
            }
        }
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function VerificarToken($request, $response, $args)
    {
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);
        $esValido = false;

        try {
            AutentificadorJWT::verificarToken($token);
            $esValido = true;
        } catch (Exception $e) {
            $payload = json_encode(array('error' => $e->getMessage()));
        }

        if ($esValido) {
            $payload = json_encode(array('valid' => $esValido));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
