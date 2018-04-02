<!doctype html>
<html lang='ja'>
<head>
<meta charset='utf-8'>
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title>The BBS! - phpで書いた簡易掲示板です。</title>
<style>
.content { border:solid 1px #eee; padding:1em; }
.user { text-align:right; }
li { margin-bottom:1em; }
textarea { width:100%; height:20em;font-size:medium; }
</style>
</head>
<body>
<h1>The BBS!</h1>
<p>phpで書いた超簡易掲示板システムです。htmlタグなどは機能しません。</p>
<?php
$mode = $_GET['mode'];
$sep = '"';
$uri = explode('?',$_SERVER['REQUEST_URI'])[0];
$newpostcode = <<< NPC
  <form action="{$uri}" method="post">
  <p>なまえ：<input type="text" name="user" size="20" /></p>
  <p>コメント：</p><textarea name="content" class="textbox" /></textarea>
  <p>パスワード：<input type="password" name="pass" size="20" /> <input type="submit" value="書き込み" /></p>
  </form>
NPC;
$singlepostcode = <<< SPC
  <form action="{$uri}">[IP/UA] {$_POST["remote"]}/{$_POST["agent"]} <input type="submit" value="一覧表示" />
  <p><textarea name="content" class="textbox" />{$_POST["content"]}</textarea></p>
  <p class="user">{$_POST["date"]} - {$_POST["time"]}<br>Witten by {$_POST["user"]}</p></form>
SPC;
if ($mode == 'newpost') {
  print($newpostcode);
} elseif ($mode == 'single') {
  print($singlepostcode);
} else {
  date_default_timezone_set('Asia/Tokyo');
  $currDatetime = new Datetime();
  print('<form action="'.$uri.'?mode=newpost" method="post"><input type="submit" value="新規書き込み" /></form>');
  print('<ol>');
  $line = file(__DIR__ . "/bbs.txt", FILE_IGNORE_NEW_LINES);
  foreach ($line as $value) {
    if ($value != null) {
      $parts = explode($sep,$value);
      $date[] = $parts[0];
      $time[] = $parts[1];
      $user[] = $parts[2];
      $pass[] = $parts[3];
      $content[] = $parts[4];
      $remote[] = $parts[5];
      $agent[] = $parts[6];
    }
  }
  if ($_POST['content'] != null) {
    $date[] = $currDatetime->format('Y/m/d');
    $time[] = $currDatetime->format('H:i:s.u');
    $user[] = $_POST['user'];
    $pass[] = hash('sha256',$_POST['pass']);
    $content[] = str_replace(array("\r\n","\r","\n"),"",nl2br(htmlspecialchars($_POST['content'],ENT_QUOTES)));
    $remote[] = hash('crc32b',$_SERVER["REMOTE_ADDR"]);
    $agent[] = hash('crc32b',$_SERVER["HTTP_USER_AGENT"]);
    file_put_contents(__DIR__.'/bbs.txt',"\n".$date[count($date)-1].$sep.$time[count($time)-1].$sep.$user[count($user)-1].$sep.$pass[count($pass)-1].$sep.$content[count($content)-1].$sep.$remote[count($remote)-1].$sep.$agent[count($agent)-1], FILE_APPEND);
  }
  for ($i=count($content);$i>0;$i--) {
$looppostcode = <<< LPC
  <li><form action="{$uri}?mode=single" method="post">[IP/UA] {$remote[$i-1]}/{$agent[$i-1]} 
  <input type="submit" value="投稿表示" /> <input type="hidden" name="date" value="{$date[$i-1]}" />
  <input type="hidden" name="time" value="{$time[$i-1]}" />
  <input type="hidden" name="user" value="{$user[$i-1]}" />
  <input type="hidden" name="pass" value="{$pass[$i-1]}" />
  <input type="hidden" name="content" value="{$content[$i-1]}" />
  <input type="hidden" name="remote" value="{$remote[$i-1]}" />
  <input type="hidden" name="agent" value="{$agent[$i-1]}" /></form>
  <p class="content">{$content[$i-1]}</p>
  <p class="user">{$date[$i-1]} - {$time[$i-1]}<br>Witten by {$user[$i-1]}</p></li>
LPC;
    print($looppostcode);
  }
  print('</ol>');
}
?>
</body>
</html>
