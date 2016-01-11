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

function isUserRegistered($username) {
    $userQuery = array(
        'username' => $username,
    );
    $db = getMongo();
    $user = $db->users->findOne($userQuery);
    // username is already registered
    if ($user != null) {
        return True;
    }
    return False;
}

function isEmailRegistered($email) {
    $userQuery = array(
        'email' => $email,
    );
    $db = getMongo();
    $user = $db->users->findOne($userQuery);
    // email is already registered
    if (user != null) {
        return True;
    }
    return False;
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

$app->get('/users/email_registered/{email}', function($req, $res, $args)  { 
    if (isEmailRegistered($args['email'])) {
        return $res->withAddedHeader('status', 'error')->withStatus(200);
    }  
    return $res->withAddedHeader('status', 'success')->withStatus(200);
});

$app->get('/users/username_registered/{username}', function($req, $res, $args)  { 
    if (isUsernameRegistered($args['username'])) {
        return $res->withAddedHeader('status', 'error')->withStatus(200);
    }  
    return $res->withAddedHeader('status', 'success')->withStatus(200); 
});

$app->get('/users/login/{username}{password}', function($req, $res, $args) { 
    $saltedPass = md5($args['username'] . $args['password']); 
    $userQuery = array(
        'username' => $args['username'],
        'password' => $saltedPass
    );
    $user = $db->users->findOne($userQuery);
    if (user != null) {
        return $res->withAddedHeader('status', 'error')->withStatus(200);
    }  
    return $res->withAddedHeader('status', 'success')->withStatus(200);
    
});

$app->post('/users/signup',  function($req, $res, $args) {
    $parsedBody = $req->getParsedBody();
    $username = $parsedBody['username'];
    $email = $parsedBody['email'];
    $password = $parsedBody['password'];
    $lastName = $parsedBody['last_name'];
    $firstName = $parsedBody['first_name'];
    $address = $parsedBody['address'];
    $saltedPass = md5($username . $password); 
    if (isUsernameRegistered($username) or isEmailRegistered($email)) {
        return $res->write("This username/email is already registered.")->withAddedHeader(
            'status', 'error')->withStatus(200);
    }       
    $db = getMongo();
    $user = array(
        'username' => $username,
        'email' => $email,
        'password' => $saltedPass,
        'first_name' => $firstName,
        'last_name' => $lastName, 
        'address' => $address);
    $db->users->insert(user);
    return $res->write("Sign up succesful.")->withAddedHeader(
        'status', 'success')->withStatus(200);
});

$app->get('/users/orders/{username}{orderId}', function($req, $res, $args) {
    $db = getMongo();
    $orderQuery = array('orderId' => $args['orderId'],
        'username' => $args['username']);
    $order = $db->orders->findOne($orderQuery);
    return $res->write(json_encode($order, JSON_NUMERIC_CHECK));
});

$app->post('/users/orders/{username}{productIds}', function($req, $res, $args) {
    $db = getMongo();
    $orderQuery = array('username' => $args['username']);   
    $order = $db->orders->findOne($orderQuery);
    if ($order == null) { 
        $orderId = 0; 
    } else {
        $orderId = $order["id"] + 1;
    }
    $productIds = explode(",", $args['productIds']);
    $orderDate = date('m/d/Y h:i:s a', time());
    $order = array(
        'orderId' => $orderId,
        'productsIds' => $productIds,
        'username' => $args['username'],
        'order_date' => $orderDate); 
    $db->orders->insert(order);
    return $res->write('Order succesful')->withStatus(200);
});

$app->run();
?>
