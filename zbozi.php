<?php

/*
    zbozi.cz XML feed generator for Prestashop.

    Original author of the script is www.prestashopcesky.cz

    ondrej.pancocha@umporto.cz
*/

// eshop URL with http://
$shopUrl = 'http://www.umporto.cz';
$outFile = dirname ( realpath ( __FILE__ ) )."/zbozi.xml";
// Regex patterns to match products which are excluded from the catalogue
$REF_IGNORE_PATTERNS = array(
"DK_1029",
"OB_030",
"OB_029",
"OB_028",
"DK_1025",
"DK_1028",
"DK_1024",
"DK_1018",
"DK_1017",
"DK_1003",
"DK_1002",
"DK_1001",
"ES_00091",
"SA_00126",
"SA_00122",
"SA_00121",
"SA_00120",
"SA_00119",
"SA_00118",
"SA_00117",
"SA_00115",
"SA_00113",
"SA_00111",
"SA_00110",
"SA_00109",
"SA_00108",
"SA_00107",
"SA_00106",
"SA_00105",
"MA_00099",
"MA_00097",
"DK_1011",
"DK_1010",
"DK_1009",
"DK_1007",
"DK_1006",
"DK_1005",
"DK_1004",
"ES_00085",
"DE_[0-9]+",
"OB_0[0,1][0-9]",
"ES_00063",
"^SY_.*");

include(dirname(__FILE__).'/../config/config.inc.php'); // Prestashop config file
include(dirname(__FILE__).'/../init.php'); 
error_reporting(1);
$xmlOut=""; //output buffer

$p=Product::getProducts(2, 0, 0, 'id_product', 'desc', false); // 2 is lang id of czech
$products=Product::getProductsProperties(2, $p); // 2 is lang id of czech
//var_dump($products);

$xmlOut .='<?xml version="1.0" encoding="utf-8"?>
<SHOP>';
?>
<html>
<body>
<table border="1">
<tr>
    <th>refcode</th>
    <th>decsription</th>
    <th>delivery date</th>
    <th>comment</th>
</tr>
<?php
foreach ($products as $row) {
    var_dump($row);
    $ignore = false;
    $reference = $row['reference'];
    $dbgRow="<tr><td>".$reference."</td><td><a href=\"".$row['link']."\">".$row['name']."</a></td>";
    $img=Product::getCover($row['id_product']);
    var_dump($img);
    $imgId = $img['id_image'];
    $imgUrl = $shopUrl.'/img/p/';
    for ($i = 0; $i < strlen($imgId); $i++) {
        $imgUrl .= $imgId[$i] . '/';
    }
    $imgUrl .= $imgId . '.jpg';
    if ($row['quantity'] == "0") {
        $deliveryDate=-1;
    } else {
        $deliveryDate=0;
    }
    $dbgRow .= "<td>".$deliveryDate."</td>";

    // ignore product?
    
    foreach ($REF_IGNORE_PATTERNS as $ref_ignore_pattern) {
        if (preg_match("/".$ref_ignore_pattern."/",$reference)) {
            $dbgRow.="<td>Ignoring product, because it's refcode '".$reference.
                "' matches ignore pattern '".$ref_ignore_pattern."</td>";
            $ignore=true;
            break; 
        }
    }

    if (!isset($reference) || ($reference == "")) {
        $dbgRow.="<td>unknown product code, not exporting</td>";
        $ignore=true;
    } 

    if (!$ignore) {
        $xmlOut .='
<SHOPITEM>
    <PRODUCT>'.str_replace("&", "&amp;", $row['name']).'</PRODUCT>
    <DESCRIPTION>'.str_replace("&", "&amp;",strip_tags($row['description_short'])).'</DESCRIPTION>
    <URL>'.$row['link'].'</URL>
    <DELIVERY_DATE>'.$deliveryDate.'</DELIVERY_DATE>
    <IMGURL>'.$imgUrl.'</IMGURL>
    <PRICE>'.$row['price_tax_exc'].'</PRICE>
    <VAT>'.$row['rate'].'</VAT>
    <PRICE_VAT>'.($row['price']*1).'</PRICE_VAT>
</SHOPITEM>';
    }
    $dbgRow .="</tr>\n";
    echo $dbgRow;
}
echo "</table>";
$xmlOut .= '</SHOP>';

$fh = fopen($outFile, "w");
if (! $fh ) {
    echo ("Failed to open file '$outFile' for writing");
} else {
    fwrite($fh,$xmlOut);
    fclose();
    echo ("XML feed saved to '$outFile'");
}

?>
</body>
</html>
