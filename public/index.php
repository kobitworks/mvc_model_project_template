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
$mustLogin = ($_ENV['MUST_LOGIN'] ?? '1') === '1';

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

// class名、function名、namespaceの取得（クエリパラメータ優先）
$className = $_GET['c'] ?? ($parts[0] ?? null);
$functionName = $_GET['f'] ?? ($parts[1] ?? 'index');
$namespaceName = $_GET['n'] ?? null;
if (!isset($className) || strlen(trim($className)) === 0) {
    $className = $_ENV['DEFAULT_CLASS'] ?? 'top';
}
$baseName = strtolower(trim($className));
$actionName = $functionName;

$controllerClass = 'App\\Controllers\\' . ($namespaceName ? $namespaceName . '\\' : '') . 'c_' . $baseName;

if (!class_exists($controllerClass)) {
    $controller = new App\Controllers\c_error();
    $controller->notFound("コントローラー '$controllerClass' が見つかりません。");
    exit;
}

$controller = new $controllerClass();

if (!method_exists($controller, $actionName)) {
    $controller = new App\Controllers\c_error();
    $controller->notFound("アクション '$actionName' が存在しません。");
    exit;
}

$controller->$actionName();
