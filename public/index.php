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
$mustLogin    = ($_ENV['MUST_LOGIN'] ?? '1') === '1';
$defaultClass = trim($_ENV['DEFAULT_CLASS'] ?? 'top');
$GLOBALS['twig']->addGlobal('default_class', $defaultClass);

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

// class名とfunction名の取得（クエリパラメータ優先）
$classParam = $_GET['c'] ?? ($parts[0] ?? null);
$actionParam = $_GET['f'] ?? ($parts[1] ?? null);

if (!isset($classParam) || strlen(trim($classParam)) === 0) {
    $classParam = $defaultClass;
}

if (!isset($actionParam) || strlen(trim($actionParam)) === 0) {
    $actionParam = 'index';
}

// cパラメータを namespace/controller 形式で解析
$classParam = trim($classParam, '/');
$segments = $classParam === '' ? [] : explode('/', $classParam);
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
