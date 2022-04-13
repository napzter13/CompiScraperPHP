<?php
namespace App\Models;

class TokenizerModel {

    public string $domain; // elgiganten.dk

    public string $value_source; // text

    public string $tag_0; // span

    public array $tag_0_attributes = [];

    public string $tag_1; // div

    public array $tag_1_attributes = [];

    public string $tag_2; // div

    public array $tag_2_attributes = [];

    public string $value; // 749

    public bool $is_target; // 1






    // domain,value_source,tag_0,tag_0_attr_0_name,tag_0_attr_0_value,tag_0_attr_1_name,tag_0_attr_1_value,tag_0_attr_2_name,tag_0_attr_2_value,tag_1,tag_1_attr_0_name,tag_1_attr_0_value,tag_1_attr_1_name,tag_1_attr_1_value,tag_1_attr_2_name,tag_1_attr_2_value,tag_2,tag_2_attr_0_name,tag_2_attr_0_value,tag_2_attr_1_name,tag_2_attr_1_value,tag_2_attr_2_name,tag_2_attr_2_value,value,is_target

    static function generateHeaderString($objects) {
        $streng = 'domain,value_source,';

        $depth = 10;

        $streng .= 'tag_0,';
        for ($i=0; $i < $depth; $i++) {
            $streng .= 'tag_0_attr_'.$i.'_name,';
            for ($a=0; $a < $depth; $a++) {
                $streng .= 'tag_0_attr_'.$i.'_value_'.$a.',';
            }
        }
        $streng .= 'tag_1,';
        for ($i=0; $i < $depth; $i++) {
            $streng .= 'tag_1_attr_'.$i.'_name,';
            for ($a=0; $a < $depth; $a++) {
                $streng .= 'tag_1_attr_'.$a.'_value_'.$a.',';
            }
        }
        $streng .= 'tag_2,';
        for ($i=0; $i < $depth; $i++) {
            $streng .= 'tag_2_attr_'.$i.'_name,';
            for ($a=0; $a < $depth; $a++) {
                $streng .= 'tag_2_attr_'.$a.'_value_'.$a.',';
            }
        }

        $streng .= 'value,is_target';

        return $streng;
    }

    public function getString() {
        $streng = $this->domain . ',' .
                  $this->value_source . ',' .
                  $this->tag_0 . ',';

        $depth = 10;

        $a = 0;
        foreach ($this->tag_0_attributes as $key => $val) {
            if ($a > $depth-1) break;
            $streng .= $key . ',';
            $i = 0;
            foreach ($val as $va) {
                if (strlen($va) < 2) continue;
                if ($i > $depth-1) break;
                $streng .= $va . ',';
                $i++;
            }
            for ($i=$i; $i < $depth; $i++) {
                $streng .= 'NONE,';
            }
            $a++;
        }
        for ($a=$a; $a < $depth; $a++) {
            $streng .= 'NONE,';
        }

        $streng .= ($this->tag_1 ?? 'NONE') . ',';

        $a = 0;
        foreach ($this->tag_1_attributes as $key => $val) {
            if ($a > $depth-1) break;
            $streng .= $key . ',';
            $i = 0;
            foreach ($val as $va) {
                if (strlen($va) < 2) continue;
                if ($i > $depth-1) break;
                $streng .= $va . ',';
                $i++;
            }
            for ($i=$i; $i < $depth; $i++) {
                $streng .= 'NONE,';
            }
            $a++;
        }
        for ($a=$a; $a < $depth; $a++) {
            $streng .= 'NONE,';
        }

        $streng .= ($this->tag_2 ?? 'NONE') . ',';

        $a = 0;
        foreach ($this->tag_2_attributes as $key => $val) {
            if ($a > $depth-1) break;
            $streng .= $key . ',';
            $i = 0;
            foreach ($val as $va) {
                if (strlen($va) < 2) continue;
                if ($i > $depth-1) break;
                $streng .= $va . ',';
                $i++;
            }
            for ($i=$i; $i < $depth; $i++) {
                $streng .= 'NONE,';
            }
            $a++;
        }
        for ($a=$a; $a < $depth; $a++) {
            $streng .= 'NONE,';
        }

        $streng .= $this->value . ',' .
                  ($this->is_target ? '1' : '0');

        return $streng;
    }


}

