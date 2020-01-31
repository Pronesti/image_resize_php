<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Source\TemplateEngine as TE;
use Source\BaseDeDatos as BD;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

if (PHP_SAPI == 'cli-server') {
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) return false;
}

$app->get('/', function (Request $request, Response $response, array $args) {
    $te = new TE("../template/fullpage.template");
    $te->addVariable("title", "Resize");
    $form = new TE("../template/form.template");
    $listaDeImagenes = "";
    $bd = new BD("../bd/imagenes.data");
    foreach($bd->read() as $k => $img){
        if($k == 0){

        }else{
            $newImg = new TE("../template/img.template");
            $newImg->addVariable("urlName", $img);
            $listaDeImagenes .= $newImg->render();
        }
        
    }
    $form->addVariable("imagenes", $listaDeImagenes);
    $te->addVariable("content", $form->render());
    $response->getBody()->write($te->render());
    return $response;
});

$app->post('/resize', function (Request $request, Response $response, array $args) {
    $imgUrl = $_POST['url'];
    // Fichero y nuevo tamaño
    $porcentaje = $_POST['percent'];

    // Tipo de contenido
    header('Content-Type: image/jpeg');

    // Obtener los nuevos tamaños
    list($ancho, $alto) = getimagesize($imgUrl);
    $nuevo_ancho = $ancho * $porcentaje;
    $nuevo_alto = $alto * $porcentaje;

    // Cargar
    $thumb = imagecreatetruecolor($nuevo_ancho, $nuevo_alto);
    $origen = imagecreatefromjpeg($imgUrl);

    // Cambiar el tamaño
    imagecopyresized($thumb, $origen, 0, 0, 0, 0, $nuevo_ancho, $nuevo_alto, $ancho, $alto);
    // Imprimir
    $nombre = time() . '_' . pathinfo($imgUrl)['filename'];
    $nombreOriginal = $nombre . '_original';
    $bd = new BD("../bd/imagenes.data");
    $listaDeImagenes = $bd->read();
    $listaDeImagenes[]=$nombre;
    $bd->save($listaDeImagenes);
    imagejpeg($thumb, 'img/'. $nombre .'.jpg', 100);
    imagejpeg($origen, 'img/'. $nombreOriginal .'.jpg', 100);
    //$response->getBody()->write(imagejpeg($thumb));
    //return $response->withHeader('Content-type', FILEINFO_MIME_TYPE);//->withStatus(302)->withHeader("Location", 'show/');
    return $response->withStatus(302)->withHeader("Location", 'show/' . $nombre);
});

$app->get('/show/{imgName}', function (Request $request, Response $response, array $args) {
    $te = new TE("../template/fullpage.template");
    $te->addVariable("title", "Show image");
    $show = new TE("../template/show.template");
    $show->addVariable("url", $args['imgName']);
    $te->addVariable("content",$show->render());
    $response->getBody()->write($te->render());
    return $response;
});

$app->run();
