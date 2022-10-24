<?php

namespace app\components;

class SumProviderRows 
{
    public static function total($provider, $fieldName, $format = true)
    {
        $total=0;
        foreach($provider->getModels() as $item){
            $total+=$item[$fieldName];
        }
        return $format ? number_format($total) : $total;
    }
    
    public static function totalCurrency($provider)
    {
        $totalRwf=0;
        $totalUgs=0;
        $totalFIB=0;
        $totalUSD=0;
        
        foreach($provider->getModels() as $item){
            if($item['currency']=='RWF')
                $totalRwf +=($item['price'] - $item['discount']);
            else if($item['currency']=='UGS')
                $totalUgs +=($item['price'] - $item['discount']);
            else if($item['currency']=='FIB')
                $totalFIB +=($item['price'] - $item['discount']);
            else if($item['currency']=='USD')
                $totalUSD +=($item['price'] - $item['discount']);
        }
        //format
        $totalRwf = number_format($totalRwf);
        $totalUgs = number_format($totalUgs);
        $totalFIB = number_format($totalFIB);
        $totalUSD = number_format($totalUSD);
        $return = "Total RWF: $totalRwf, UGS: $totalUgs, FIB: $totalFIB, USD $totalUSD";
        return $return;
    }
}
