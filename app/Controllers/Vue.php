<?php

namespace App\Controllers;

class Vue extends BaseController
{
    public function index(): string
    {
        return view('vue');
    }
}
