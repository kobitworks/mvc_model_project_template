<?php
class cls_dbcopy {
    public static function mysql_to_sqlite(string $mysqlHost, string $mysqlDb, string $user, string $pass, string $sqliteDir): void {
        $sqliteFile = rtrim($sqliteDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $mysqlDb . '.sqlite';
        if (file_exists($sqliteFile)) {
            throw new \RuntimeException('SQLiteファイルが既に存在します。');
        }

        $mysql = new \PDO("mysql:host={$mysqlHost};dbname={$mysqlDb};charset=utf8mb4", $user, $pass);
        $sqlite = new \PDO('sqlite:' . $sqliteFile);

        $tables = $mysql->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);
        foreach ($tables as $table) {
            $create = $mysql->query("SHOW CREATE TABLE `$table`")->fetch(\PDO::FETCH_ASSOC)['Create Table'] ?? '';
            $create = self::mysqlCreateToSqlite($create);
            $sqlite->exec($create);

            $stmt = $mysql->query("SELECT * FROM `$table`");
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $cols = array_keys($row);
                $placeholders = implode(',', array_fill(0, count($row), '?'));
                $insert = $sqlite->prepare("INSERT INTO `$table` (`" . implode('`,`', $cols) . "`) VALUES ($placeholders)");
                $insert->execute(array_values($row));
            }
        }
    }

    public static function sqlite_to_mysql(string $sqliteFile, string $mysqlHost, string $user, string $pass): void {
        if (!file_exists($sqliteFile)) {
            throw new \RuntimeException('SQLiteファイルが存在しません。');
        }
        $dbName = pathinfo($sqliteFile, PATHINFO_FILENAME);

        $mysql = new \PDO("mysql:host={$mysqlHost};charset=utf8mb4", $user, $pass);
        $exists = $mysql->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$dbName}'")->fetch();
        if ($exists) {
            throw new \RuntimeException('MySQLデータベースが既に存在します。');
        }
        $mysql->exec("CREATE DATABASE `$dbName`");
        $mysql->exec("USE `$dbName`");

        $sqlite = new \PDO('sqlite:' . $sqliteFile);
        $tables = $sqlite->query("SELECT name, sql FROM sqlite_master WHERE type='table' AND name!='sqlite_sequence'")->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($tables as $tbl) {
            $create = self::sqliteCreateToMysql($tbl['sql']);
            $mysql->exec($create);

            $stmt = $sqlite->query("SELECT * FROM `{$tbl['name']}`");
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $cols = array_keys($row);
                $placeholders = implode(',', array_fill(0, count($row), '?'));
                $insert = $mysql->prepare("INSERT INTO `{$tbl['name']}` (`" . implode('`,`', $cols) . "`) VALUES ($placeholders)");
                $insert->execute(array_values($row));
            }
        }
    }

    private static function mysqlCreateToSqlite(string $sql): string {
        $sql = preg_replace('/AUTO_INCREMENT=\d+/i', '', $sql);
        $sql = preg_replace('/ENGINE=\w+\s*DEFAULT CHARSET=\w+/i', '', $sql);
        return $sql;
    }

    private static function sqliteCreateToMysql(string $sql): string {
        $sql = preg_replace('/"([^"]+)"/', '`$1`', $sql);
        $sql = str_ireplace('AUTOINCREMENT', 'AUTO_INCREMENT', $sql);
        return $sql;
    }
}
