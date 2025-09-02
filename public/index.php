<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/bootstrap.php';

// Log出力
$GLOBALS['logger']->info("File : " . __FILE__);

// .env 読み込み
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();
$defaultClass = trim($_ENV['DEFAULT_CLASS'] ?? 'top');
$GLOBALS['twig']->addGlobal('default_class', $defaultClass);

// ルーティングテーブルの読み込み
$routeTable = [];
$routeFile = __DIR__ . '/../config/route.json';
if (is_readable($routeFile)) {
    $routeTable = json_decode(file_get_contents($routeFile), true) ?? [];
}

// URL解析
$pathInfo = $_SERVER['PATH_INFO'] ?? '';
if ($pathInfo !== '') {
    $path = trim($pathInfo, '/');
} else {
    $urlPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    $path = trim(str_replace($scriptDir, '', $urlPath), '/');
    $path = preg_replace('#^index\.php/?#', '', $path);
}
$parts = $path === '' ? [] : explode('/', $path);

// class名とfunction名の取得
$classParam = $parts[0] ?? null;
$actionParam = $parts[1] ?? null;

if (!isset($classParam) || strlen(trim($classParam)) === 0) {
    $classParam = $defaultClass;
}

// ルーティングテーブルによるクラス名の解決
$classKey = trim($classParam);
if (isset($routeTable[$classKey])) {
    $classParam = $routeTable[$classKey];
}

if (!isset($actionParam) || strlen(trim($actionParam)) === 0) {
    $actionParam = 'index';
}

// classパラメータを dir-class 形式で解析
$classParam = trim($classParam, '/');
$segments = $classParam === '' ? [] : explode('-', $classParam);
$className = array_pop($segments) ?? '';
$namespaceName = implode('\\', array_map('trim', $segments));

$baseName = strtolower(trim($className));
$actionName = trim($actionParam);

$controllerClass = 'App\\Controllers\\' . ($namespaceName ? $namespaceName . '\\' : '') . 'c_' . $baseName;

$errorMsg = null;
if (!class_exists($controllerClass)) {
    $errorMsg = "コントローラー '$controllerClass' が見つかりません。";
    $controllerClass = 'App\\Controllers\\c_' . $defaultClass;
    $actionName = 'index';
}

$controller = new $controllerClass();

if (!method_exists($controller, $actionName)) {
    $errorMsg = "アクション '$actionName' が存在しません。";
    $actionName = 'index';
}

$GLOBALS['twig']->addGlobal('error_msg', $errorMsg);

$controller->$actionName();
