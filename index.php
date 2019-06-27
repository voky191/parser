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

for ($i=0;$i<32;$i++)
{
    $product = $products['data'][$i];

        $id = $product['id'];
        $name = $product['name'];
        $brand = $product['brand'];
        $sku = $product['sku'];
        $thumbnail_url = $product['thumbnail_url'];
        $price_usd = $product['current_price_usd'];
        //var_dump($sku);


        $sql = 'INSERT INTO product (id, name, brand, sku, thumbnail_url, price_usd) VALUES (:id,:name,:brand,:sku,:thumbnail_url,:price_usd)';
        $result = $db->prepare($sql);
        $result->bindParam(':id', $id, PDO::PARAM_STR);
        $result->bindParam(':name', $name, PDO::PARAM_STR);
        $result->bindParam(':brand', $brand, PDO::PARAM_STR);
        $result->bindParam(':sku', $sku, PDO::PARAM_STR);

        if (!file_exists('images/' . $brand)) {
        mkdir('images/' . $brand, 0777, true);
        }

        if($thumbnail_url != '') {
        $url = $thumbnail_url;
        $img = 'images/' . $brand . '/' . $sku . '.jpg';
        file_put_contents($img, file_get_contents($url));
        }

        $result->bindParam(':thumbnail_url', $img, PDO::PARAM_STR);
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

}
