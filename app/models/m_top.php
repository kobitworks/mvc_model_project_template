<?php
namespace App\Models;

class m_top {
    public function __construct()
    {
        $GLOBALS['logger']->info("File : " . __FILE__);
    }

    public function getMessage(): string {
        return "これは top モデルからのメッセージです。";
    }
}