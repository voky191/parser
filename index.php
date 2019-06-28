<?php
include 'component.php';
//url
$url='https://engine.sneakers123.com/api/releases?currency=eur&page=1';
//  Initiate curl
$ch = curl_init();
// Will return the response, if false it print the response
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// Set the url
curl_setopt($ch, CURLOPT_URL, $url);
// Execute
$result=curl_exec($ch);
// Closing
curl_close($ch);

$products = json_decode($result, true);

$db = Db::getConnection();

for ($i=0;$i<10;$i++)
{
    $product = $products['data'][$i];

        $id = $product['id'];
        $name = $product['name'];
        $brand = $product['brand'];
        $sku = $product['sku'];
        $thumbnail_url = $product['thumbnail_url'];
        $price_usd = $product['current_price_usd'];
        //var_dump($sku);

        $sizes = $product['sizes_eu'];

        $sizes_string = implode(' , ', $sizes);



        $sql = 'INSERT INTO product (id, name, brand, sku, sizes, price_usd) VALUES (:id,:name,:brand,:sku,:sizes,:price_usd)';
        $result = $db->prepare($sql);
        $result->bindParam(':id', $id, PDO::PARAM_STR);
        $result->bindParam(':name', $name, PDO::PARAM_STR);
        $result->bindParam(':brand', $brand, PDO::PARAM_STR);
        $result->bindParam(':sku', $sku, PDO::PARAM_STR);
        $result->bindParam(':sizes', $sizes_string, PDO::PARAM_STR);
        $result->bindParam(':price_usd', $price_usd, PDO::PARAM_STR);

        $result->execute();


    for($y=0;$y<$product['colors_count'];$y++)
    {
        $hsl = [];

        $hsl[0] = $product['colors'][$y]['h'];
        $hsl[1] = $product['colors'][$y]['s'];
        $hsl[2] = $product['colors'][$y]['l'];

        $sql = "INSERT INTO color(h, s, l) VALUES ('$hsl[0]','$hsl[1]','$hsl[2]')";
        $db->query($sql);
        $color_id = $db->lastInsertId();
        $sql = "INSERT INTO product_color(product_id, color_id) VALUES ('$id','$color_id')";
        $db->query($sql);
    }

        $innerUrl = "https://engine.sneakers123.com/api/releases/".$product['slug'];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $innerUrl);
        $result=curl_exec($ch);
        curl_close($ch);

        $sneakers = json_decode($result, true);

        $brand = $sneakers['brand'];
        $sku = $sneakers['sku'];
        $id = $sneakers['id'];

        $img = $sneakers['image'];
        $gallery = $sneakers['gallery'];
        $prod = $sneakers['products'];

    $img_array = [];
    $img_array[0] = $img;
    $img_array[1] = $gallery[0];
    $img_array[2] = $gallery[1];


    if (!file_exists('images/' . $brand)) {
        mkdir('images/' . $brand, 0777, true);
    }

    if (!file_exists('images/' . $brand . '/' . $sku)) {
        mkdir('images/' . $brand. '/' . $sku, 0777, true);
    }

    if($img != '') {
        $url = $img;
        $img = 'images/' . $brand . '/' . $sku . '/'.$sku.'-1.jpg';
        file_put_contents($img, file_get_contents($url));
    }

    foreach($gallery as $key => $value){
        $url2 = $value;
        $path = 'images/' . $brand . '/' . $sku . '/'.$sku.'-'.($key+2).'.jpg';
        file_put_contents($path, file_get_contents($url2));
    }

    foreach($img_array as $key => $value){
        $path = 'images/' . $brand . '/' . $sku . '/'.$sku.'-'.($key+1).'.jpg';
        $sql = "INSERT INTO images(product_id, image) VALUES ('$id','$path')";
        $db->query($sql);
    }

}
