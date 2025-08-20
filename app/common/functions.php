<?php
/**
 * app/controllers 配下のフォルダ構成からナビゲーション情報を取得する。
 *
 * 直下のコントローラファイルはトップメニューの項目となり、
 * フォルダはグループとして再帰的に処理する。フォルダ内のファイルはサブメニュー、
 * さらにフォルダがネストしている場合はサードメニューとして扱う。
 * 除外対象は config/define.php に定義された定数で設定する。
 *
 * @param string $controllerDir 探索対象のディレクトリ
 * @param string $namespace     現在の名前空間（再帰用）
 * @return array<int, array<string, mixed>>
 */
function get_controller_nav(string $controllerDir, string $namespace = ''): array
{
    $nav = [];

    $excludeDirs  = defined('EXCLUDE_CONTROLLER_DIRS') ? EXCLUDE_CONTROLLER_DIRS : [];
    $excludeFiles = defined('EXCLUDE_CONTROLLER_FILES') ? EXCLUDE_CONTROLLER_FILES : [];

    try {
        foreach (scandir($controllerDir) as $entry) {
            if ($entry === '.' || $entry === '..' || strpos($entry, '.') === 0) {
                continue;
            }
            $path = $controllerDir . '/' . $entry;

            if (is_dir($path)) {
                if (in_array($entry, $excludeDirs, true)) {
                    continue;
                }
                $children = get_controller_nav($path, trim($namespace . '/' . $entry, '/'));
                $nav[] = [
                    'name'       => $entry,
                    'controller' => null,
                    'namespace'  => '',
                    'children'   => $children,
                ];
            } elseif (preg_match('/^c_.+\.php$/', $entry)) {
                if (in_array($entry, $excludeFiles, true)) {
                    continue;
                }
                $name = substr($entry, 2, -4);
                $nav[] = [
                    'name'       => $name,
                    'controller' => $name,
                    'namespace'  => $namespace,
                    'children'   => [],
                ];
            } else {
                // 不明なファイルタイプについてログを出力する
                error_log("Unknown file type: $entry in $controllerDir");
            }
        }
    } catch (Exception $e) {
        // 例外が発生した場合についてログを出力する
        error_log("An error occurred: " . $e->getMessage());
    }

    return $nav;
}
