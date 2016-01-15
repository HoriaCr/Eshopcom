<?php
require_once 'mongo_conf.php';

$dbhost = DB_HOST;
$dbname = DB_NAME;

// Connect to test database
$m = new MongoClient("mongodb://$dbhost");
$db = $m->$dbname;
// Clear the database.
$db->drop();

// Insert categories
$collection = $db->createCollection("categories");
$data = file_get_contents("data_cat.json");

$categories = json_decode($data, true);
$collection->batchInsert($categories);

$collection = $db->createCollection("products");
$data = file_get_contents("product_data.json");
$phones = json_decode($data, true);
$collection->batchInsert($phones);

$db->createCollection("users");
$db->createCollection("orders");

?>
