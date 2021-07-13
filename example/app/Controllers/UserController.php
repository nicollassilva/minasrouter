<?php

namespace App\Controllers;

use App\Models\User;

class UserController
{
    public function show($id)
    {
        echo '<pre style="background-color: #212121; color: orange; padding: 10px;">';
        print_r(User::find($id));
        echo '</pre>';
    }
}
