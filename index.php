<?php
/**
 * Created by PhpStorm.
 * User: liang
 * Date: 16-4-16
 * Time: 下午3:40
 */

include "./vendor/autoload.php";

//Server
$JD_items = new \src\JD\Onsales();
$skus = $JD_items->productSkus();


$JD_queue = new \src\Redis\JD\Queue();
$JD_queue->insert($skus);




//Client
$JD_queue = new \src\Redis\JD\Queue();

while (true) { //Mock a daemon


    if($sku = $JD_queue->pop()){

        try {
            $Product = new \src\Product(['sku' => $sku]);
            $Product->update();

            $JD_item = new \src\JD\Item(['outer_id' => $sku]);
            $JD_item->update();

            $JD_queue->finished($sku);

        }catch (Exception $e){

            $success = $JD_queue->reinsert($sku);

            if($success){
                $JD_item = new \src\JD\Item(['outer_id' => $sku]);
                $JD_item->offsale();
            }
        }
    }
}

