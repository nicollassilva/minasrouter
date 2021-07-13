<?php

namespace App\Controllers;

use MinasRouter\Http\Request;

class WebController
{
    public function about(Request $request)
    {
        echo 'About page... Some data of the request:';
        echo '<pre style="background-color: #212121; color: orange; padding: 10px;">';
        print_r($request);
        echo '</pre>';
        echo 'Be happy! :)';
    }
}
