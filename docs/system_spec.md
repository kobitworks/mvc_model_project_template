# システム仕様

## 概要

本システムは PHP と Twig を用いたシンプルな MVC アプリケーションです。`public/index.php` をフロントコントローラとして利用し、リクエストを解析して適切なコントローラとアクションにディスパッチします。

## ディレクトリ構成

- `public/` : エントリポイントとなる `index.php` や公開用アセットを配置
- `config/` : 初期化処理を行う `bootstrap.php` を配置
- `app/controllers/` : `c_` で始まるコントローラクラスを配置
- `app/models/` : `m_` で始まるモデルクラスを配置
- `app/views/` : Twig テンプレートを配置
- `app/common/` : 共通関数などを配置

## 起動フロー

1. `public/index.php` がセッションを開始し Composer オートロードと `config/bootstrap.php` を読み込みます。
2. `Dotenv` で `.env` を読み込み、`MUST_LOGIN` や `DEFAULT_CLASS` などの環境変数を設定します。
3. URL やクエリパラメータからコントローラ (`c`) とアクション (`f`) を決定します。`c` には `namespace/controller` 形式で名前空間を指定可能です。
   `c` が指定されない場合は環境変数 `DEFAULT_CLASS`（未設定時は `top`）、`f` が指定されない場合は `index` が使用されます。
4. `MUST_LOGIN` が有効な場合、未ログインユーザは `c_login` にリダイレクトされます。
5. 指定されたコントローラやアクションが存在しない場合は、エラーメッセージをグローバル変数 `error_msg` として Twig に渡し、`base.twig` でポップアップ表示します。
6. コントローラ内でモデルを呼び出し、Twig を通じてビューをレンダリングします。

## 共通処理

- `config/bootstrap.php` で Monolog を用いたログ設定と Twig 環境の初期化を行います。
- `app/common/functions.php` の `get_controller_nav()` がコントローラ構成からナビゲーション情報を生成し、Twig のグローバル変数 `nav` として登録します。
- 基底コントローラ `c_base.php` はメニューから除外されます。

## エラーハンドリング

- 指定されたコントローラまたはアクションが存在しない場合、`error_msg` が Twig に設定され、`base.twig` がポップアップでメッセージを表示します。

## 環境変数

- `DEFAULT_CLASS` : コントローラが指定されない場合のデフォルトクラス（未設定時は `top`）
- `MUST_LOGIN` : `1` でログイン必須を有効化

## 命名規約

- コントローラは `c_` で、モデルは `m_` で始まるクラス名とし、必要に応じてサブディレクトリと名前空間を利用します。
- ビューには Twig テンプレートを用い、`base.twig` を共通レイアウトとして拡張します。
- スタイルシートは、`/public/css/` フォルダに配置され、クラス名と同じ命名規則に従います。
- JavaScript は、`/public/js/` フォルダに配置され、クラス名と同じ命名規則に従います。

## 例: c_main1.php と m_main1.php
