<?php
namespace App\Models;

class TokenizerModel {

    public string $domain; // elgiganten.dk

    public string $value_source; // text

    public string $tag_0; // span

    public string $tag_0_attr_0_name; // itemprop

    public string $tag_0_attr_0_value; // price

    public string $tag_0_attr_1_name; // content

    public string $tag_0_attr_1_value; // 749

    public string $tag_0_attr_2_name; // null

    public string $tag_0_attr_2_value; // null

    public string $tag_1; // div

    public string $tag_1_attr_0_name; // class

    public string $tag_1_attr_0_value; // list_price_inner_price divProductMarking

    public string $tag_1_attr_1_name; // null

    public string $tag_1_attr_1_value; // null

    public string $tag_1_attr_2_name; // null

    public string $tag_1_attr_2_value; // null

    public string $tag_2; // div

    public string $tag_2_attr_0_name; // null

    public string $tag_2_attr_0_value; // null

    public string $tag_2_attr_1_name; // null

    public string $tag_2_attr_1_value; // null

    public string $tag_2_attr_2_name; // null

    public string $tag_2_attr_2_value; // null

    public string $value; // 749

    public bool $is_target; // 1






    // domain,value_source,tag_0,tag_0_attr_0_name,tag_0_attr_0_value,tag_0_attr_1_name,tag_0_attr_1_value,tag_0_attr_2_name,tag_0_attr_2_value,tag_1,tag_1_attr_0_name,tag_1_attr_0_value,tag_1_attr_1_name,tag_1_attr_1_value,tag_1_attr_2_name,tag_1_attr_2_value,tag_2,tag_2_attr_0_name,tag_2_attr_0_value,tag_2_attr_1_name,tag_2_attr_1_value,tag_2_attr_2_name,tag_2_attr_2_value,value,is_target

    public function getString() {

        return $this->domain . ',' .
               $this->value_source . ',' .
               ($this->tag_0 ?? '') . ',' .
               ($this->tag_0_attr_0_name ?? '') . ',' .
               ($this->tag_0_attr_0_value ?? '') . ',' .
               ($this->tag_0_attr_1_name ?? '') . ',' .
               ($this->tag_0_attr_1_value ?? '') . ',' .
               ($this->tag_0_attr_2_name ?? '') . ',' .
               ($this->tag_0_attr_2_value ?? '') . ',' .
               ($this->tag_1 ?? '') . ',' .
               ($this->tag_1_attr_0_name ?? '') . ',' .
               ($this->tag_1_attr_0_value ?? '') . ',' .
               ($this->tag_1_attr_1_name ?? '') . ',' .
               ($this->tag_1_attr_1_value ?? '') . ',' .
               ($this->tag_1_attr_2_name ?? '') . ',' .
               ($this->tag_1_attr_2_value ?? '') . ',' .
               ($this->tag_2 ?? '') . ',' .
               ($this->tag_2_attr_0_name ?? '') . ',' .
               ($this->tag_2_attr_0_value ?? '') . ',' .
               ($this->tag_2_attr_1_name ?? '') . ',' .
               ($this->tag_2_attr_1_value ?? '') . ',' .
               ($this->tag_2_attr_2_name ?? '') . ',' .
               ($this->tag_2_attr_2_value ?? '') . ',' .
               $this->value . ',' .
               $this->is_target
        ;

    }


}

