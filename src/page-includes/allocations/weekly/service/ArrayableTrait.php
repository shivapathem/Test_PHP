<?php
/*
 * Created on Thu Oct 28 2021
 *
 * Author: Cagri S. Kirbiyik
 * Name: Trait ArrayableTrait
 * Description: A Trait that lets you generate array from instance or instance from array
 *
 * Copyright (c) 2021 BBC
 */

trait ArrayableTrait {

    /**
     * Generates the instance from array
     *
     * @param array $array
     * @return self
     */
    public function fromArray($array = [])
    {
        foreach(get_object_vars($this) as $attrName => $attrValue){
            $this->{$attrName} = $array[$attrName] ?? $this->{$attrName};
        }

        return $this;
    }

     /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray() : array
    {
        $obj = get_object_vars($this);

        if(is_null($obj['ID'])){
            unset($obj['ID']);
        } 

        return $obj;
    }
    
}