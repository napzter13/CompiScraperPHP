<?php
namespace App\Helpers;

use App\Models\Constants;
use App\Models\ScrapeData;
use DOMDocument;
use DOMXPath;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\TransferStats;

class ScrapeHelper {


    public function __construct(
        protected Constants $constants ) {
    }


    public function getUrlsByName($name) {
        $name = trim($name);
        return \cache()->remember('getUrlsByName' . $name, now()->addWeek(), function () use ($name) {

            $client = new Client;

            // Pricerunner.dk

            $urls = [];
            $product_names = [];
            $errors = [];

            $response = $client->get('https://www.pricerunner.dk/results?q=' . $name . '&suggestionsActive=false&suggestionClicked=false&suggestionReverted=false', [
            ])->getBody()->getContents();

            $error = $this->checkResponseError($response, $name);
            if ($error != 'ok') return $error;

            //// Init
            $dom = new DOMDocument();
            @$dom->loadHTML($response);

            //// Get Products
            $pricerunner_urls = [];
            $nodes = $dom->getElementsByTagName('a');
            foreach ($nodes as $node) {
                $url = $node->getAttribute('href');
                if (strpos($url, "/pl/") !== false) {
                    $url = 'https://www.pricerunner.dk' . $url;
                    array_push($pricerunner_urls, $url);

                    // Get Product Name
                    $productName = $dom->saveHTML($node);
                    $startsAt = strpos($productName, "<h3");
                    $startsAt2 = strpos($productName, ">", $startsAt);
                    $productName = substr($productName, $startsAt2 + 1);
                    $endsAt = strpos($productName, "</h3>");
                    $productName = substr($productName, 0, $endsAt);
                    array_push($product_names, $productName);
                    //
                }
            }

            foreach ($pricerunner_urls as $pr_url) {
                $response = $client->get($pr_url, [
                    ])->getBody()->getContents();
                $error = $this->checkResponseError($response, $pr_url);
                if ($error != 'ok') {
                    array_push($errors, $error);
                } else {
                    $dom = new DOMDocument();
                    @$dom->loadHTML($response);
                    $nodes = $dom->getElementsByTagName('a');
                    foreach ($nodes as $node) {
                        $url = $node->getAttribute('href');
                        if (strpos($url, "/gotostore/") !== false) {
                            array_push($urls, 'https://www.pricerunner.dk' . $url);
                        }
                    }
                }
            }

            $urls = array_unique($urls);

            return [
                'success' => true,
                'urls' => $urls,
                'product_names' => $product_names,
                'errors' => $errors,
            ];
        });

    }

    public function getData($url) {
        $url = trim($url);

        return \cache()->remember('getData' . $url, now()->addWeek(), function () use ($url) {

            $client = new Client;
            $response = null;
            $url_real = '';

            try {
                $response = $client->get($url, [
                    'timeout' => 10,
                    'connect_timeout' => 10,
                    'on_stats' => function (TransferStats $stats) use (&$url_real) {
                        $url_real = $stats->getEffectiveUri();
                    }
                ]);
                $response = $response->getBody()->getContents();
            } catch (ClientException $e) {
                return [
                    'success' => false,
                    'message' => 'ClientException: ' . $url_real,
                ];
            }

            $url = str_replace('www.', '', $url);
            $url_real = str_replace('www.', '', $url_real);

            $error = $this->checkResponseError($response, $url);
            if ($error != 'ok') return $error;


            //// Init
            $dom = new DOMDocument();
            @$dom->loadHTML($response);
            $dom->preserveWhiteSpace = false;
            $finder = new DomXPath($dom);

            //// Get names
            $names = [];
            $nodes = $dom->getElementsByTagName('h1');
            foreach ($nodes as $node) {
                $name = preg_replace('/\s+/', ' ', $node->nodeValue);
                $name = trim($name);
                array_push($names, $name);
            }

            //// Get Prices
            $data = [];
            $prices = [];

            $data = array_merge($data, $this->getById($dom, 'commodity-show-price'));
            $data = array_merge($data, $this->getById($dom, 'prisruta'));
            $data = array_merge($data, $this->getById($dom, 'main_BuyProductControl1_OnlinePriceLabel'));
            $data = array_merge($data, $this->getByIdTag($dom, $finder, 'variant-price', 'div'));
            $data = array_merge($data, $this->getByClassTag($dom, $finder, 'option-nonmembers', 'div'));
            $data = array_merge($data, $this->getByClass($dom, $finder, 'current_price'));
            $data = array_merge($data, $this->getByClass($dom, $finder, 'product_price'));
            $data = array_merge($data, $this->getByClass($dom, $finder, 'h4 m-product-price'));
            $data = array_merge($data, $this->getByClass($dom, $finder, 'main-price'));
            $data = array_merge($data, $this->getByClassTag($dom, $finder, 'col-xs-24 col-md-12 col-lg-10', 'span'));
            $data = array_merge($data, $this->getByClassTag($dom, $finder, 'Prices_Custom_DIV', 'div'));
            $data = array_merge($data, $this->getByClassTag($dom, $finder, 'list_price_inner_price', 'span'));
            $data = array_merge($data, $this->getByTag($dom, $finder, 'font'));
            $data = array_merge($data, $this->getBetweenStrings($response, "content_type:'product',value:'", "',", true));
            $data = array_merge($data, $this->getBetweenStrings($response, 'product:price:amount', '>', true));
            $data = array_merge($data, $this->getBetweenStrings($response, '<metaitemprop="price"content="', '">', true));
            $data = array_merge($data, $this->getBetweenStrings($response, 'priceCurrency":"DKK","price":"', '"', true));

            foreach ($data as $dat) {
                $value = $this->cleanPriceHtml($dat);
                preg_match_all('!\d+\.*\d*\,*\d*!', $value, $matches);
                foreach ($matches as $match) {
                    foreach ($match as $mat) {
                        array_push($prices, $this->cleanPrice($mat));
                    }
                }
            }
            $prices = array_unique($prices);
            ////

            $names = array_unique($names);

            $object = [
                'success' => true,
                'data' => (new ScrapeData(
                    $url_real,
                    parse_url($url_real)['host'],
                    $names,
                    $prices,
                ))->toArray(),
            ];

            if ($url != $url_real) {
                \cache()->put('getData' . $url_real, $object, now()->addDay());
            }



            $html = $response;
            $html = trim($response);
            $html = preg_replace("/[\r\n]*/","", $html);
            $html = preg_replace('!\s+!', ' ', $html);
            $html = strtolower($html);
            // $html = preg_replace("/[\s]*,[\s]*/",",", $html);
            // info(parse_url($url_real)['host'] . ',  ' . $html);





            //// Get Tokens Training
            if (count($prices) == 1) {
                $tokens = $this->tokenizeResponse($dom, $object);
                $labels_dev_data = implode(' ', $tokens['labels']);
                if (!is_file('train_labels.txt') || strpos(file_get_contents("train_labels.txt"), $labels_dev_data) !== 0) {
                    $fp = fopen('train_labels.txt', 'a');
                    fwrite($fp, $labels_dev_data . PHP_EOL);
                    $fp = fopen('train_text.txt', 'a');
                    fwrite($fp, implode(' ', $tokens['text']) . PHP_EOL);
                }
            }


            return $object;

        });

    }

    // <meta itemprop="price" content="1119.00">
    private function tokenizeResponse($dom, $object) {
        $text_dev = [];
        $labels_dev = [];

        array_push($text_dev, $this->cleanTextName($object['data']['domain']));
        array_push($labels_dev, 'DOMAIN');

        foreach ($dom->getElementsByTagName('*') as $node) {
            array_push($text_dev, $this->cleanTextName($node->tagName) . 'start');
            array_push($labels_dev, 'TAG_start');
            foreach ($node->attributes as $attr) {
                array_push($text_dev, $this->cleanTextName($attr->localName));
                array_push($labels_dev, 'ATTR_name');
                foreach (explode(' ', $attr->nodeValue) as $attrr) {
                    if (strlen($attrr) > 1) {
                        array_push($text_dev, $this->cleanTextName($attrr));
                        array_push($labels_dev, 'ATTR_value');
                    }
                }

                preg_match('!\d+\.*\d*\,*\d*!', $attr->nodeValue, $matches);
                foreach ($matches as $match) {
                    $val = $this->cleanPrice($match);
                    array_push($text_dev, 'attrnumberstart');
                    array_push($labels_dev, 'SPECIAL');
                    array_push($text_dev, $val);
                    if ($val == $object['data']['prices'][0]) {
                        array_push($labels_dev, 'PRICE');
                    } else {
                        array_push($labels_dev, 'O');
                    }
                    array_push($text_dev, 'attrnumberend');
                    array_push($labels_dev, 'SPECIAL');
                }
            }

            array_push($text_dev, $this->cleanTextName($node->tagName) . 'end');
            array_push($labels_dev, 'TAG_end');

            preg_match('!\d+\.*\d*\,*\d*!', $node->nodeValue, $matches);
            foreach ($matches as $match) {
                $val = $this->cleanPrice($match);
                array_push($text_dev, 'numberstart');
                array_push($labels_dev, 'SPECIAL');
                array_push($text_dev, $val);
                if ($val == $object['data']['prices'][0]) {
                    array_push($labels_dev, 'PRICE');
                } else {
                    array_push($labels_dev, 'O');
                }
                array_push($text_dev, 'numberend');
                array_push($labels_dev, 'SPECIAL');
            }

            array_push($text_dev, 'elementend');
            array_push($labels_dev, 'SPECIAL');
        }

        return [
            'text' => $text_dev,
            'labels' => $labels_dev,
        ];
    }


    private function checkResponseError($response, $var) {
        // if ( ! $response->ok()) {
        //     return [
        //         'success' => false,
        //         'message' => $var . ' - Error !ok: ' . $response->status(),
        //     ];
        // } else if ( ! $response->successful()) {
        //     return [
        //         'success' => false,
        //         'message' => $var . ' - Error !successful: ' . $response->status(),
        //     ];
        // } else if ($response->failed()) {
        //     return [
        //         'success' => false,
        //         'message' => $var . ' - Error failed: ' . $response->status(),
        //     ];
        // } else if ($response->serverError()) {
        //     return [
        //         'success' => false,
        //         'message' => $var . ' - Error serverError: ' . $response->status(),
        //     ];
        // } else if ($response->clientError()) {
        //     return [
        //         'success' => false,
        //         'message' => $var . ' - Error clientError: ' . $response->status(),
        //     ];
        // }

        return 'ok';
    }

    private function getById(DOMDocument $dom, $id) {
        $data = [];
        $node = $dom->getElementById($id);

        if (!$node) {
            return [];
        }

        array_push($data, $dom->saveHTML($node));

        return $data;
    }

    private function getByTag(DOMDocument $dom, DomXPath $finder, $tag) {
        $data = [];
        $nodes = $dom->getElementsByTagName('font');
        foreach($nodes as $node) {
            array_push($data, $dom->saveHTML($node));
        }
        return $data;
    }

    private function getByClass(DOMDocument $dom, DomXPath $finder, $class) {
        $data = [];
        $nodes = $finder->query("//*[contains(@class, '" . $class . "')]");
        foreach($nodes as $node) {
            array_push($data, $dom->saveHTML($node));
        }
        return $data;
    }

    private function getByClassTag(DOMDocument $dom, DomXPath $finder, $class, $tag) {
        $data = [];
        $nodes = $finder->query("//*[contains(@class, '" . $class . "')]");
        foreach($nodes as $node) {
            $nodes_nested = $finder->query($tag, $node);
            foreach ($nodes_nested as $node_nest) {
                array_push($data, $dom->saveHTML($node_nest));
            }
        }
        return $data;
    }

    private function getByIdTag(DOMDocument $dom, DomXPath $finder, $id, $tag) {
        $data = [];
        $nodes = $finder->query("//*[contains(@id, '" . $id . "')]");
        foreach($nodes as $node) {
            $nodes_nested = $finder->query($tag, $node);
            foreach ($nodes_nested as $node_nest) {
                array_push($data, $dom->saveHTML($node_nest));
            }
        }
        return $data;
    }

    private function getBetweenStrings($response, $startString, $endString, $removeWhiteSpace = false) {
        $data = [];

        if ($removeWhiteSpace) {
            $response = preg_replace("/\s+/", "", $response);
        }

        preg_match_all('/' . $startString . '(.*?)' . $endString . '/', $response, $matches);
        if (count($matches) > 0) {
            foreach ($matches[1] as $match) {
                array_push($data, $match);
            }
        }

        return $data;
    }



    private function cleanPriceHtml($price) {
        $price = str_replace('<sup>', ',', $price);
        $price = str_replace('</sup>', '', $price);
        $price = strip_tags($price);
        return $price;
    }

    private function cleanPrice($price) {
        $price = preg_replace('/[^0-9,.]+/', '', $price);

        $afterDotChars = substr($price, strpos($price, ".") + 1);
        if (strlen($afterDotChars) < 3) {
            $price = str_replace('.', ',', $price);
        }

        $price = str_replace('.', '', $price);
        $price = str_replace(',', '.', $price);
        $price = (float) $price;
        $price = number_format($price, 0, ',', '');
        $price = trim($price);

        return $price;
    }

    private function cleanTextName($name) {
        $name = preg_replace('/[^A-Za-z]+/', '', $name);
        $name = trim($name);

        return $name;
    }

}
