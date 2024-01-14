<?php

header("Content-Type: text/html;charset=UTF-8");
header("Expires: Thu, 01 Dec 1994 16:00:00 GMT");
header("Last-Modified: ". gmdate("D, d M Y H:i:s"). " GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

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

//----------------------------------------------------------------------
//  データ保存用ファイル、画像保存ディレクトリのパーミッションチェック (START)
//----------------------------------------------------------------------
$messe = calf_permissionCheck($filePath,$commentFilePath,$pulldownFilePath,$closedFilePath,$dataDir,$perm_check01,$perm_check02,$perm_check03,$reservFileDir,$timeListFilePath);
//----------------------------------------------------------------------
//  データ保存用ファイルのパーミッションチェック (END)
//----------------------------------------------------------------------

//----------------------------------------------------------------------
//  書き込み・編集処理 (START)
//----------------------------------------------------------------------

if (isset($_POST['holiday_submit'])){
	
	//トークンチェック(CSRF対策のため追記 2020/07/20)
	if(empty($_SESSION['token']) || ($_SESSION['token'] !== $_POST['token'])){
		exit('ページ遷移エラー(トークン)');
	}
	$_SESSION['token'] = '';//トークン破棄
	
	
	$getYm = date('Y-m');
	if(isset($_GET['ym'])){
		$getYm = $_GET['ym'];
	}
	
	$holidayWriteData = '';
	if(isset($_POST['holiday_set']) && is_array($_POST['holiday_set'])){
		
		foreach($_POST['holiday_set'] as $val){
			  $holidayWriteData .= $val."\n";
		}
		
	}else{
		$holidayWriteData = '';
	}
	
	//更新月以外のデータを再セット
	$lines = array();
	$linesArray = array();
	$lines = file($filePath);
	foreach($lines as $linesVal){
		if(strpos($linesVal,$getYm) === false){
			$holidayWriteData .= $linesVal;
		}
	}
	
	$fp = fopen($filePath, "r+b") or die("fopen Error!!");
	// 俳他的ロック
	if (flock($fp, LOCK_EX)) {
		ftruncate($fp,0);
		rewind($fp);
		fwrite($fp, $holidayWriteData);// 書き込み
	}
	fflush($fp);
	flock($fp, LOCK_UN);
	fclose($fp);
	
			
	//コメントの登録
	$commentWriteData = '';
	if(is_array($_POST['comment'])){
		foreach($_POST['comment'] as $key => $val){
			  if(!empty($val)){
				$val = str_replace(array("\n","\r",",",'%'),array("<br />",'','、','％'),$val);
				$commentWriteData .= $key.','.$val."\n";
			  }
		}
	}
	
	//更新月以外のデータを再セット
	$lines = array();
	$linesArray = array();
	$lines = file($commentFilePath);
	foreach($lines as $linesVal){
		$linesArray = explode(',',$linesVal);
		if(strpos($linesArray[0],$getYm) === false){
			$commentWriteData .= $linesVal;
		}
	}
	
	$fp = fopen($commentFilePath, "r+b") or die("fopen Error!!");
	// 俳他的ロック
	if (flock($fp, LOCK_EX)) {
		ftruncate($fp,0);
		rewind($fp);
		fwrite($fp, $commentWriteData);// 書き込み
	}
	fflush($fp);
	flock($fp, LOCK_UN);
	fclose($fp);
		
			
	for($i = 0;$i < $pulldownCount;$i++){
		
		//プルダウンデータの登録
		$pulldownWriteData = '';
		if(is_array($_POST['pulldown'][$i])){
			foreach($_POST['pulldown'][$i] as $key => $val){
				  if(!empty($val)){
					$val = str_replace(array("\n","\r",",",'%'),array("",'','、','％'),$val);
					$pulldownWriteData .= $key.','.$val.','."\n";
				  }
			}
			
		}
		
		//更新月以外のデータを再セット
		$lines = array();
		$linesArray = array();
		$lines = file($pulldownFilePath[$i]);
		foreach($lines as $linesVal){
			$linesArray = explode(',',$linesVal);
			if(strpos($linesArray[0],$getYm) === false){
				$pulldownWriteData .= $linesVal;
			}
		}
		
		
		$fp = fopen($pulldownFilePath[$i], "r+b") or die("fopen Error!!");
		// 俳他的ロック
		if (flock($fp, LOCK_EX)) {
			ftruncate($fp,0);
			rewind($fp);
			fwrite($fp, $pulldownWriteData);// 書き込み
		}
		fflush($fp);
		flock($fp, LOCK_UN);
		fclose($fp);
		
		if(function_exists('reservCountReg')){
			//予約可能数データの保存（チェックされた日時のみ）※プラグイン対応
			if($reservCount == 1){
				reservCountReg($i,$reservCountNum,$reservFileDir);
			}
		}
		
	}
		
	
	//定休日保存処理
	$closedWriteData = '';
	if(isset($_POST['closed'])){
		foreach($_POST['closed'] as $val){
			  $closedWriteData .= $val."\n";
		}
	}else{
			  $closedWriteData = '';
	}
		$fp = fopen($closedFilePath, "r+b") or die("fopen Error!!");
		// 俳他的ロック
		if (flock($fp, LOCK_EX)) {
			ftruncate($fp,0);
			rewind($fp);
			fwrite($fp, $closedWriteData);// 書き込み
		}
			fflush($fp);
			flock($fp, LOCK_UN);
			fclose($fp);
	
	//再送信防止リダイレクト
	header("Location: ./complete.php?ym=$getYm");
	exit();
}
//----------------------------------------------------------------------
//  書き込み・編集処理 (END)
//----------------------------------------------------------------------
cffsg($warningMesse02,$cfilePath);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>管理画面</title>
<link href="style.css" rel="stylesheet" type="text/css" media="all" />
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>   
<script type="text/javascript">
//ポップアップ用JS
function openwin(url) {
 wn = window.open(url, 'win','width=620,height=700,status=no,location=no,scrollbars=yes,directories=no,menubar=no,resizable=no,toolbar=no,left=50,top=50');wn.focus();
}

$(function(){
　$(".message_com").delay(2000).fadeOut("slow");
});
//トグル
$(function(){
    $(".acrbtn").click(function () {
      $(".acrDescription").toggle("normal");
    });
});
//一括受付中反映
$(function(){
	$('#selectChange01').click(function(){
		$('select[name^="pulldown"]').val(1);
		$('#selectChangeBtn01').hide();
		$('#selectChangeBtn02').show();
		
	});
	$('#selectChange02').click(function(){
		$('select[name^="pulldown"]').val(0);
		$('#selectChangeBtn01').show();
		$('#selectChangeBtn02').hide();
	});
});

//日毎の全受付、解除
$(function(){
		   
<?php for($i=1;$i<32;$i++){ ?>
	$('#selectChangeDay01_<?php echo $i;?>').click(function(){
		$('select[class="pulldown_<?php echo $i;?>"]').val(1);
		$('#selectChangeBtnDay01_<?php echo $i;?>').hide();
		$('#selectChangeBtnDay02_<?php echo $i;?>').show();
		
	});
	$('#selectChangeDay02_<?php echo $i;?>').click(function(){
		$('select[class="pulldown_<?php echo $i;?>"]').val(0);
		$('#selectChangeBtnDay01_<?php echo $i;?>').show();
		$('#selectChangeBtnDay02_<?php echo $i;?>').hide();
	});
<?php } ?>
});

//数値を変更したらチェックを付ける
function oncheck(param){
	$('input#'+param).attr('checked','checked');
}

//トークンセット(CSRF対策のため追記 2020/07/20)
<?php $token = sha1(uniqid(mt_rand(), true)); $_SESSION['token'] = $token;?>
$(function(){	$('form').append('<input type="hidden" name="token" value="<?php echo $token;?>" />');	 });

</script>
</head>
<body id="admin">
<div id="wrapper">
<?php if(!$copyright){echo $warningMesse; exit;}else{ ?>
<?php if(!empty($messe))echo "<p class=\"fc_red api_error\">{$messe}</p>"; ?>
<?php if(@$_GET['mode'] == 'complete') echo '<p class="fc_red message_com">登録が完了しました</p>'; ?>
<div class="logout_btn"><a href="?logout=true">ログアウト</a></div>
<div class="pulldownList_btn"><a href="javascript:openwin('pulldown_manage.php')" >予約時間リストを追加、更新</a></div>
<div class="f5_btn"><a href="./<?php if(!empty($_GET["ym"])) echo '?ym='.$_GET["ym"];?>">最新の状態に更新</a></div>
<h1>予約・申し込み管理ページ</h1>
<h2>予約管理・休業日設定</h2>
<p class="acrbtn">【操作マニュアル・注意事項】</p>
<p class="acrDescription ml10" style="display:none">
※このページを開いている間にも予約が発生している可能性があります。残り予約可能数はリアルタイムでは反映されませんので更新時には必ず上部の「最新の状態に更新」を押してから速やかに更新して下さい。（でないと予約可能数を超えた申し込みが発生するリスクがあります）<br />
※プルダウンから「<?php echo $pulldownListArray[0];?>」、「<?php echo $pulldownListArray[1];?>」を選択し、ページ下の「登録」ボタンを押して下さい。<br />
※「<?php echo $pulldownListArray[0];?>」を選択した場合のみ「<?php echo $reservText;?>」ボタンが表示されます。<br />
※「未選択」を選択したものは非表示になります。

<?php if($reservCount == 1){ ?>
<br />※「残り予約可能数」を変更する場合は、数値を選択し、必ず右側の「変更する」にチェックを入れて下さい。（自動でチェックが入ります）<br />
※プルダウンで「<?php echo $pulldownListArray[0];?>」を選択していても、残り予約可能数が「0」の場合、<?php echo $pulldownListArray[1];?>と表示されます。<br />
※プルダウンで「<?php echo $pulldownListArray[1];?>」を選択した場合、残り予約可能数が1以上でも<?php echo $pulldownListArray[1];?>と表示されます。
<?php } ?>
<br />※休業日設定は設定したい日にチェックを入れてページ下の「登録」ボタンを押して下さい。曜日ごとの定休日はページ下で設定可能です。
<br />※日毎に補足、コメントも入力可能です。（改行は維持されます。文字数があまり多いと見づらくなる場合があります）<br />
※登録、変更などは月ごとに行なってください（複数月を同時に更新することはできません）</p>

<p><?php if($closedText) echo $closedText ;//定休日テキスト（オリジナルも可）?><span class="holidayCube" style="background:<?php echo $holidayBg;?>"></span>休業日</p>
<?php if($selectAllChange == 1){ ?>
<p class="taR" id="selectChangeBtn01"><a href="javascript:void(0)" id="selectChange01">すべてのプルダウンを受付中にする（登録ボタンを押すまでは登録されません）</a><br />※残り予約可能数は変更しません</p>
<p class="taR" id="selectChangeBtn02" style="display:none"><a href="javascript:void(0)" id="selectChange02">すべてのプルダウンを未選択にする（登録ボタンを押すまでは登録されません）</a><br />※残り予約可能数は変更しません</p>
<?php } ?>
<?php echo scheduleCalenderAdmin();?>
<?php Uqa4h78r();}//著作権表記リンク無断削除禁止?>
</div>
</body>
</html>