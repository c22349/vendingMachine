# vendingMachine

<h2>制作環境</h2>
<li>PHP</li>
<li>Visual Studio Code</li>

<h2>構成</h2>
<li>購入ページ：index.php</li>
<li>管理ページ：tool.php</li>
<li>購入結果ページ：result.php</li>
<li>スタイルシート1：index.css</li>
<li>スタイルシート2：tool.css</li>

<h2>詳細</h2>

<h3>購入ページ：index.php</h3>
<li>ステータスが「公開」のドリンク情報を一覧で表示されています。</li>
<li>金額をテキストボックスを記入します。</li>
<li>ドリンクを選択するラジオボタンを選択します</li>
<li>ドリンクの在庫が0の場合、ドリンクを選択するラジオボタンは表示せず、「売り切れ」と表示されます。</li>
<li>購入ボタンを押すと購入結果ページへ遷移します。</li>
<p><a href="http://codecamp22349.lesson7.codecamp.jp//php/21/php/index.php" target="_blank">自動販売機 （Web上）</a></p>

<h3>管理ページ：tool.php</h3>
<li>「ドリンク名」「値段」「在庫数」「公開ステータス」を入力し、商品を追加できます。</li>
<li>※商品を追加する場合、「商品画像」を指定してアップロードできます</li>
<li>追加した商品の一覧情報として、「商品画像」、「商品名」、「値段」、「在庫数」、「公開ステータス」のデータを一覧で表示されます。</li>
<li>商品一覧から指定ドリンクの在庫数を入力し、在庫数の変更できます</li>
<li>商品一覧から指定ドリンクの公開ステータス「公開」あるいは「非公開」の変更できます</li>
<li>アップロードできる「商品画像」のファイル形式は「JPEG」、「PNG」のみ可能とする。「JPEG」、「PNG」以外はエラーメッセージを表示して、商品を追加できません。</li>
<p><a href="http://codecamp22349.lesson7.codecamp.jp//php/21/php/tool.php" target="_blank">自動販売機管理ツール （Web上）</a></p>
