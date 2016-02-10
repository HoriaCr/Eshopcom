<?php
require 'vendor/autoload.php';
require_once 'mongo_conf.php';

error_reporting(E_ALL);
ini_set('display_errors', 'on');
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


function getSession(){
    if (!isset($_SESSION)) {
        session_start();
    }
    $sess = array();
    if(isset($_SESSION['uid']))
    {
        $sess["uid"] = $_SESSION['uid'];
        $sess["name"] = $_SESSION['name'];
        $sess["email"] = $_SESSION['email'];
    }
    else
    {
        $sess["uid"] = '';
        $sess["name"] = 'Guest';
        $sess["email"] = '';
    }
    // Both logged users and guests can add to cart.
    $sess["cart"] = $_SESSION['cart'];
    return $sess;
}

function destroySession(){
    if (!isset($_SESSION)) {
        session_start();
    }
    if(isSet($_SESSION['uid']))
    {
        unset($_SESSION['uid']);
        unset($_SESSION['name']);
        unset($_SESSION['email']);
        $info='info';
        if(isSet($_COOKIE[$info]))
        {
            setcookie ($info, '', time() - $cookie_time);
        }
        $msg="Logged Out Successfully...";
    }
    else
    {
        $msg = "Not logged in...";
    }
    return $msg;
}
function passwordHash($email, $pass) {
    return md5($email . $pass);
}

function isEmailRegistered($email) {
    $userQuery = array(
        'email' => $email,
    );
    $db = getMongo();
    $user = $db->users->findOne($userQuery);
    // email is already registered
    if (is_null($user)) {
        return False;
    }
    return True;
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

$app->get('/email_registered/{email}', function($req, $res, $args)  { 
    if (isEmailRegistered($args['email'])) {
        return $res->withAddedHeader('status', 'error')->withStatus(200);
    }  
    return $res->withAddedHeader('status', 'success')->withStatus(200);
});

$app->get('/logout', function($req, $res, $args) {
    $session = destroySession();
    $response["status"] = "info";
    $response["message"] = "Logged out successfully";
    return $res->write(json_encode($response))->withStatus(200);
});

$app->post('/login', function($req, $res, $args) { 
    $user = $req->getParsedBody()["customer"];
    $saltedPass = passwordHash($user['email'], $user['password']);
    $userQuery = array(
        'email' => $user['email'],
    );
    $db = getMongo();
    $user = $db->users->findOne($userQuery);
     
    if ($user != NULL) {
        if ($user['password'] === $saltedPass) {
            $response['status'] = "success";
            $response['message'] = 'Logged in successfully.';
            $response['name'] = $user['name'];
            $response['uid'] = $user['_id'];
            $response['email'] = $user['email'];
            if (!isset($_SESSION)) {
                session_start();
            }
            $_SESSION['uid'] = $user['_id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['name'] = $user['name'];
        } else {
            $response['status'] = "error";
            $response['message'] = 'Login failed. Incorrect credentials';
            return $res->write(json_encode($response))->withStatus(201);
        }
    } else {
            $response['status'] = "error";
            $response['message'] = 'No such user is registered';

            return $res->write(json_encode($response))->withStatus(201);
    }
    return $res->write(json_encode($response))->withStatus(200);
});

$app->post('/signup',  function($req, $res, $args) {
    $parsedBody = $req->getParsedBody()["customer"];
    $email = $parsedBody['email'];
    $password = $parsedBody['password'];
    $name = $parsedBody['name'];
    $address = $parsedBody['address'];
    $saltedPass = md5($email . $password);
    $phone = $parsedBody['phone'];
    if (isEmailRegistered($email) === True) {
        $response["status"] = "error";
        $response["message"] = "This email is already registered.";
        return $res->write(json_encode($response))->withStatus(201);
    }       
    $db = getMongo();
    $user = array(
        'email' => $email,
        'password' => $saltedPass,
        'name' => $name,
        'address' => $address,
        'phone' => $phone);
    try {
        $db->users->insert($user);
    }
    catch (MongoCursorException $mce) {
        $response["status"] = "error";
        $response["message"] = "Failure, mongoDB exception. Please try again";  
        return $res->write(json_encode($response))->withStatus(201);
    }
    
    if (!isset($_SESSION)) {
        session_start();
    }
    $_SESSION['uid'] = $user["_id"];
    $_SESSION['phone'] = $phone;
    $_SESSION['name'] = $name;
    $_SESSION['email'] = $email;

    $response["status"] = "success";
    $response["message"] = "User account created successfully";
    return $res->write(json_encode($response))->withStatus(200);
});

$app->get('/session', function($req, $res, $args) {
    $session = getSession();
    $response["uid"] = $session['uid'];
    $response["email"] = $session['email'];
    $response["name"] = $session['name'];
    return $res->write(json_encode($response))->withStatus(200);
});


$app->post('/order', function($req, $res, $args) { 
    $session = getSession();
    $cart = $session['cart'];
    if (empty($cart)) {
        $response["status"] = "error";
        $response["message"] = "Order failed, cart is empty!";
        return $res->write(json_encode($response))->withStatus(201);
    } else
    // check if the user is logged in
    if ($session['email'] === '') {
        $response["status"] = "error";
        $response["message"] = "Only logged users can order!";
        return $res->write(json_encode($response))->withStatus(201);
    }
    $email = $session['email'];
    $cart = $session["cart"];
    $orderDate = date('m/d/Y h:i:s a', time());
    
    $order = array(
        'products' => $cart,
        'email' => $email,
        'order_date' => $orderDate);
     
    $db = getMongo();
    
    foreach ($_SESSION['cart'] as $p) { 
        $productQuery = array(
            'id' => $p['id'],
        );
        $product = $db->products->findOne($productQuery);
        if ($product['stock'] < $p['quantity']) { 
            $response["status"] = "error";
            $response["message"] = "Some products are out of stock!";
            return $res->write(json_encode($response))->withStatus(201);
        }
    }

    foreach ($_SESSION['cart'] as $p) { 
        $db->products->update(array('id' => $p['id']),
            array('$inc' => array('stock' => -$p['quantity'])), 
            array('upsert' => true));
    }
    $db->orders->insert($order);
    // empty cart
    $_SESSION['cart'] = array();
    $response["status"] = "success";
    $response["message"] = "Order sent successfully";
    return $res->write(json_encode($response))->withStatus(200);
});

$app->get('/orders', function($req, $res, $args) {
    $session = getSession(); 
    $email = $session['email'];
    $db = getMongo();
    $orderQuery = array('email' => $email);   
    $cursor = $db->orders->find($orderQuery);
    $respData = array();
    foreach($cursor as $order) {
        array_push($respData, $order);
    }
    return $res->write(json_encode($respData, JSON_NUMERIC_CHECK));
});

$app->get('/cart', function($req, $res, $args) {
    $session = getSession();
    $cart = $session["cart"];
    return $res->write(json_encode($cart, JSON_NUMERIC_CHECK));
});

$app->post('/addtocart', function($req, $res, $args) {
    if (!isset($_SESSION)) {
        session_start();
    }
    
    if(!isSet($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }

    $product = $req->getParsedBody()['product'];
    $product['quantity'] = 1;
    foreach ($_SESSION['cart'] as &$p) {
        if ($p['id'] === $product['id']) {
            $p['quantity'] += 1;
            $product['quantity'] = 0;
        } 
    }
    if ($product['quantity'] > 0) {
        array_push($_SESSION['cart'], $product);
    }
    $response["status"] = "success";
    $response["message"] = "Product added to cart successfully!";

    return $res->write(json_encode($response))->withStatus(200);
});

$app->post('/removefromcart', function($req, $res, $args) {
    if (!isset($_SESSION)) {
        session_start();
    }
    
    if(!isSet($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }
    $product = $req->getParsedBody()['product'];
    $found = false;
    foreach (array_keys($_SESSION['cart']) as $key) {
        if ($_SESSION['cart'][$key]['id'] === $product['id']) {
            unset($_SESSION['cart'][$key]);
            $found = true;
        } 
    }

    if ($found === false) {
        $response["status"] = "error";
        $response["message"] = "No such product in cart!";
        return $res->write(json_encode($response))->withStatus(201);
    }
    $response["status"] = "success";
    $response["message"] = "Product removed from cart successfully!";

    return $res->write(json_encode($response))->withStatus(200);
});

$app->run();
?>
