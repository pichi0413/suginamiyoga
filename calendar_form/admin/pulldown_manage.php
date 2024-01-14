<?php
#設定ファイルインクルード
require_once('./config.php');
//----------------------------------------------------------------------
//  ログイン処理 (START)
//----------------------------------------------------------------------
session_name($session_name);
session_start();
calf_authAdmin($userid,$password);
//----------------------------------------------------------------------
//  ログイン処理 (END)
//----------------------------------------------------------------------

//プルダウンリストデータの更新
if(isset($_POST['update'])){
	
	//トークンチェック(CSRF対策)
	if(empty($_SESSION['token2']) || ($_SESSION['token2'] !== $_POST['token2'])){
		exit('ページ遷移エラー(トークン)');
	}
	$_SESSION['token2'] = '';//トークン破棄
	
	
	$writeData = rtrim($_POST["time_list"]);
	$fp = fopen($timeListFilePath, "r+b") or die("fopen Error!!");
	// 俳他的ロック
	if (flock($fp, LOCK_EX)) {
		ftruncate($fp,0);
		rewind($fp);
		fwrite($fp, $writeData);// 書き込み
	}
	fflush($fp);
	flock($fp, LOCK_UN);
	fclose($fp);
	$messe = '<p class="fc_red api_error">更新完了！</p>';
}

$lines = file($timeListFilePath);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>予約時間リスト管理画面</title>
<link href="style.css" rel="stylesheet" type="text/css" media="all" />
</head>
<body id="admin">
<div id="pulldownManageWrap">
<?php if(!empty($messe)) echo $messe ;?>
<h1>予約時間リスト管理</h1>
<p>管理画面の予約時間リストの更新、及び新規追加、削除が可能です。<br />
それぞれ1行毎に記述して下さい。途中に空の行は入れないで下さい。<br />
登録内容は「時間」に限定しません。なんでもOKです。<br />
記入例　11：00～、AM9：00、午前10時、佐藤太郎　など。
</p>
<h2>予約時間リスト更新</h2>
<form action="" method="post">
<?php
$token = sha1(uniqid(mt_rand(), true));
$_SESSION['token2'] = $token;
?>
<input type="hidden" name="token2" value="<?php echo $token;?>" />
<textarea name="time_list" rows="10" cols="40" style="width:90%;"><?php foreach($lines as $key => $val) echo $val;?></textarea>
<p class="taC"><input type="submit" name="update" value="　更新　" class="submitBtn" /></p>
</form>

<h2>注意事項（初めに必ずご一読下さい）</h2>
<ul>
<li>予約時間ごとの予約ではなく単純に日ごとで予約を受付ける場合は内容を空にして「更新」して下さい。
<li>リストを更新すれば表示側は自動で変更されます。
<li>リストの途中に追加するなど、<span class="col19">途中で順番を変えた場合には予約可能数の再設定が必要になります</span>のでご注意下さい。（行番号で管理しているため）
<li>こちらを変更したら管理画面（予約管理ページ）ではページ上部の「最新の状態に更新」を押すか、ブラウザを更新（F5）して下さい。
<li>リスト数が多くなるほど管理画面の表示が重くなる傾向にありますので極端に（10以上など）多く設定することはオススメしません。<span class="col19">（特に15以上の場合、PHPの上限値に引っかかり更新できない可能性があります）</span>
<li>特殊文字、機種依存文字、記号等は基本的には使用できません。（使っても環境により表示されない、または不具合が発生することがあります）
</li>
</ul>
<p class="close_btn"><a href="javascript:window.close();">CLOSE</a></p>
</div>
<?php Uqa4h78r()//著作権表記リンク無断削除禁止?>
</body>
</html>