<?php
namespace App\Controllers;

use App\Models\m_base;

class c_base {
    public function __construct()
    {
        $GLOBALS['logger']->info("File : " . __FILE__);
    }

    public function index() {
        $model = new m_base();
        $message = $model->getMessage();

        $twig = $GLOBALS['twig'];
        echo $twig->render('base.twig', [
            'items' => $message
        ]);
    }
}