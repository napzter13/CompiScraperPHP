<?php
namespace App\Http\Controllers;

use App\Helpers\ScrapeHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class HomeController {

    public function __construct(protected ScrapeHelper $scrapeHelper ) {
    }

    public function index() {
        return \view('home.index');
    }

    public function clearCache() {
        Artisan::call('cache:clear');
        return true;
    }

    public function getDataByUrl(Request $request) {
        $urls = preg_split("/\r\n|\n|\r/", $request->input('urls'));
        $data = [];

        foreach ($urls as $url) {
            if (strpos($url, 'http') !== false) {
                $dat = $this->scrapeHelper->getData($url);
                array_push($data, $dat);
            }
        }

        return response($data, 200)
              ->header('Content-Type', 'text/plain');
    }

    public function getUrlsByProduct(Request $request) {
        $names = preg_split("/\r\n|\n|\r/", $request->input('names'));
        $data = [];

        foreach ($names as $name) {
            if (strlen($name) > 2) {
                $dat = $this->scrapeHelper->getUrlsByName($name);
                array_push($data, $dat);
            }
        }

        return response($data, 200)
              ->header('Content-Type', 'text/plain');
    }

}
