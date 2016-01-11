<?php
require 'vendor/autoload.php';
require_once 'mongo_conf.php';

// Get Slim instance
$app = new \Slim\App();

function getMongo() {
    $dbhost = DB_HOST;
    $dbname = DB_NAME;
    // Connect to test database
    $m = new Mongo("mongodb://$dbhost");
    $db = $m->$dbname;
    return $db;
}
$mw = function ($request, $response, $next) {
    $response = $next($request, $response);
    return $response->withHeader(
        'Content-type', 'application/json')->withStatus(200);
};

$app->get('/categories', function($req, $res, $args) { 
    $db = getMongo();
    $cursor = $db->categories->find();
    $categories = iterator_to_array($cursor);
    return $res->write(json_encode($categories, JSON_NUMERIC_CHECK));
})->add($mw);

$app->get('/categories/{category}', function($req, $res, $args) { 
    $db = getMongo();
    $query = array(
        'category' => $args['category'],
    );
    $cursor = $db->products->find($query);
    $respData = array();
    foreach($cursor as $product) {
        array_push($respData, $product);
    }
    return $res->write(json_encode($respData, JSON_NUMERIC_CHECK));
})->add($mw);

$app->get('/categories/{category}/{productId}', function($req, $res, $args) {
    $db = getMongo();
    $productQuery = array(
        'id' => $args['productId'],
        'category' => $args['category']
    );
    $product = $db->products->findOne($productQuery);
    if ($product != null) {
        $product = $product["details"];
    }
    return $res->write(json_encode($product, JSON_NUMERIC_CHECK));
})->add($mw);

$app->run();

?>
