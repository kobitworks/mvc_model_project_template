# mvc_model_project_template
PHPでMVCモデルの構成で簡易的なシステムを作るテンプレート

## 使い方

ブラウザで `index.php?c=controller&f=action` の形式でコントローラとアクションを指定できます。
名前空間を利用する場合は `c` に `namespace/controller` を指定してください。
`c` が指定されない場合は環境変数 `DEFAULT_CLASS`（未設定時は `top`）、`f` が指定されない場合は `index` が呼び出されます。

