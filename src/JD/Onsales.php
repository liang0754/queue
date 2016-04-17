<?php
namespace src\JD;
/**
 * Created by PhpStorm.
 * User: liang
 * Date: 16-4-17
 * Time: 下午7:53
 */
class Onsales
{

    public function productSkus()
    {
        $skus = [];
        for($i=1;$i<=10;$i++){
            $skus[] = $i;
        }
        return $skus;
    }
}