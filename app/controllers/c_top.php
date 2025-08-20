<?php
namespace App\Controllers;

use App\Models\m_top;

class c_top {
    public function __construct()
    {
        $GLOBALS['logger']->info("File : " . __FILE__);
    }

    public function index() {
        $model = new m_top();
        $message = $model->getMessage();

        $twig = $GLOBALS['twig'];
        echo $twig->render('top.twig', [
            'username' => 'ゲスト',
            'items' => $message
        ]);
    }
}