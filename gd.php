<?php

require_once '../shopperdknew/app/Mage.php';
Mage::setIsDeveloperMode(true);
ini_set('display_errors', 1);
umask(0);
Mage::app('admin');
Mage::register('isSecureArea', 1);

//Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
set_time_limit(0);
ini_set('memory_limit','1024M');

$P = Array();

//getting custom dropdown values
$selAttrArray = [
    'farve', 'stik', 'kabinet', 'dimensioner', 'bluetooth1',
    'pladespillerindgang', 'bluetooth_stereo', 'wifi_anslutning', 'airplay', 'spotifyconnect',
    'fjernbetjening', 'trigger_12v', 'ethernet', 'niveau_kontrol', 'sw_lfe_janej'
];
foreach ($selAttrArray as $selAttr) {
    $name = $selAttr;
    $attributeInfo = Mage::getResourceModel('eav/entity_attribute_collection')->setCodeFilter($name)->getFirstItem();
    $attributeId = $attributeInfo->getAttributeId();
    $attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attributeId);
    $attributeOptions = $attribute->getSource()->getAllOptions(false);
    $P[$selAttr] = $attributeOptions;
}

// Getting products
$i = 0;
$collection = Mage::getModel('catalog/product')->getCollection()
    ->addAttributeToSelect('*')// select all attributes
    ->setPageSize(2000)// limit number of results returned
    ->setCurPage(1); // set the offset (useful for pagination)

$attributeSetModel = Mage::getModel('eav/entity_attribute_set');


// we iterate through the list of products to get attribute values
foreach ($collection as $product) {
    //if ($i == 6) break;
    $P[$i]['id'] = $product->getId();
    $P[$i]['name'] = $product->getName(); //get name
    $P['compare_name'][] = strtoupper($P[$i]['name']); //get name
    $P[$i]['sku'] = $product->getSku();
    $P['compare_sku'][] = strtoupper($P[$i]['sku']);
    $P[$i]['price'] = (float)$product->getPrice(); //get price as cast to float
    $P[$i]['description'] = $product->getDescription(); //get description
    $P[$i]['short_description'] = $product->getShortDescription(); //get short description
    $P[$i]['type'] = $product->getTypeId(); //get product type
    $P[$i]['status'] = $product->getStatus(); //get product status
    $P[$i]['weight'] = $product->getWeight();
    $P[$i]['stock_data'] = \Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getData();


    // getCategoryIds(); returns an array of category IDs associated with the product
    $j = 0;
    foreach ($product->getCategoryIds() as $category_id) {
        $category = Mage::getModel('catalog/category')->load($category_id);
        $P[$i]['category']['name'][$j] = $category->getName();
        $P[$i]['category']['parent'][$j] = $category->getParentCategory()->getName(); // get parent of category
        $j = $j + 1;
    }

    // Getting ALL arrtibites
    $attributes = $product->getAttributes();
    $j = 0;
    foreach ($attributes as $attribute) {
        $attributeCode = $attribute->getAttributeCode();
        $P[$i]['attr'][$attributeCode]['code'] = $attributeCode;
        $P[$i]['attr'][$attributeCode]['value'] = $attribute->getFrontend()->getValue($product);;
        $P[$i]['attr'][$attributeCode]['label'] = $attribute->getFrontend()->getLabel($product);
    }

    // Getting Attribute Set info
    $attributeSetId    = $product->getAttributeSetId();
    $attributeSetModel->load($attributeSetId);
    $P[$i]['attr_set_name'] = $attributeSetModel->getData();

    // Getting Attributes infos from Attribute Set
    $attributes = Mage::getModel('catalog/product_attribute_api')->items($attributeSetId);
    foreach($attributes as $_attribute){
        $P[$i]['attr_set_info'][$_attribute['code']] = $_attribute;
    }


    //Getting a simple products belonging to configurable product
    if($product->getTypeId() == "configurable") {
        $conf = Mage::getModel('catalog/product_type_configurable')->setProduct($product);
        $simple_collection = $conf->getUsedProductCollection()->addAttributeToSelect('*')->addFilterByRequiredOptions();
        $j = 0;
        foreach ($simple_collection as $simple_product) {
            //$P[$i]['simple'][$j] = $simple_product->getSku() . " - " . $simple_product->getName() . " - " . Mage::helper('core')->currency($simple_product->getPrice());
            $P[$i]['simple'][$j]['sku'] = $simple_product->getSku();
            $P[$i]['simple'][$j]['id'] = $simple_product->getId();
            $P[$i]['simple'][$j]['data'] = $simple_product->getData();

            $j = $j + 1;
        }
        $P[$i]['configurableAttributesData'] = $product->getTypeInstance()->getConfigurableAttributesAsArray();
    }


    //gets the image url of the product
    $P[$i]['image_url'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();
    $P[$i]['simple_prod_image'] = Mage::getModel('catalog/product_media_config')->getMediaUrl($product->getImage());
    $P[$i]['base_prod_image'] = $product->getImage();
    $P[$i]['thumb_prod_image'] = $product->getThumbnail();
    $P[$i]['small_prod_image'] = $product->getSmallImage();

    $media_gal = Mage::getModel('catalog/product')->load($product->getId())->getMediaGalleryImages();
    $j = 0;
    foreach ($media_gal as $image) {
        $P[$i]['media_gal'][$j] = $image->getFile();
        $j++;
    }


    $P[$i]['spec_price'] = $product->getSpecialPrice();
    $P[$i]['product_url'] = $product->getProductUrl();  //gets the product url



    $i = $i + 1;
}

$S = serialize($P);
$h = fopen('marantz.bin', 'wb');
fwrite($h, $S);
fclose($h);
