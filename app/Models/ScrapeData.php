<?php

namespace App\Models;

class ScrapeData {
    public function __construct(
        protected $url,
        protected $domain,
        protected $names,
        protected $prices,
    ) { }

    public function toArray() {
        return [
            'url' => $this->url,
            'domain' => $this->domain,
            'names' => $this->names,
            'prices' => $this->prices,
        ];
    }
}
