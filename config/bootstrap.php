<?php

use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Monolog 初期化
$logDirectory = __DIR__ . '/../.logs';
if (!is_dir($logDirectory)) {
    mkdir($logDirectory, 0777, true);
}
$logger = new Logger('app');
$date = date('Ymd');
$logger->pushHandler(new StreamHandler($logDirectory . "/app_{$date}.log", Logger::DEBUG));

// Logger をグローバルに利用できるようにする
$GLOBALS['logger'] = $logger;

// Twig初期化
$loader = new FilesystemLoader(__DIR__ . '/../app/views');
$twig = new Environment($loader);

// 設定の読み込み
require_once __DIR__ . '/define.php';

// 共通関数の読み込み
require_once __DIR__ . '/../app/common/functions.php';

// ナビゲーション用のコントローラとメソッドの取得
$controllerDir = __DIR__ . '/../app/controllers';
$nav = get_controller_nav($controllerDir);
$twig->addGlobal('nav', $nav);

// Twigをグローバルに使えるようにする（必要に応じて）
$GLOBALS['twig'] = $twig;

