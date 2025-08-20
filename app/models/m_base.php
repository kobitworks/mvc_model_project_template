<?php
namespace App\Models;

class m_base {
    public function __construct()
    {
        $GLOBALS['logger']->info("File : " . __FILE__);
    }

    public function getMessage(): string {
        return "これは base モデルからのメッセージです。";
    }
}