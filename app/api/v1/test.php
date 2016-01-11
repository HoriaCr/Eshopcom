<?php
$data = file_get_contents("product_data.json");
$products = json_decode($data, true);

foreach($products as $p) {
    echo $p["imageUrl"] . "\r\n";
}
// var_dump($products);

?>
