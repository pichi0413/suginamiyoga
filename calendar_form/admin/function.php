<?php
//----------------------------------------------------------------------
// 　関数定義（基本的に変更不可） (START)
//----------------------------------------------------------------------
function calf_h($string) {
  return htmlspecialchars($string, ENT_QUOTES,'utf-8');
}
//ログイン認証
function calf_authAdmin($userid,$password){
	
	//ログアウト処理
	if(isset($_GET['logout'])){
		$_SESSION = array();
		session_destroy();//セッションを破棄
	}
	
	$error = '';
	# セッション変数を初期化
	if (!isset($_SESSION['auth'])) {
	  $_SESSION['auth'] = FALSE;
	}
	
	if (isset($_POST['userid']) && isset($_POST['password'])){
	  foreach ($userid as $key => $value) {
		if ($_POST['userid'] === $userid[$key] &&
			$_POST['password'] === $password[$key]) {
		  $oldSid = session_id();
		  session_regenerate_id(TRUE);
		  if (version_compare(PHP_VERSION, '5.1.0', '<')) {
			$path = session_save_path() != '' ? session_save_path() : '/tmp';
			$oldSessionFile = $path . '/sess_' . $oldSid;
			if (file_exists($oldSessionFile)) {
			  unlink($oldSessionFile);
			}
		  }
		  $_SESSION['auth'] = TRUE;
		  break;
		}
	  }
	  if ($_SESSION['auth'] === FALSE) {
		$error = '<div style="text-align:center;color:red">ユーザーIDかパスワードに誤りがあります。</div>';
	  }
	}
	if ($_SESSION['auth'] !== TRUE) {
echo <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
<meta name="robots" content="noindex,nofollow" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>管理画面ログイン認証</title>
<link href="style.css" rel="stylesheet" type="text/css" media="all" />
</head>
<body id="auth">{$error}
<div id="login_form">
<p class="taC">管理画面に入場するにはログインする必要があります。<br />管理者以外の入場は固くお断りします。</p>
<form action="./" method="post">
<label for="userid">ユーザーID</label>
<input class="input" type="text" name="userid" id="userid" value="" style="ime-mode:disabled" />
<label for="password">パスワード</label>      
<input class="input" type="password" name="password" id="password" value="" size="30" />
<p class="taC">
<input class="button-primary" type="submit" name="login_submit" value="　ログイン　" />
</p>
</form>
</div>
</body>
</html>
EOF;
exit();
	}
}
//パーミッションチェック関数
function calf_permissionCheck($filePath,$commentFilePath,$pulldownFilePath,$closedFilePath,$dataDir,$perm_check01,$perm_check02,$perm_check03,$reservFileDir,$timeListFilePath){
	$messe = '';
	if(!is_writable($dataDir)){
		$messe = $perm_check02;
		exit($messe);
	}
	elseif(!is_writable($reservFileDir)){
		$messe = "data/reservディレクトリのパーミッションが正しくありません。777等書き込み可能なパーミッションに変更する必要があります";
		exit($messe);
	}
	elseif (!is_writable($filePath)){
		$messe = str_replace(dirname(__FILE__).'/','',$filePath).$perm_check01;
	}
	elseif(!is_writable($closedFilePath)){
		$messe = str_replace(dirname(__FILE__).'/','',$closedFilePath).$perm_check01;
	}
	elseif(!is_writable($commentFilePath)){
		$messe = str_replace(dirname(__FILE__).'/','',$commentFilePath).$perm_check01;
	}
	elseif(!is_writable($timeListFilePath)){
		$messe = str_replace(dirname(__FILE__).'/','',$timeListFilePath).$perm_check01;
	}
	elseif(@$_GET['check']=='permission'){
		$messe = $perm_check03;
	}
	return $messe;
}

//カレンダー生成（一般ユーザー向け表示用）PC用表形式　※デフォルト
function scheduleCalenderTable($ym,$timeStamp){
global $todayFlag,$todayFlagBg,$filePath,$dispMonth,$holidayFilePath,$flagHiddenPrev,$closedFilePath,$closedBg,$holidayBg,$commentFilePath,$weekArray,$scheList,$pulldownFilePath,$pulldownCount,$timeArray;

	$scheduleCalendar = '<table id="calenderTable">';

	//休業日データ取得
	$holidayArray = file($filePath);
	//祝日データを読み込み
	$shukujituArray = file_exists($holidayFilePath) ? file($holidayFilePath) : array();
	//定休日データの取得
	$closedArray = file($closedFilePath);
	
	//----------------------------------------------------------------------
	// 　コメントデータを取得 (START)
	//----------------------------------------------------------------------
	$commentArray = file($commentFilePath);
	//----------------------------------------------------------------------
	// 　コメントデータを取得 (END)
	//----------------------------------------------------------------------
	
	//今月、来月
	$prev = date("Y-m",mktime(0,0,0,date("m",$timeStamp)-1,1,date("Y",$timeStamp)));
	$next = date("Y-m",mktime(0,0,0,date("m",$timeStamp)+1,1,date("Y",$timeStamp)));
	
	
	$dspPrev = '<a href="?ym='.$prev.'">&laquo;</a>';//前月へのナビ
	
	if((strtotime($prev.'-01') < strtotime(date("Y-m-01",mktime(0,0,0,date("m")-$dispMonth,1,date("Y"))))) || ($flagHiddenPrev == 0 && strtotime($ym.'-01') <= strtotime(date('Y-m-01')))){
		$dspPrev = '';
	}
	
	$dspNext = '<a href="?ym='.$next.'">&raquo;</a>';//翌月へのナビ
	
	if(strtotime($next.'-01') > strtotime(date("Y-m-01",mktime(0,0,0,date("m")+$dispMonth,1,date("Y"))))){
		$dspNext = '';
	}
	
	$scheduleCalendar .= '
<tr><th class="calenderHeader">'.$dspPrev.'</th><th colspan="5" class="calenderHeader">'.date("Y",$timeStamp) . "年" . date("n",$timeStamp). "月" .'</th><th class="calenderHeader">'.$dspNext.'</th></tr>
<tr><th class="youbi_0">'.$weekArray[0].'</th><th>'.$weekArray[1].'</th><th>'.$weekArray[2].'</th><th>'.$weekArray[3].'</th><th>'.$weekArray[4].'</th><th>'.$weekArray[5].'</th><th class="youbi_6">'.$weekArray[6].'</th></tr>
<tr>
';
	
	//月末
	$lastDay = date("t", $timeStamp);
	
	//1日の曜日
	$youbi = date("w",mktime(0,0,0,date("m",$timeStamp),1,date("Y",$timeStamp)));
	
	//最終日の曜日
	$lastYoubi = date("w",mktime(0,0,0,date("m",$timeStamp)+1,0,date("Y",$timeStamp)));
	
	$scheduleCalendar .= str_repeat('<td></td>',$youbi);
	
	for($day = 1; $day <= $lastDay; $day++,$youbi++){
		
		//----------------------------------------------------------------------
		// 　コメント用タグ生成 (START)
		//----------------------------------------------------------------------
		$commentTag = '';
		if(count($commentArray) > 0){
			foreach($commentArray as $commentArrayVal){
				$commentArrayExp = explode(',',$commentArrayVal);
				if(strtotime($ym."-".$day) == strtotime($commentArrayExp[0]) ){
					$commentTag = "\n".'<div class="scheduleComment">'.rtrim($commentArrayExp[1]).'</div>';
					break;
				}
			}
		}
		//----------------------------------------------------------------------
		// 　コメント用タグ生成 (END)
		//----------------------------------------------------------------------
		
		//----------------------------------------------------------------------
		// 　プルダウン用タグ生成 (START)
		//----------------------------------------------------------------------
		$pulldownTag = '';
		$scheduleList = '';
		$classCount = 1;
		
		//プルダウンが1つだったらborder-bottomは付けない
		$addBorderLessClass = "";
		if($pulldownCount < 2){
			$addBorderLessClass = " borderless";	
		}
		
		//本日以降のみ表示（過去の予約は不可とする）
		if(strtotime($ym."-".$day) >= strtotime(date("Y-m-d"))){
			
			//プルダウンの数だけループ
			for($j = 0;$j<$pulldownCount;$j++,$classCount++){
	
				//----------------------------------------------------------------------
				// 　プルダウンリストデータを取得 (START)
				//----------------------------------------------------------------------
				$pulldownArray[$j] = file($pulldownFilePath[$j]);
				//----------------------------------------------------------------------
				// 　プルダウンリストデータを取得 (END)
				//----------------------------------------------------------------------
				
				if(count($pulldownArray[$j]) > 0){
					foreach($pulldownArray[$j] as $pulldownArrayKey => $pulldownArrayVal){
						$pulldownExp = explode(',',$pulldownArrayVal);
						if(strtotime($ym."-".$day) == strtotime($pulldownExp[0]) ){
							
							$pulldownTag .= "\n".'<div class="schedulePulldownList list'.$classCount.'_'.$pulldownExp[1].$addBorderLessClass.'">'.$timeArray[$j];
							//予約可能ボタンの表示・非表示処理
							$pulldownTag .= reservBtnProcess($pulldownExp,$ym,$day,$j);
							$pulldownTag .= '</div>';
							
							//プルダウンリスト毎にclassを付与（あくまでカスタマイズ用）
							$scheduleList = ' scheduleList'.$pulldownExp[1];
							break;
						}
					}
				}
			
			}
		}
		//----------------------------------------------------------------------
		// 　プルダウン用タグ生成 (END)
		//----------------------------------------------------------------------
		
		//表示内容を連結
		$dspTag = $pulldownTag.$commentTag;
		
		//祝日の判定
		$shukujituClass = '';
		foreach($shukujituArray as $val){
			if(strtotime($ym."-".$day) == strtotime($val)){
				$shukujituClass = ' shukujitu';
				break;
			}
		}
		
		//定休日の場合はclassを付与し指定背景色を反映
		$holidayFlag = '';
		foreach($closedArray as $val){
			if($youbi % 7 == $val){
				$scheduleCalendar .= sprintf('<td class="closed youbi_%d'.$shukujituClass.$scheduleList.'" style="background:'.$closedBg.'">%d'.$dspTag.'</td>'."\n",$youbi % 7, $day);
				$holidayFlag = 1;
				break;
			}
		}
		
		//休業日の場合はclassを付与し指定背景色を反映
		if($holidayFlag != 1){
			foreach($holidayArray as $val){
				if(strtotime($ym."-".$day) == strtotime($val)){
					$scheduleCalendar .= sprintf('<td class="holiday youbi_%d'.$shukujituClass.$scheduleList.'" style="background:'.$holidayBg.'">%d'.$dspTag.'</td>'."\n",$youbi % 7, $day);
					$holidayFlag = 1;
					break;
				}
			}
		}
		
		if($holidayFlag != 1){
			//本日の場合はclassを付与
			if(strtotime($ym."-".$day) == strtotime(date("Y-m-d")) && $todayFlag == 1){
				$scheduleCalendar .= sprintf('<td class="today youbi_%d'.$shukujituClass.$scheduleList.'" style="background:'.$todayFlagBg.'">%d'.$dspTag.'</td>'."\n",$youbi % 7, $day);
			}
			//デフォルト
			else{
				$scheduleCalendar .= sprintf('<td class="youbi_%d'.$shukujituClass.$scheduleList.'">%d'.$dspTag.'</td>'."\n",$youbi % 7, $day);
			}
		}
		//土曜で行を変える
		if($youbi % 7 == 6){
			$scheduleCalendar .= "</tr><tr>";
		}
		//最終日以降空セル埋め
		if($day == $lastDay){
			$scheduleCalendar .= str_repeat('<td class="blankCell"></td>',(6 - $lastYoubi));
		}
	}
	$scheduleCalendar .= "</tr>\n";
	$scheduleCalendar .= "</table>\n";
	$scheduleCalendar = str_replace('<tr></tr>','',$scheduleCalendar);
	
	return $scheduleCalendar;
}
//カレンダー生成（一般ユーザー向け表示用）PC用リスト形式
function scheduleCalenderList($ym,$timeStamp){
global $todayFlag,$todayFlagBg,$filePath,$dispMonth,$holidayFilePath,$flagHiddenPrev,$closedFilePath,$closedBg,$holidayBg,$commentFilePath,$weekArray,$closedText,$scheList,$pulldownFilePath,$pulldownCount,$timeArray;
	$scheduleCalendar = '';
	
	//休業日データ取得
	$holidayArray = file($filePath);
	//祝日データを読み込み
	$shukujituArray = file_exists($holidayFilePath) ? file($holidayFilePath) : array();
	//定休日データの取得
	$closedArray = file($closedFilePath);
	//コメントデータを取得
	$commentArray = file($commentFilePath);
	//今月、来月
	$prev = date("Y-m",mktime(0,0,0,date("m",$timeStamp)-1,1,date("Y",$timeStamp)));
	$next = date("Y-m",mktime(0,0,0,date("m",$timeStamp)+1,1,date("Y",$timeStamp)));
	
	$dspPrev = '<a href="?ym='.$prev.'">&laquo;前月</a>';//前月へのナビ
	
	if((strtotime($prev.'-01') < strtotime(date("Y-m-01",mktime(0,0,0,date("m")-$dispMonth,1,date("Y"))))) || ($flagHiddenPrev == 0 && strtotime($ym.'-01') <= strtotime(date('Y-m-01')))){
		$dspPrev = '';
	}
	
	$dspNext = '<a href="?ym='.$next.'">翌月&raquo;</a>';//翌月へのナビ
	
	if(strtotime($next.'-01') > strtotime(date("Y-m-01",mktime(0,0,0,date("m")+$dispMonth,1,date("Y"))))){
		$dspNext = '';
	}
	
	//NextPrevナビセット
	$navNextPrev = '
	<table class="navNextPrev">
	<tr><td class="dspPrev">'.$dspPrev.'</td><td class="dspNext">'.$dspNext.'</td></tr>
	</table>
	';
	
	//ヘッダ部の年月
	$scheduleCalendar .= '<h2 id="headerYm">'.date("Y",$timeStamp) . "年" . date("n",$timeStamp). "月".'</h2>';
	
	//NextPrevナビセットを出力
	$scheduleCalendar .= $navNextPrev;
	
	//リスト形式の場合には休業日テキストを上部にも表示
	$scheduleCalendar .= '<p class="holidayText">';
	if($closedText){
		$scheduleCalendar .= $closedText;
	}
	$scheduleCalendar .= '<span class="holidayCube" style="background:'.$holidayBg.'"></span>休業日</p>';

	//月末
	$lastDay = date("t", $timeStamp);
	
	//1日の曜日
	$youbi = date("w",mktime(0,0,0,date("m",$timeStamp),1,date("Y",$timeStamp)));
	
	//最終日の曜日
	$lastYoubi = date("w",mktime(0,0,0,date("m",$timeStamp)+1,0,date("Y",$timeStamp)));
	
	$scheduleCalendar .= '<ul id="calenderList">';
	
	for($day = 1; $day <= $lastDay; $day++,$youbi++){
		
		$weeekText = '（'.$weekArray[($youbi % 7)].'）';
		//----------------------------------------------------------------------
		// 　コメント用タグ生成 (START)
		//----------------------------------------------------------------------
		$commentTag = '';
		if(count($commentArray) > 0){
			foreach($commentArray as $commentArrayVal){
				$commentArrayExp = explode(',',$commentArrayVal);
				if(strtotime($ym."-".$day) == strtotime($commentArrayExp[0]) ){
					$commentTag = "\n".'<div class="scheduleComment">'.rtrim($commentArrayExp[1]).'</div>';
					break;
				}
			}
		}
		//----------------------------------------------------------------------
		// 　コメント用タグ生成 (END)
		//----------------------------------------------------------------------
		
		//----------------------------------------------------------------------
		// 　プルダウン用タグ生成 (START)
		//----------------------------------------------------------------------
		$pulldownTag = '';
		$scheduleList = '';
		$classCount = 1;
		
		//プルダウンが1つだったらborder-bottomは付けない
		$addBorderLessClass = "";
		if($pulldownCount < 2){
			$addBorderLessClass = " borderless";	
		}
		
		//本日以降のみ表示（過去の予約は不可とする）
		if(strtotime($ym."-".$day) >= strtotime(date("Y-m-d"))){
			
			for($j = 0;$j<$pulldownCount;$j++,$classCount++){
			
			//----------------------------------------------------------------------
			// 　プルダウンリストデータを取得 (START)
			//----------------------------------------------------------------------
			$pulldownArray[$j] = file($pulldownFilePath[$j]);
			//----------------------------------------------------------------------
			// 　プルダウンリストデータを取得 (END)
			//----------------------------------------------------------------------
				if(count($pulldownArray[$j]) > 0){
					foreach($pulldownArray[$j] as $pulldownArrayKey => $pulldownArrayVal){
						$pulldownExp = explode(',',$pulldownArrayVal);
						if(strtotime($ym."-".$day) == strtotime($pulldownExp[0]) ){
							
							$pulldownTag .= "\n".'<div class="schedulePulldownList list'.$classCount.'_'.$pulldownExp[1].$addBorderLessClass.'">'.$timeArray[$j];
							//予約可能ボタンの表示・非表示処理
							$pulldownTag .= reservBtnProcess($pulldownExp,$ym,$day,$j);
							$pulldownTag .= '</div>';
							
							
							//プルダウンリスト毎にclassを付与（あくまでカスタマイズ用）
							$scheduleList = ' scheduleList'.$pulldownExp[1];
							break;
						}
					}
				}
			}
		}
		//----------------------------------------------------------------------
		// 　プルダウン用タグ生成 (END)
		//----------------------------------------------------------------------
		
		//表示内容を連結
		$dspTag = $weeekText.$pulldownTag.$commentTag;
		
		//1日にclass追加
		$addClass = '';
		if($day == 1){
			$addClass = ' first-child';
		}
		
		//祝日の判定
		$shukujituClass = '';
		foreach($shukujituArray as $val){
			if(strtotime($ym."-".$day) == strtotime($val)){
				$shukujituClass = ' shukujitu';
				break;
			}
		}
		
		//定休日の場合はclassを付与し指定背景色を反映
		$holidayFlag = '';
		foreach($closedArray as $val){
			if($youbi % 7 == $val){
				$scheduleCalendar .= sprintf('<li class="closed youbi_%d'.$shukujituClass.$addClass.$scheduleList.'" style="background:'.$closedBg.'">%d'.$dspTag.'</li>'."\n",$youbi % 7, $day);
				$holidayFlag = 1;
				break;
			}
		}
		
		//休業日の場合はclassを付与し指定背景色を反映
		if($holidayFlag != 1){
			foreach($holidayArray as $val){
				if(strtotime($ym."-".$day) == strtotime($val)){
					$scheduleCalendar .= sprintf('<li class="holiday youbi_%d'.$shukujituClass.$addClass.$scheduleList.'" style="background:'.$holidayBg.'">%d'.$dspTag.'</li>'."\n",$youbi % 7, $day);
					$holidayFlag = 1;
					break;
				}
			}
		}
		
		if($holidayFlag != 1){
			//本日の場合はclassを付与
			if(strtotime($ym."-".$day) == strtotime(date("Y-m-d")) && $todayFlag == 1){
				$scheduleCalendar .= sprintf('<li class="today youbi_%d'.$shukujituClass.$addClass.$scheduleList.'" style="background:'.$todayFlagBg.'">%d'.$dspTag.'</li>'."\n",$youbi % 7, $day);
			}
			//デフォルト
			else{
				$scheduleCalendar .= sprintf('<li class="youbi_%d'.$shukujituClass.$addClass.$scheduleList.'">%d'.$dspTag.'</li>'."\n",$youbi % 7, $day);
			}
		}
	}
	$scheduleCalendar .= '</ul>';
	
	//NextPrevナビセットを出力
	$scheduleCalendar .= $navNextPrev;
	
	return $scheduleCalendar;
}

//PCの表示形式の判定（関数切り替え処理）
function scheduleCalenderPc($ym,$timeStamp,$copyright =''){
	global $dspCalender,$warningMesse;
	if(empty($copyright)) {
		exit($warningMesse);
	}elseif($dspCalender == 1){
		$res = scheduleCalenderList($ym,$timeStamp);
	}else{
		$res = scheduleCalenderTable($ym,$timeStamp);
	}
	return $res;
}

//カレンダー生成（一般ユーザー向け表示用）スマホ用
function scheduleCalenderSp($ym,$timeStamp){
global $todayFlag,$todayFlagBg,$filePath,$dispMonth,$holidayFilePath,$flagHiddenPrev,$closedFilePath,$closedBg,$holidayBg,$commentFilePath,$weekArray,$scheList,$pulldownFilePath,$pulldownCount,$timeArray;
	$scheduleCalendar['body'] = '';
	
	//休業日データ取得
	$holidayArray = file($filePath);
	//祝日データを読み込み
	$shukujituArray = file_exists($holidayFilePath) ? file($holidayFilePath) : array();
	//定休日データの取得
	$closedArray = file($closedFilePath);
	//コメントデータを取得
	$commentArray = file($commentFilePath);
	//今月、来月
	$prev = date("Y-m",mktime(0,0,0,date("m",$timeStamp)-1,1,date("Y",$timeStamp)));
	$next = date("Y-m",mktime(0,0,0,date("m",$timeStamp)+1,1,date("Y",$timeStamp)));
	
	$scheduleCalendar['dspPrev'] = $prev;//前月へのナビ
	
	if((strtotime($prev.'-01') < strtotime(date("Y-m-01",mktime(0,0,0,date("m")-$dispMonth,1,date("Y"))))) || ($flagHiddenPrev == 0 && strtotime($ym.'-01') <= strtotime(date('Y-m-01')))){
		$scheduleCalendar['dspPrev'] = '';
	}
	
	$scheduleCalendar['dspNext'] = $next;//翌月へのナビ
	
	if(strtotime($next.'-01') > strtotime(date("Y-m-01",mktime(0,0,0,date("m")+$dispMonth,1,date("Y"))))){
		$scheduleCalendar['dspNext'] = '';
	}
	
	//ヘッダ部の年月
	$scheduleCalendar['calnderHeaderYm'] = date("Y",$timeStamp) . "年" . date("n",$timeStamp). "月";

	//月末
	$lastDay = date("t", $timeStamp);
	
	//1日の曜日
	$youbi = date("w",mktime(0,0,0,date("m",$timeStamp),1,date("Y",$timeStamp)));
	
	//最終日の曜日
	$lastYoubi = date("w",mktime(0,0,0,date("m",$timeStamp)+1,0,date("Y",$timeStamp)));
	
	for($day = 1; $day <= $lastDay; $day++,$youbi++){
		
		$weeekText = '（'.$weekArray[($youbi % 7)].'）';
		//----------------------------------------------------------------------
		// 　コメント用タグ生成 (START)
		//----------------------------------------------------------------------
		$commentTag = '';
		if(count($commentArray) > 0){
			foreach($commentArray as $commentArrayVal){
				$commentArrayExp = explode(',',$commentArrayVal);
				if(strtotime($ym."-".$day) == strtotime($commentArrayExp[0]) ){
					$commentTag = "\n".'<div class="scheduleComment">'.rtrim($commentArrayExp[1]).'</div>';
					break;
				}
			}
		}
		//----------------------------------------------------------------------
		// 　コメント用タグ生成 (END)
		//----------------------------------------------------------------------
		
		//----------------------------------------------------------------------
		// 　プルダウン用タグ生成 (START)
		//----------------------------------------------------------------------
		$pulldownTag = '';
		$scheduleList = '';
		$classCount = 1;
		
		//プルダウンが1つだったらborder-bottomは付けない
		$addBorderLessClass = "";
		if($pulldownCount < 2){
			$addBorderLessClass = " borderless";	
		}
		//本日以降のみ表示（過去の予約は不可とする）
		if(strtotime($ym."-".$day) >= strtotime(date("Y-m-d"))){
			for($j = 0;$j<$pulldownCount;$j++,$classCount++){
			
			//----------------------------------------------------------------------
			// 　プルダウンリストデータを取得 (START)
			//----------------------------------------------------------------------
			$pulldownArray[$j] = file($pulldownFilePath[$j]);
			//----------------------------------------------------------------------
			// 　プルダウンリストデータを取得 (END)
			//----------------------------------------------------------------------
				if(count($pulldownArray[$j]) > 0){
					foreach($pulldownArray[$j] as $pulldownArrayKey => $pulldownArrayVal){
						$pulldownExp = explode(',',$pulldownArrayVal);
						if(strtotime($ym."-".$day) == strtotime($pulldownExp[0]) ){
							
							$pulldownTag .= "\n".'<div class="schedulePulldownList list'.$classCount.'_'.$pulldownExp[1].$addBorderLessClass.'">'.$timeArray[$j];
							//予約可能ボタンの表示・非表示処理（第五引数はスマホ判定用→formタグにajax回避追記）
							$pulldownTag .= reservBtnProcess($pulldownExp,$ym,$day,$j,'sp');
							$pulldownTag .= '</div>';
							
							//$pulldownTag .= "\n".'<div class="schedulePulldownList list'.$classCount.'_'.$pulldownExp[1].'">'.$scheList[$j][$pulldownExp[1]].'</div>';
							//プルダウンリスト毎にclassを付与（あくまでカスタマイズ用）
							$scheduleList = ' scheduleList'.$pulldownExp[1];
							break;
						}
					}
				}
			}
		}
		
		//----------------------------------------------------------------------
		// 　プルダウン用タグ生成 (END)
		//----------------------------------------------------------------------
		
		//表示内容を連結
		$dspTag = $weeekText.$pulldownTag.$commentTag;
		
		
		//祝日の判定
		$shukujituClass = '';
		foreach($shukujituArray as $val){
			if(strtotime($ym."-".$day) == strtotime($val)){
				$shukujituClass = ' shukujitu';
				break;
			}
		}
		
		//定休日の場合はclassを付与し指定背景色を反映
		$holidayFlag = '';
		foreach($closedArray as $val){
			if($youbi % 7 == $val){
				$scheduleCalendar['body'] .= sprintf('<li class="closed youbi_%d'.$shukujituClass.$scheduleList.'" style="background:'.$closedBg.'">%d'.$dspTag.'</li>'."\n",$youbi % 7, $day);
				$holidayFlag = 1;
				break;
			}
		}
		
		//休業日の場合はclassを付与し指定背景色を反映
		if($holidayFlag != 1){
			foreach($holidayArray as $val){
				if(strtotime($ym."-".$day) == strtotime($val)){
					$scheduleCalendar['body'] .= sprintf('<li class="holiday youbi_%d'.$shukujituClass.$scheduleList.'" style="background:'.$holidayBg.'">%d'.$dspTag.'</li>'."\n",$youbi % 7, $day);
					$holidayFlag = 1;
					break;
				}
			}
		}
		
		if($holidayFlag != 1){
			//本日の場合はclassを付与
			if(strtotime($ym."-".$day) == strtotime(date("Y-m-d")) && $todayFlag == 1){
				$scheduleCalendar['body'] .= sprintf('<li class="today youbi_%d'.$shukujituClass.$scheduleList.'" style="background:'.$todayFlagBg.'">%d'.$dspTag.'</li>'."\n",$youbi % 7, $day);
			}
			//デフォルト
			else{
				$scheduleCalendar['body'] .= sprintf('<li class="youbi_%d'.$shukujituClass.$scheduleList.'">%d'.$dspTag.'</li>'."\n",$youbi % 7, $day);
			}
		}
	}
	
	return $scheduleCalendar;
}
//カレンダー生成（一般ユーザー向け表示用）ガラケー用
function scheduleCalenderMb($ym,$timeStamp){
global $todayFlag,$todayFlagBg,$filePath,$dispMonth,$holidayFilePath,$flagHiddenPrev,$closedFilePath,$closedBg,$holidayBg,$commentFilePath,$weekArray,$scheList,$pulldownFilePath,$pulldownCount,$timeArray;
	$scheduleCalendar['body'] = '';
	
	//休業日データ取得
	$holidayArray = file($filePath);
	//祝日データを読み込み
	$shukujituArray = file_exists($holidayFilePath) ? file($holidayFilePath) : array();
	//定休日データの取得
	$closedArray = file($closedFilePath);
	//コメントデータを取得
	$commentArray = file($commentFilePath);
	//今月、来月
	$prev = date("Y-m",mktime(0,0,0,date("m",$timeStamp)-1,1,date("Y",$timeStamp)));
	$next = date("Y-m",mktime(0,0,0,date("m",$timeStamp)+1,1,date("Y",$timeStamp)));
	
	$scheduleCalendar['dspPrev'] = $prev;//前月へのナビ
	
	if((strtotime($prev.'-01') < strtotime(date("Y-m-01",mktime(0,0,0,date("m")-$dispMonth,1,date("Y"))))) || ($flagHiddenPrev == 0 && strtotime($ym.'-01') <= strtotime(date('Y-m-01')))){
		$scheduleCalendar['dspPrev'] = '';
	}
	
	$scheduleCalendar['dspNext'] = $next;//翌月へのナビ
	
	if(strtotime($next.'-01') > strtotime(date("Y-m-01",mktime(0,0,0,date("m")+$dispMonth,1,date("Y"))))){
		$scheduleCalendar['dspNext'] = '';
	}
	
	//ヘッダ部の年月
	$scheduleCalendar['calnderHeaderYm'] = date("Y",$timeStamp) . "年" . date("n",$timeStamp). "月";

	//月末
	$lastDay = date("t", $timeStamp);
	
	//1日の曜日
	$youbi = date("w",mktime(0,0,0,date("m",$timeStamp),1,date("Y",$timeStamp)));
	
	//最終日の曜日
	$lastYoubi = date("w",mktime(0,0,0,date("m",$timeStamp)+1,0,date("Y",$timeStamp)));
	
	for($day = 1; $day <= $lastDay; $day++,$youbi++){
		
		$weeekText = '（'.$weekArray[($youbi % 7)].'）';
		//----------------------------------------------------------------------
		// 　コメント用タグ生成 (START)
		//----------------------------------------------------------------------
		$commentTag = '';
		if(count($commentArray) > 0){
			foreach($commentArray as $commentArrayVal){
				$commentArrayExp = explode(',',$commentArrayVal);
				if(strtotime($ym."-".$day) == strtotime($commentArrayExp[0]) ){
					$commentTag = '<div style="font-size:xx-small;text-align:left;color:#555555">'.rtrim($commentArrayExp[1]).'</div>';
					break;
				}
			}
		}
		//----------------------------------------------------------------------
		// 　コメント用タグ生成 (END)
		//----------------------------------------------------------------------
		
		//----------------------------------------------------------------------
		// 　プルダウン用タグ生成 (START)
		//----------------------------------------------------------------------
		$pulldownTag = '';
		$classCount = 1;
		
		//本日以降のみ表示（過去の予約は不可とする）
		if(strtotime($ym."-".$day) >= strtotime(date("Y-m-d"))){
			
			for($j = 0;$j<$pulldownCount;$j++,$classCount++){
			
				//----------------------------------------------------------------------
				// 　プルダウンリストデータを取得 (START)
				//----------------------------------------------------------------------
				$pulldownArray[$j] = file($pulldownFilePath[$j]);
				//----------------------------------------------------------------------
				// 　プルダウンリストデータを取得 (END)
				//----------------------------------------------------------------------
				if(count($pulldownArray[$j]) > 0){
					foreach($pulldownArray[$j] as $pulldownArrayKey => $pulldownArrayVal){
						$pulldownExp = explode(',',$pulldownArrayVal);
						if(strtotime($ym."-".$day) == strtotime($pulldownExp[0]) ){
							
							$pulldownTag .= "\n".'<div class="schedulePulldownList list'.$classCount.'_'.$pulldownExp[1].'" style="font-size:small;text-align:left;color:#555555">'.$timeArray[$j];
							//予約可能ボタンの表示・非表示処理
							$pulldownTag .= reservBtnProcess($pulldownExp,$ym,$day,$j);
							$pulldownTag .= '</div><br />';
							
							//$pulldownTag .= '<div class="schedulePulldownList list'.$classCount.'_'.$pulldownExp[1].'" style="font-size:xx-small;text-align:left;color:#555555">'.$scheList[$j][$pulldownExp[1]].'</div>'."\n";
							//プルダウンリスト毎にclassを付与（あくまでカスタマイズ用）
							$scheduleList = ' scheduleList'.$pulldownExp[1];
							break;
						}
					}
				}
			}
		}
		
		//----------------------------------------------------------------------
		// 　プルダウン用タグ生成 (END)
		//----------------------------------------------------------------------
		
		//表示内容を連結
		$dspTag = $weeekText.$pulldownTag.$commentTag;
		
		//祝日の判定
		$shukujituClass = '';
		foreach($shukujituArray as $val){
			if(strtotime($ym."-".$day) == strtotime($val)){
				$shukujituClass = ' shukujitu';
				break;
			}
		}
		
		//----------------------------------------------------------------------
		// 　携帯版独自処理 (START)
		//----------------------------------------------------------------------
		//文字色をセット
		$mobileTextColor = '';
		
		if($youbi % 7 == 0 || $shukujituClass == ' shukujitu'){
			$mobileTextColor = 'red';
		}elseif($youbi % 7 == 6){
			$mobileTextColor = '#3366FF';
		}
		//----------------------------------------------------------------------
		// 　携帯版独自処理 (END)
		//----------------------------------------------------------------------
		
		//定休日の場合はclassを付与し指定背景色を反映
		$holidayFlag = '';
		foreach($closedArray as $val){
			if($youbi % 7 == $val){
				$scheduleCalendar['body'] .= sprintf('<div class="youbi_%d" bgcolor="'.$closedBg.'" style="background:'.$closedBg.'"><font color="'.$mobileTextColor.'">%d'.$dspTag.'</font></div><hr />',$youbi % 7, $day);
				$holidayFlag = 1;
				break;
			}
		}
		
		//休業日の場合はclassを付与し指定背景色を反映
		if($holidayFlag != 1){
			foreach($holidayArray as $val){
				if(strtotime($ym."-".$day) == strtotime($val)){
					$scheduleCalendar['body'] .= sprintf('<div class="youbi_%d" bgcolor="'.$holidayBg.'" style="background:'.$holidayBg.'"><font color="'.$mobileTextColor.'">%d'.$dspTag.'</font></div><hr />',$youbi % 7, $day);
					$holidayFlag = 1;
					break;
				}
			}
		}
		
		if($holidayFlag != 1){
			//本日の場合はclassを付与
			if(strtotime($ym."-".$day) == strtotime(date("Y-m-d")) && $todayFlag == 1){
				$scheduleCalendar['body'] .= sprintf('<div class="youbi_%d" bgcolor="'.$todayFlagBg.'" style="background:'.$todayFlagBg.'"><font color="'.$mobileTextColor.'">%d'.$dspTag.'</font></div><hr />',$youbi % 7, $day);
			}
			//デフォルト
			else{
				$scheduleCalendar['body'] .= sprintf('<div class="youbi_%d"><font color="'.$mobileTextColor.'">%d'.$dspTag.'</font></div><hr />',$youbi % 7, $day);
			}
		}
	}
	
	return $scheduleCalendar;
}

//カレンダー生成（管理画面用）
function scheduleCalenderAdmin(){
global $todayFlag,$todayFlagBg,$filePath,$adminDispMonth,$holidayFilePath,$holidayBg,$closedFilePath,$closedBg,$holidayBg,$commentFilePath,$weekArray,$scheList,$pulldownFilePath,$pulldownCount,$timeArray,$reservCount,$reservCountNum,$reservFileDir;

	//パラメータをセット
	$getYm = date('Y-m');
	if(isset($_GET['ym'])){
		$getYm = $_GET['ym'];
	}
	
	//休日データ取得
	$holidayArray = file($filePath);
	
	//祝日データの取得
	$shukujituArray = file_exists($holidayFilePath) ? file($holidayFilePath) : array();
		
	//定休日データの取得
	$closedArray = file($closedFilePath);
	
	
	
	//----------------------------------------------------------------------
	// 　コメントデータを取得 (START)
	//----------------------------------------------------------------------
	$commentArray = file($commentFilePath);
	//----------------------------------------------------------------------
	// 　コメントデータを取得 (END)
	//----------------------------------------------------------------------
	
	$scheduleCalendar = '<form action="?ym='.$getYm.'" method="post">';
	
	//スタート年月をセット
	//$startYmd = date("Y-m-d",mktime(0,0,0,date("m")-$adminDispMonth,1,date("Y")));
	//過去は1ヶ月だけでOK
	//$startYmd = date("Y-m-d",mktime(0,0,0,date("m")-1,1,date("Y")));
	//$startYmd = date("Y-m-d",mktime(0,0,0,date("m"),1,date("Y")));
	
	$startYmd = date($getYm."-d",mktime(0,0,0,date("m"),1,date("Y")));
	
	//カレンダーのループ（当月分があるので+1とした）
	//$dispMonthLoop = $adminDispMonth * 2 + 1;
	//$dispMonthLoop = $adminDispMonth + 2;
	//for($i = 0;$i < $dispMonthLoop;$i++){
	
	//当月のみ表示するよう修正。PHP5.3.9以降でPOSTがMAX1000（デフォルト）に設定されたため 2014/8/25
	for($i = 0;$i < 1;$i++){
	
		$timeStamp = strtotime("+$i month",strtotime($startYmd));
		
		$ym = date("Y-m",$timeStamp);
		
		//今月、来月
		$prev = date("Y-m",mktime(0,0,0,date("m",$timeStamp)-1,1,date("Y",$timeStamp)));
		$next = date("Y-m",mktime(0,0,0,date("m",$timeStamp)+1,1,date("Y",$timeStamp)));
		
		$calenderClass = 'hidden';
		if($getYm == $ym){
			$calenderClass = 'calenderClassAdmin';
		}
		$calenderClass = 'calenderClassAdmin';
		
		$nextClass = '';	
		if($i == ($adminDispMonth + 1)  ){
			//$nextClass = 'hidden';
		}
		
		$prevClass = '';
		if($i == 0 ){
			//$prevClass = 'hidden';
		}

$scheduleCalendar .= '<table id="calenderTableAdmin-'.$ym.'" class="'.$calenderClass.'">
<tr><th><a href="?ym='.$prev.'" class="'.$prevClass.'">&laquo;'.date("n",strtotime($prev.'-01')).'月</a></th><th colspan="5">'.date("Y",$timeStamp) . "年" . date("n",$timeStamp). "月" .'</th><th><a href="?ym='.$next.'" class="'.$nextClass.'">&raquo;'.date("n",strtotime($next.'-01')).'月</a></th></tr><tr><th class="youbi_0">'.$weekArray[0].'</th><th>'.$weekArray[1].'</th><th>'.$weekArray[2].'</th><th>'.$weekArray[3].'</th><th>'.$weekArray[4].'</th><th>'.$weekArray[5].'</th><th class="youbi_6">'.$weekArray[6].'</th></tr><tr>';
		
		//今月末
		$lastDay = date("t", $timeStamp);
		
		//1日の曜日
		$youbi = date("w",mktime(0,0,0,date("m",$timeStamp),1,date("Y",$timeStamp)));
		
		//最終日の曜日
		$lastYoubi = date("w",mktime(0,0,0,date("m",$timeStamp)+1,0,date("Y",$timeStamp)));
		
		$scheduleCalendar .= str_repeat('<td></td>',$youbi);
		
		for($day = 1; $day <= $lastDay; $day++,$youbi++){
			
			$selectChangeBtnDay = '
			<div class="selectChangeDay" id="selectChangeBtnDay01_'.$day.'"><a href="javascript:void(0)" id="selectChangeDay01_'.$day.'">全て受付中にする</a></div>
			<div class="selectChangeDay" id="selectChangeBtnDay02_'.$day.'" style="display:none"><a href="javascript:void(0)" id="selectChangeDay02_'.$day.'">全て未選択にする</a></div>';
			
			//----------------------------------------------------------------------
			// 　コメント用タグ生成 (START)
			//----------------------------------------------------------------------
			$commentTag = '<div class="adminTextArea">補足、コメントなど<br /><textarea name="comment['.date("Y-m-d",strtotime($ym."-".$day)).']" rows="2" cols="5"></textarea></div>';
			
			if(count($commentArray) > 0){
				foreach($commentArray as $commentArrayVal){
					$commentArrayExp = explode(',',$commentArrayVal);
					if(strtotime($ym."-".$day) == strtotime($commentArrayExp[0]) ){
						$commentTag = '<div class="adminTextArea">補足、コメントなど<br /><textarea name="comment['.date("Y-m-d",strtotime($ym."-".$day)).']" rows="2" cols="5">'.str_replace(array('<br />','<br>'),"\n",rtrim($commentArrayExp[1])).'</textarea></div>';
					}
				}
			}
			//----------------------------------------------------------------------
			// 　コメント用タグ生成 (END)
			//----------------------------------------------------------------------
			
			
			//----------------------------------------------------------------------
			// 　プルダウン用タグ生成 (START)
			//----------------------------------------------------------------------
			//$pulldownTag = '';
			$tempPulldownTag = '';
			//プルダウンの数だけループ
			for($j = 0;$j<$pulldownCount;$j++){
			//----------------------------------------------------------------------
			// 　プルダウンリストデータを取得 (START)
			//----------------------------------------------------------------------
			$pulldownArray[$j] = file($pulldownFilePath[$j]);
			//----------------------------------------------------------------------
			// 　プルダウンリストデータを取得 (END)
			//----------------------------------------------------------------------
			
				$pulldownTag = '<div class="adminPullDown">'.$timeArray[$j].'<select class="pulldown_'.$day.'" name="pulldown['.$j.']['.date("Y-m-d",strtotime($ym."-".$day)).']">'."\n";
				$pulldownTag .= '<option value="">未選択</option>'."\n";
				
				foreach($scheList[$j] as $scheListKey => $scheListVal){
						$pulldownTag .= '<option value="'.$scheListKey.'">'.$scheListVal.'</option>'."\n";
				}
				$pulldownTag .= '</select>';
					
					
				$countPulldown = count($pulldownArray[$j]);
				$pulldownStatus = "";
				if($countPulldown > 0){
					
					foreach($pulldownArray[$j] as $pulldownArrayVal){
						$pulldownArrayExp = explode(',',$pulldownArrayVal);
						if(strtotime($ym."-".$day) == strtotime($pulldownArrayExp[0]) ){
							$pulldownTag = '<div class="adminPullDown">'.$timeArray[$j].'<select class="pulldown_'.$day.'" name="pulldown['.$j.']['.date("Y-m-d",strtotime($ym."-".$day)).']">'."\n";
							$pulldownTag .= '<option value="">未選択</option>'."\n";
							
							foreach($scheList[$j] as $scheListKey => $scheListVal){
									
									if($pulldownArrayExp[1] == $scheListKey){
										$pulldownTag .= '<option value="'.$scheListKey.'" selected="selected">'.$scheListVal.'</option>'."\n";
										
										//現在のプルダウンの状態を取得（「ボタン非表示中」のテキストを表示するため）
										$pulldownStatus = $pulldownArrayExp[1];
										
									}else{
										$pulldownTag .= '<option value="'.$scheListKey.'">'.$scheListVal.'</option>'."\n";
									}
							}
							$pulldownTag .= '</select>';
						}
					}
				}
				
				//----------------------------------------------------------------------
				// 　予約可能数処理 (START)
				//----------------------------------------------------------------------
				if($reservCount == 1){
					
					
					//定義予約可能数
					$remainingReserv = $reservCountNum;
					
					//現在の予約数取得
					$reservFilePath = $reservFileDir."/".date("Y-n-j",strtotime($ym."-".$day))."-".$j.".dat";
					
					//予約カウント用データが存在する場合のみデータを取得、カウントし、残り数を計算
					if(file_exists($reservFilePath)){
						
						$fp = fopen($reservFilePath, "rb") or die("fopen Error!!");
						
						$getReservLines = fgets($fp);
						
						
						//残り予約可能数計算
						$remainingReserv = $reservCountNum - trim($getReservLines);
					}
					
					//残り予約可能数がゼロだったら文字色を変更する
					
					if($remainingReserv < 1 || (!empty($pulldownStatus) && $pulldownStatus > 1)){
						$addClassAdminReservCount = " reservStatesFull";
						$textReservCount = "予約終了";	
					}else{
						$addClassAdminReservCount = '';
						$textReservCount = "残り予約可能数";	
					}
					
					
					$pulldownTag .= '<div class="adminReservCount'.$addClassAdminReservCount.'">'.$textReservCount.'<br /><select name="reserv['.$j.']['.date("Y-m-d",strtotime($ym."-".$day)).']" onchange="oncheck(\'reserv'.$ym."_".$day."_".$j.'\');">'."\n";
					
					//予約可能数プルダウン表示
					for($reserv_i = 0;$reserv_i <= $reservCountNum;$reserv_i++){
						
						if($remainingReserv == $reserv_i){
							$pulldownTag .= '<option value="'.$reserv_i.'" selected="selected">'.$reserv_i.'</option>';
						}else{
							$pulldownTag .= '<option value="'.$reserv_i.'">'.$reserv_i.'</option>';
						}
							
					}
					
					
					$pulldownTag .= '</select>';
					$pulldownTag .= '<input type="checkbox" name="reservChange['.$j.']['.date("Y-m-d",strtotime($ym."-".$day)).']" value="true" id="reserv'.$ym."_".$day."_".$j.'" /><label for="reserv'.$ym."_".$day."_".$j.'">変更する</label>'."\n";
					
					$pulldownTag .= '</div>'."\n\n";
					
				
				}
				//----------------------------------------------------------------------
				// 　予約可能数処理 (END)
				//----------------------------------------------------------------------
				
				$pulldownTag .= '</div>'."\n";
				
				$tempPulldownTag .= $pulldownTag;
			}
			
			$pulldownTag  = '';
			$pulldownTag = $tempPulldownTag;
			//echo $pulldownTag;
			//----------------------------------------------------------------------
			// 　プルダウン用タグ生成 (END)
			//----------------------------------------------------------------------
			
			
			$inputText01 = '<br /><span style="font-size:11px;color:#666;">休業日チェック</span><input type="checkbox" name="holiday_set[]" value="'.date("Y-m-d",strtotime($ym."-".$day)).'" checked />'.$selectChangeBtnDay.'<hr style="margin:5px 0 0">'.$pulldownTag.$commentTag;
			$inputText02 = '<br /><span style="font-size:11px;color:#666;">休業日チェック</span><input type="checkbox" name="holiday_set[]" value="'.date("Y-m-d",strtotime($ym."-".$day)).'" />'.$selectChangeBtnDay.'<hr style="margin:5px 0 0">'.$pulldownTag.$commentTag;
			
			//祝日の判定
			$shukujituClass = '';
			foreach($shukujituArray as $val){
				if(strtotime($ym."-".$day) == strtotime($val)){
					$shukujituClass = ' shukujitu';
					break;
				}
			}
			
			//定休日の場合はclassを付与し背景色を反映
			$holidayFlag = '';
			foreach($closedArray as $val){
				if($youbi % 7 == $val){
					$scheduleCalendar .= sprintf('<td class="closed youbi_%d'.$shukujituClass.'" style="background:'.$closedBg.'">%d'.$inputText02.'</td>',$youbi % 7, $day);
					$holidayFlag = 1;
					break;
				}
			}
			
			//休業日の場合はclassを付与し背景色を反映＆checked付与
			if($holidayFlag != 1){
				foreach($holidayArray as $val){
					if(strtotime($ym."-".$day) == strtotime($val)){
						$scheduleCalendar .= sprintf('<td class="holiday youbi_%d'.$shukujituClass.'" style="background:'.$holidayBg.'">%d'.$inputText01.'</td>',$youbi % 7, $day);
						$holidayFlag = 1;
						break;
					}
				}
			}
			
			if($holidayFlag != 1){
			
				//本日の場合はclassを付与
				if(strtotime($ym."-".$day) == strtotime(date("Y-m-d")) && $todayFlag == 1){
					$scheduleCalendar .= sprintf('<td class="today youbi_%d'.$shukujituClass.'" style="background:'.$todayFlagBg.'">%d'.$inputText02.'</td>',$youbi % 7, $day);
				}
				//デフォルト
				else{
					$scheduleCalendar .= sprintf('<td class="youbi_%d'.$shukujituClass.'">%d'.$inputText02.'</td>',$youbi % 7, $day);
				}
			}
			//土曜で行を変える
			if($youbi % 7 == 6){
				$scheduleCalendar .= "</tr><tr>";
			}
			//最終日以降空セル埋め
			if($day == $lastDay){
				$scheduleCalendar .= str_repeat('<td class="blankCell"></td>',(6 - $lastYoubi));
			}
		}
		$scheduleCalendar .= "</tr>";
		$scheduleCalendar .= "</table>";
	}
		//定休日処理
		$scheduleCalendar .= "<h2>定休日設定</h2><p>毎週定休日が決まっている場合には該当する曜日にチェックを入れれば全期間で有効になります。（隔週の場合は↑でチェックして下さい）<br>";
		$youbi_array = array('日','月','火','水','木','金','土');
		$lines = file($closedFilePath);
		
		for($i = 0;$i<7;$i++){
			
			$chekedFlag = '';
			foreach($lines as $val){
				if($val == $i){
					$chekedFlag = ' checked';
				}
			}
			
			$scheduleCalendar .= '<input type="checkbox" name="closed[]" id="closed'.$i.'" value="'.$i.'"'.$chekedFlag.' /><label for="closed'.$i.'"> '.$youbi_array[$i].'</label>　';
		}
		
		$scheduleCalendar .= "<p align=\"center\"><input type=\"submit\" class=\"submitBtn\" value=\"　登録　\" name=\"holiday_submit\"></p>\n";
		$scheduleCalendar .= "</form>\n";
		$scheduleCalendar = str_replace('<tr></tr>','',$scheduleCalendar);
		
		return $scheduleCalendar;
}

function calf_copyright(){//無断削除禁止（改変を行うと一部または全機能が停止もしくはランダムで不具合が発生します）
	global $copyright;
	echo $copyright;
}
//GoogleカレンダーAPIから祝日を取得
function getHolidays($year) {
	global $apiKey;
	if(empty($apiKey)) exit('Googleから祝日取得用のGoogleカレンダーAPIキーがconfig.phpで設定されていません。Googleにて取得し、設定下さい。または設定ファイルでこの機能をOFFにするか、<a href="http://www.php-factory.net/calendar_form/01.php" target="_blank">当サイト</a>から祝日用のデータファイルをダウンロード下さい。');
	
	$holidays = array();
	$holidays_id = 'outid3el0qkcrsuf89fltf7a4qbacgt9@import.calendar.google.com'; // mozilla.org版
	//$holidays_id = 'japanese__ja@holiday.calendar.google.com'; // Google 公式版日本語
	//$holidays_id = 'japanese@holiday.calendar.google.com'; // Google 公式版英語
	$url = sprintf(
		'https://www.googleapis.com/calendar/v3/calendars/%s/events?'.
		'key=%s&timeMin=%s&timeMax=%s&maxResults=%d&orderBy=startTime&singleEvents=true',
		$holidays_id,
		$apiKey,
		$year.'-01-01T00:00:00Z' , // 取得開始日
		$year.'-12-31T00:00:00Z' , // 取得終了日
		150 // 最大取得数
	);
 
	if ( $results = file_get_contents($url, true )) {
		//JSON形式で取得した情報を配列に格納
		$results = json_decode($results);
		//年月日をキー、祝日名を配列に格納
		foreach ($results->items as $item ) {
			$date = strtotime((string) $item->start->date);
			$title = (string) $item->summary;
			$holidays[date('Y-m-d', $date)] = $title;
		}
		//祝日の配列を並び替え
		ksort($holidays);
	}
	return $holidays; 
}

//祝日取得、保存
function buildHoliDay($holidayFilePath){
		$messe ='';
		if($res = getHolidays(date("Y"))){
			//去年、今年、来年の祝日をGoogleから取得
			$holidaysPrevYear = getHolidays(date("Y",strtotime("-1 year")));
			$holidays = getHolidays(date("Y"));
			$holidaysNextYear = getHolidays(date("Y",strtotime("+1 year")));
			
			$fp = fopen($holidayFilePath, "w+b") or die("fopen Error!!");
			$holidaysWriteData = '';
			if (flock($fp, LOCK_EX)) {
				ftruncate($fp,0);
				rewind($fp);
				// 書き込み
				foreach($holidaysPrevYear as $key => $val){
					$holidaysWriteData .= $key."\n";
				}
				foreach($holidays as $key => $val){
					$holidaysWriteData .= $key."\n";
				}
				foreach($holidaysNextYear as $key => $val){
					$holidaysWriteData .= $key."\n";
				}
				
				fwrite($fp, $holidaysWriteData);
			}
			    fclose($fp);
		}else{
				$messe = 'GoogleカレンダーAPIから祝日データが取得できません。<br>Googleの仕様が変更になった可能性がありますので管理者にお問い合わせください。';
		}
	
	return $messe;
}
function Uqa4h78r(){
	global $copyright;echo $copyright;
}
function cffsg($warningMesse02,$cfilePath){
	if(filesize($cfilePath) != 415 && filesize($cfilePath) != 410 && filesize($cfilePath) != 122 && filesize($cfilePath) != 117) exit($warningMesse02);//ASCIIモードでの転送にも対応
}
//NULLバイト除去//
function calf_sanitize($arr){
	if(is_array($arr)){
		return array_map('calf_sanitize',$arr);
	}
	return str_replace("\0","",$arr);
}
//予約可能数データの保存（チェックされた日時のみ）
function reservCountReg($i,$reservCountNum,$reservFileDir){
	$reservWriteData = '';
	if(isset($_POST['reservChange'][$i])){
		foreach($_POST['reservChange'][$i] as $key => $val){
			$reservWriteData = $reservCountNum - $_POST['reserv'][$i][$key];
			$reservFilePath[$i] = $reservFileDir."/".date("Y-n-j",strtotime($key))."-".$i.".dat";
			
			$fp = fopen($reservFilePath[$i], "a+b") or die("fopen Error!!");
			// 俳他的ロック
			if (flock($fp, LOCK_EX)) {
				ftruncate($fp,0);
				rewind($fp);
				fwrite($fp, $reservWriteData);// 書き込み
			}
			fflush($fp);
			flock($fp, LOCK_UN);
			fclose($fp);
		}
		
	//古い予約数カウント用ファイルの削除（3ヶ月以上前のファイル）
	deleteReservCountFile($reservFileDir);
		
	}
}

//予約ボタンの表示処理（ALLデバイス共通）※スマホのみajax回避属性追記（第五引数）
function reservBtnProcess($pulldownExp,$ym,$day,$j,$device=""){
	global $reservCount,$reservCountNum,$reservFileDir,$pulldownListArray,$reservText,$setDspDate;
	
	$pulldownTag = '<div class="reservBtnWrap">';
	
	//予約可能状態の場合のみ予約ボタン表示処理
	$setDspDate = (isset($setDspDate)) ? $setDspDate :0;

	$getYmd = $ym."-".$day;
	$confYmd = date("Y-m-d",strtotime("+".$setDspDate." day"));
	
	if( $pulldownExp[1] == 1 && strtotime($getYmd) >= strtotime($confYmd) ){//中○日は予約終了ボタン表示（2014/9/24設定実装）
	
//	global $reservCount,$reservCountNum,$reservFileDir,$pulldownListArray,$reservText;
//	
//	$pulldownTag = '<div class="reservBtnWrap">';
//	//予約可能状態の場合のみ予約ボタン表示処理
//	if($pulldownExp[1] == 1){
		
		//----------------------------------------------------------------------
		// 　予約可能数表示処理 (START)
		//----------------------------------------------------------------------
		$remainingFlag = "";
		if($reservCount == 1){
			//定義予約可能数
			$remainingReserv = $reservCountNum;
			//現在の予約数取得
			$reservFilePath = $reservFileDir."/".date("Y-n-j",strtotime($ym."-".$day))."-".$j.".dat";
			//予約カウント用データが存在する場合のみデータを取得、カウントし、残り数を計算
			if(file_exists($reservFilePath)){
				$fp = fopen($reservFilePath, "rb") or die("fopen Error!!");
				$getReservLines = fgets($fp);
				//残り予約可能数計算
				$remainingReserv = $reservCountNum - trim($getReservLines);
			}
			
			
			//残り数が0の場合の処理
			if($remainingReserv < 1){
				$pulldownTag .= $pulldownListArray[1].'<input type="button" disabled value="'.$reservText.'">';//高さ合わせのためdisabledボタン設置
				//ゼロだったらボタン非表示のためのフラグ
				$remainingFlag = 1;	
			}else{
				$pulldownTag .= '<span class="countNum"> [残り:'.$remainingReserv."]</span>\n";
			}
		
		}
		
		//スマホの場合、ajax回避属性追記
		if($device == "sp") $addPropaty = ' data-ajax="false"';else $addPropaty = '';
		
		//予約可能な状態のみ予約ボタン表示
		if(empty($remainingFlag)){
			$pulldownTag .= "\n".'<form class="reservForm" action="'.$_SERVER['SCRIPT_NAME'].'?mode=form&date='.date("Y-n-j",strtotime($ym."-".$day)).'&time='.$j.'" method="post"'.$addPropaty.' target="_parent"><input type="hidden" name="date" value="'.date("Y-n-j",strtotime($ym."-".$day)).'" /><input type="hidden" name="time" value="'.$j.'" /><input type="submit" value="'.$reservText.'" name="reservSubmit" /></form>';
		}
		//----------------------------------------------------------------------
		// 　予約可能数表示処理 (END)
		//----------------------------------------------------------------------
		
	}else{
		//プルダウンリストの配列（2番目の予約終了表示）
		$pulldownTag .= $pulldownListArray[1].'<input type="button" disabled value="'.$reservText.'">';//高さ合わせのためdisabledボタン設置
	}
	$pulldownTag .= '</div>';
	return $pulldownTag;
}
//古い予約数カウント用ファイルの削除（3ヶ月以上前のファイル）
function deleteReservCountFile($reservFileDir){
	
	if(file_exists($reservFileDir)){
		//ディレクトリ・ハンドルをオープン
		$res_dir = @opendir($reservFileDir);
		
		//ディレクトリ内のファイル名を取得
		while( $file_name = @readdir($res_dir) ){
			
			if(strpos($file_name,".dat") !== false){
				//取得したファイル名を表示
				$file_name2 = str_replace('.dat','',$file_name);
				
				$file_name_array = explode("-",$file_name2);
				
				$file_name2 = $file_name_array[0]."-".$file_name_array[1]."-".$file_name_array[2];
				
				//指定日以前のファイルを削除
				if( strtotime($file_name2) < strtotime(date("Y-n-j",strtotime("-3 month"))) ){
						@unlink("{$reservFileDir}/{$file_name}");
				}
			}
		}
		@closedir($res_dir);
	}
}

//----------------------------------------------------------------------
// 　関数定義（基本的に変更不可） (END)
//----------------------------------------------------------------------
//----------------------------------------------------------------------
//  関数定義　メール　(START)
//----------------------------------------------------------------------
function checkMail($str){
	$mailaddress_array = explode('@',$str);
	if(preg_match("/^[\.!#%&\-_0-9a-zA-Z\?\/\+]+\@[!#%&\-_0-9a-z]+(\.[!#%&\-_0-9a-z]+)+$/", "$str") && count($mailaddress_array) ==2){
		return true;
	}else{
		return false;
	}
}
//Shift-JISの場合に誤変換文字の置換関数
function sjisReplace($arr,$encode){
	foreach($arr as $key => $val){
		$key = str_replace('＼','ー',$key);
		$resArray[$key] = $val;
	}
	return $resArray;
}
//送信メールにPOSTデータをセットする関数
function postToMail($arr){
	global $hankaku,$hankaku_array,$replaceStr;	
	
	$resArray = '';
	foreach($arr as $key => $val){
		$out = '';
		if(is_array($val)){
			foreach($val as $item){ 
				//連結項目の処理
				if(is_array($item)){
					$out .= connect2val($item);
				}else{
					$out .= $item . ', ';
				}
			}
			$out = rtrim($out,', ');
		}else{
			$out = $val;
		}
		
		//機種依存文字の置換処理
		$out = str_replace($replaceStr['before'], $replaceStr['after'], $out);
		
		
		if (version_compare(PHP_VERSION, '5.1.0', '<=')) {//PHP5.1.0以下の場合のみ実行（7.4でget_magic_quotes_gpcが非推奨になったため）
			if(get_magic_quotes_gpc()) { $out = stripslashes($out); }
		}
		
		//全角→半角変換
		if($hankaku == 1){
			$out = zenkaku2hankaku($key,$out,$hankaku_array);
		}
		
		if($out != "confirm_submit" && $key != "httpReferer" && $key != 'confirm_reserv') {
			$resArray .= "【 ".$key." 】 ".$out."\n";
		}
	}
	return $resArray;
}
//確認画面の入力内容出力用関数
function confirmOutput($arr){
	global $hankaku,$hankaku_array,$useToken,$confirmDsp,$replaceStr;
	$html = '';
	foreach($arr as $key => $val) {
		$out = '';
		if(is_array($val)){
			foreach($val as $item){ 
			
				//連結項目の処理
				if(is_array($item)){
					$out .= connect2val($item);
				}else{
					$out .= $item . ', ';
				}
				
			}
			$out = rtrim($out,', ');
		}else { $out = $val; }//チェックボックス（配列）追記ここまで
		
		if (version_compare(PHP_VERSION, '5.1.0', '<=')) {//PHP5.1.0以下の場合のみ実行（7.4でget_magic_quotes_gpcが非推奨になったため）
			if(get_magic_quotes_gpc()) { $out = stripslashes($out); }
		}
		$out = nl2br(calf_h($out));//※追記 改行コードを<br>タグに変換
		$key = calf_h($key);
		
		//機種依存文字の置換処理
		$out = str_replace($replaceStr['before'], $replaceStr['after'], $out);
		
		//全角→半角変換
		if($hankaku == 1){
			$out = zenkaku2hankaku($key,$out,$hankaku_array);
		}
		
		
		$html .= "<tr><th>".$key."</th><td>".$out;
		$html .= '<input type="hidden" name="'.$key.'" value="'.str_replace(array("<br />","<br>"),"",$out).'" />';
		$html .= "</td></tr>\n";
	}
	
	//トークンをセット
	if($useToken == 1 && $confirmDsp == 1){
		$token = sha1(uniqid(mt_rand(), true));
		$_SESSION['mailform_token'] = $token;
		$html .= '<input type="hidden" name="mailform_token" value="'.$token.'" />';
	}
	
	return $html;
}
//全角→半角変換
function zenkaku2hankaku($key,$out,$hankaku_array){
	global $encode;
	if(is_array($hankaku_array) && function_exists('mb_convert_kana')){
		foreach($hankaku_array as $hankaku_array_val){
			if($key == $hankaku_array_val){
				$out = mb_convert_kana($out,'a',$encode);
			}
		}
	}
	return $out;
}

//配列連結の処理
function connect2val($arr){
	$out = '';
	foreach($arr as $key => $val){
		if($key === 0 || $val == ''){//配列が未記入（0）、または内容が空のの場合には連結文字を付加しない（型まで調べる必要あり）
			$key = '';
		}elseif(strpos($key,"円") !== false && $val != '' && preg_match("/^[0-9]+$/",$val)){
			$val = number_format($val);//金額の場合には3桁ごとにカンマを追加
		}
		$out .= $val . $key;
	}
	return $out;
}

//管理者宛送信メールヘッダ
function adminHeader($post_mail,$BccMail,$to){
	global $fromAdimin;
	$header = '';
	if(!empty($post_mail)) {
		
		$fromAdimin = (!empty($fromAdimin)) ? $fromAdimin : $post_mail;
		$header="From: $fromAdimin\n";
		if($BccMail != '') {
		  $header.="Bcc: $BccMail\n";
		}
		$header.="Reply-To: ".$post_mail."\n";
	}else {
		if($BccMail != '') {
		  $header="Bcc: $BccMail\n";
		}
		$header.="Reply-To: ".$to."\n";
	}
		$header.="Content-Type:text/plain;charset=iso-2022-jp\nX-Mailer: PHP/".phpversion();
		return $header;
}
//管理者宛送信メールボディ
function mailToAdmin($arr,$subject,$mailFooterDsp,$mailSignature,$encode,$confirmDsp){
	$adminBody="「".$subject."」からメールが届きました\n\n";
	$adminBody .="＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝\n\n";
	$adminBody.= postToMail($arr);//POSTデータを関数からセット
	$adminBody.="\n＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝\n";
	$adminBody.="送信された日時：".date( "Y/m/d (D) H:i:s", time() )."\n";
	$adminBody.="送信者のIPアドレス：".@$_SERVER["REMOTE_ADDR"]."\n";
	$adminBody.="送信者のホスト名：".getHostByAddr(getenv('REMOTE_ADDR'))."\n";
	if($confirmDsp != 1){
		$adminBody.="フォームのページURL：".@$_SERVER['HTTP_REFERER']."\n";
	}else{
		$adminBody.="フォームのページURL：".@$arr['httpReferer']."\n";
	}
	if($mailFooterDsp == 1) $adminBody.= $mailSignature;
	return mb_convert_encoding($adminBody,"JIS",$encode);
}

//ユーザ宛送信メールヘッダ
function userHeader($refrom_name,$from,$encode){
	$reheader = "From: ";
	if(!empty($refrom_name)){
		$default_internal_encode = mb_internal_encoding();
		if($default_internal_encode != $encode){
			mb_internal_encoding($encode);
		}
		$reheader .= mb_encode_mimeheader($refrom_name)." <".$from.">\nReply-To: ".$from;
	}else{
		$reheader .= "$from\nReply-To: ".$from;
	}
	$reheader .= "\nContent-Type: text/plain;charset=iso-2022-jp\nX-Mailer: PHP/".phpversion();
	return $reheader;
}
//ユーザ宛送信メールボディ
function mailToUser($arr,$dsp_name,$remail_text,$mailFooterDsp,$mailSignature,$encode){
	$userBody = '';
	if(isset($arr[$dsp_name])) $userBody = calf_h($arr[$dsp_name]). " 様\n";
	$userBody.= $remail_text;
	$userBody.="\n＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝\n\n";
	$userBody.= postToMail($arr);//POSTデータを関数からセット
	$userBody.="\n＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝\n\n";
	$userBody.="送信日時：".date( "Y/m/d (D) H:i:s", time() )."\n";

	if($mailFooterDsp == 1) $userBody.= $mailSignature;
	return mb_convert_encoding($userBody,"JIS",$encode);
}

//必須チェック関数（2016/8/5に項目連結版に変更）
function requireCheck($require){
	$res['errm'] = '';
	$res['empty_flag'] = 0;
	foreach($require as $requireVal){
		$existsFalg = '';
		foreach($_POST as $key => $val) {
			if($key == $requireVal) {
				
				//連結指定の項目（配列）のための必須チェック
				if(is_array($val)){
					$connectEmpty = 0;
					foreach($val as $kk => $vv){
						if(is_array($vv)){
							foreach($vv as $kk02 => $vv02){
								if($vv02 == ''){
									$connectEmpty++;
								}
							}
						}
						
					}
					if($connectEmpty > 0){
						$res['errm'] .= "<p class=\"error_messe\">【".calf_h($key)."】は必須項目です。</p>\n";
						$res['empty_flag'] = 1;
					}
				}
				//デフォルト必須チェック
				elseif($val == ''){
					$res['errm'] .= "<p class=\"error_messe\">【".calf_h($key)."】は必須項目です。</p>\n";
					$res['empty_flag'] = 1;
				}
				
				$existsFalg = 1;
				break;
			}
			
		}
		if($existsFalg != 1){
				$res['errm'] .= "<p class=\"error_messe\">【".$requireVal."】が未選択です。</p>\n";
				$res['empty_flag'] = 1;
		}
	}
	
	return $res;
}


//リファラチェック
function refererCheck($Referer_check,$Referer_check_domain){
	if($Referer_check == 1 && !empty($Referer_check_domain)){
		if(strpos($_SERVER['HTTP_REFERER'],$Referer_check_domain) === false){
			return exit('<p align="center">リファラチェックエラー。フォームページのドメインとこのファイルのドメインが一致しません</p>');
		}
	}
}
//ご予約日時連結のための再セット
function formPostToConnect($timeArray){
	global $weekArray,$weekDsp,$selectDateText;
	$post = array();
	$reserv = array();
	foreach($_POST as $key => $val){
		
		if($key == "reserv"){
			
			$key = $selectDateText;
			$res = "";
			$getDateArray = explode("-",$_POST["reserv"]["date"]);
			$res .= $getDateArray[0]."年".$getDateArray[1]."月".$getDateArray[2]."日";
			$res .= ($weekDsp == 1) ? '（'.$weekArray[date('w',strtotime($_POST["reserv"]["date"]))].'）' : '';
			
			$res .= $timeArray[$_POST["reserv"]["time"]];
			$val = rtrim($res);
		}
		
		$post[$key] = $val;
	}

	//予約数カウント用ファイルにログるため変数にセット
	$reserv["date"] = $_POST["reserv"]["date"];
	$reserv["time"] = $_POST["reserv"]["time"];
	
	//POSTデータをセットし直す
	$_POST = array();
	$_POST = $post;

	return $reserv;
}
//予約データカウント用に日時を保存する（第三引数はデバイスごとのトプページURL）
function mailToReservCountReg($reservFileDir,$reservCountNum,$site_top){
	global $reserv,$pulldownFilePath,$dispMonth;

	//$reserv = array();
	
	
	if(isset($_POST["confirm_reserv"]["date"])){
		$reserv["date"] = calf_h($_POST["confirm_reserv"]["date"]);
		if(strpos($reserv["date"],'/') !== false) exit();//トラバーサル対策
	}
	
	if(isset($_POST["confirm_reserv"]["time"])){
		$reserv["time"] = calf_h($_POST["confirm_reserv"]["time"]);
		if(strpos($reserv["time"],'/') !== false) exit();//トラバーサル対策
	}
	
	
	if($reserv["date"] == ''){
		exit('日付が選択されていません。<br>大変お手数ですが日時を再度ご選択の上お申込み下さい。<br><a href="javascript:history.back()">戻る&raquo;<a>');	
	}
	
	//もし受付中でなかったら強制終了（2015/9/7　完全に不正防止のため追加）
	$acceptingFlag = 0;
	if(file_exists($pulldownFilePath[$reserv["time"]])){
		$getLinesArr = file($pulldownFilePath[$reserv["time"]]);
		foreach($getLinesArr as $getLinesArrVal){
			$getLinesArrValArr = explode(',',$getLinesArrVal);	
			if(strtotime($getLinesArrValArr[0]) == strtotime($reserv["date"])){
				if($getLinesArrValArr[1] == 1){
					$acceptingFlag = 1;
				}
				break;
			}
		}
	}
	//選択した日付が表示期間内かどうか、過去の日付でないかのチェック（パラメータ不正対策）
	if(strtotime($reserv["date"]) < strtotime(date('Y-m-d')) || strtotime(date('Y-m-01',strtotime('+'.($dispMonth+1).'month'))) <= strtotime($reserv["date"])){
		$acceptingFlag = 0;
	}
	
	if($acceptingFlag == 0) exit('選択された日付は現在受付中ではありません。<br>大変お手数ですが日時を再度ご選択の上お申込み下さい。<br><a href="'.$site_top.'">サイトに戻る&raquo;<a>');
	
	
	
	//保存先パス（data/reserv/予約日-予約時間のキー.dat）
	$reservFilePath = $reservFileDir."/".$reserv["date"]."-".$reserv["time"].".dat";
	
	$fp = fopen($reservFilePath, "a+b") or die("ファイルが生成できませんでした。reservディレクトリのパーミッションを確認下さい。");
	$lines = fgets($fp);
	
	//予約可能数が「0」の場合には強制終了
	if($lines != "" && $lines >= $reservCountNum){
		exit('大変申し訳ございませんが、直前のタイミングで上限数を超えてしまいました。<br>大変お手数ですが日時を再度ご選択の上お申込み下さい。<br><a href="'.$site_top.'">サイトに戻る&raquo;<a>');	
	}
	
	if($lines != ""){
		$reservRegData = $lines + 1;
	}else{
		$reservRegData = 1;
	}
	
	// 俳他的ロック
	if (flock($fp, LOCK_EX)) {
		ftruncate($fp,0);
		rewind($fp);
		fwrite($fp, $reservRegData);// 書き込み
	}
	fflush($fp);
	flock($fp, LOCK_UN);
	fclose($fp);
}

//----------------------------------------------------------------------
//  関数定義(END)
//----------------------------------------------------------------------
?>