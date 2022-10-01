<?php
if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

require __DIR__ . '/../vendor/autoload.php';

session_start();

// Instantiate the app
$settings = require __DIR__ . '/../src/settings.php';
$app = new \Slim\App($settings);

// Set up dependencies
require __DIR__ . '/../src/dependencies.php';

// Register middleware
require __DIR__ . '/../src/middleware.php';

// Register routes
require __DIR__ . '/../src/routes.php';

// API group
$app->group('/v1', function () use ($app) {


    $app->post('/crear', function ($request, $response, $args) {
        $item = $request->getParsedBody();
        $json = file_get_contents('items.json');
        if (!(is_null($json))) {
            $items = json_decode($json, true);
            $item = array(
                "title" => $item['title'],
                "description" => $item['description'],
                "link" => $item['link'],
                "id" => count($items) + 1
            );


            array_push($items, $item);
            $json = json_encode($items);
            file_put_contents('items.json', $json);
        } else {
            $item = array("Error" => "No existe json");
        }


        return $this->response->withJson($item);
    });

    $app->get('/obtener/{id}', function ($request, $response, $args) {
        $json = file_get_contents('items.json');
        if (!(is_null($json))) {
            $items = json_decode($json, true);
            foreach ($items as $itemb) {
                if ($itemb['id'] == $args['id']) {
                    $item = $itemb;
                }
            }
        } else {
            $item = array("Error" => "No existe json");
        }
        return $this->response->withJson($item);
    });

    $app->put('/editar/{id}', function ($request, $response, $args) {
        $itemn = $request->getParsedBody();
        $json = file_get_contents('items.json');
        if (!(is_null($json))) {
            $items = json_decode($json, true);
            foreach ($items as &$item) {
                if ($item['id'] == $args['id']) {
                    $item['tittle'] = $itemn['title'];
                    $item['description'] = $itemn['description'];
                    $item['link'] = $itemn['link'];
                }
            }
            $json = json_encode($items);
            file_put_contents('items.json', $json);
        } else {
            $item = array("Error" => "No existe json");
        }
        return $this->response->withJson($item);
    });

    $app->delete('/eliminar/{id}', function ($request, $response, $args) {
        $json = file_get_contents('items.json');
        if (!(is_null($json))) {
            $itemsold = json_decode($json, true);
            foreach ($itemsold as $item) {
                if ($item['id'] != $args['id']) {
                    if (!(is_null($item))) {
                        array_push($items, $item);
                    }
                }
            }
            $json = json_encode($items);
            file_put_contents('items.json', $json);
        } else {
            $item = array("Error" => "No existe json");
        }
        return $this->response->withJson($item);
    });




    $app->get('/base', function ($request, $response, $args) {
        $url = "https://www.ecuavisa.com/rss/portada.json";
        $json = url_get_contents($url);
        $items = json_decode($json, true);
        if (!(is_null($items))) {
            $items = $items['rss']['channel']['item'];
            $i = 1;
            foreach ($items as &$item) {
                $item['id'] = $i;
                $i++;
            }
            $json = json_encode($items);
            file_put_contents('items.json', $json);
            foreach ($items as &$item) {
                unset($item['link']);
                unset($item['guid']);
                unset($item['pubDate']);
                unset($item['source']);
                unset($item['category']);
                unset($item['description']);
                unset($item['content']);
                unset($item['id']);
            }
            $json = json_encode($items);
        } else {
            $items = array("Error" => "Sin Titulos");
        }
        return $this->response->withJson($items);
    });


    $app->get('/stored', function ($request, $response, $args) {
        $json = file_get_contents('items.json');
        if (!(is_null($json))) {
            $items = json_decode($json, true);
            foreach ($items as &$item) {
                unset($item['link']);
                unset($item['guid']);
                unset($item['pubDate']);
                unset($item['source']);
                unset($item['category']);
                unset($item['description']);
                unset($item['content']);
            }
            $json = json_encode($items);
        } else {
            $items = array("Error" => "No existe json");
        }

        return $this->response->withJson($items);
    });
});

$corsOptions = array(
    "origin" => "*",
    "exposeHeaders" => array("Content-Type", "X-Requested-With", "X-authentication", "X-client"),
    "allowMethods" => array('GET', 'POST', 'PUT', 'DELETE', 'OPTIONS')
);
$cors = new \CorsSlim\CorsSlim($corsOptions);

$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

// Catch-all route to serve a 404 Not Found page if none of the routes match
// NOTE: make sure this route is defined last
$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($req, $res) {
    $handler = $this->notFoundHandler; // handle using the default Slim page not found handler
    return $handler($req, $res);
});

function url_get_contents($Url)
{
    if (!function_exists('curl_init')) {
        die('CURL is not installed!');
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $Url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

function url_post_contents($Url, $Data)
{
    if (!function_exists('curl_init')) {
        die('CURL is not installed!');
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $Url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $Data);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

// Run app
$app->run();
