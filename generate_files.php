<?php
if ($argc < 2) {
    echo "Usage: php generate_files.php [namespace/]c_[name][.php]\n";
    exit(1);
}

$inputPath = strtolower($argv[1]);
$inputPath = str_replace(['\\', '/'], '/', $inputPath);
$inputPath = preg_replace('/\\.php$/', '', $inputPath);
$inputPath = trim($inputPath, '/');

$segments = $inputPath === '' ? [] : explode('/', $inputPath);
$controllerFile = array_pop($segments);

if (strpos($controllerFile, 'c_') !== 0) {
    echo "Controller name must start with 'c_' prefix.\n";
    exit(1);
}

$name = substr($controllerFile, 2); // ファイル名・クラス名とも小文字

$wGroupDir = empty($segments) ? '' : implode('/', $segments) . '/';
$wClassGroupDir = empty($segments) ? '' : '\\' . implode('\\', $segments);

$baseDir = __DIR__ . '/app';
$paths = [
    'controller' => __DIR__ . "/app/controllers/{$wGroupDir}c_{$name}.php",
    'model'      => __DIR__ . "/app/models/{$wGroupDir}m_{$name}.php",
    'view'       => __DIR__ . "/app/views/{$wGroupDir}{$name}.twig",
    'css'        => __DIR__ . "/public/css/{$wGroupDir}{$name}.css",
    'js'         => __DIR__ . "/public/js/{$wGroupDir}{$name}.js"
];

 $template = [
'controller' => <<<PHP
<?php
namespace App\Controllers{$wClassGroupDir};

use App\Models{$wClassGroupDir}\m_{$name};

class c_{$name} {
    public function index() {
        \$model = new m_{$name}();
        \$message = \$model->getMessage();

        \$twig = \$GLOBALS['twig'];
        echo \$twig->render('{$wGroupDir}{$name}.twig', [
            'items' => \$message
        ]);
    }
}
PHP,

'model' => <<<PHP
<?php
namespace App\Models{$wClassGroupDir};

class m_{$name} {
    public function getMessage(): string {
        return "これは {$name} モデルからのメッセージです。";
    }
}
PHP,

'view' => <<<TWIG
{% extends 'base.twig' %}
{% block title %}{$name}{% endblock %}

{% block head %}
  <link rel="stylesheet" href="./css/{$wGroupDir}{$name}.css">
{% endblock %}

{% block content %}
  <h1>{$name}</h1>
  <p>{{ items }}</p>
{% endblock %}

{% block scripts %}
  <script src="./js/{$wGroupDir}{$name}.js"></script>
{% endblock %}
TWIG,

'css' => <<<CSS
/* cssファイル */

CSS,

'js' => <<<JS
// jsファイル

JS


];

// ディレクトリ作成とファイル生成
foreach ($paths as $type => $path) {
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    if (!file_exists($path)) {
        file_put_contents($path, $template[$type]);
        echo "Created: $path\n";
    } else {
        echo "Already exists: $path\n";
    }
}
