<?php
require_once MODELS . "/Usuario.php";
class AuthController
{
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
                $datos = array('usuario' => $usuario);
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
