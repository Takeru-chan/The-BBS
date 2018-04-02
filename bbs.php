<!doctype html>
<html lang='ja'>
<head>
<meta charset='utf-8'>
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title>The BBS! - phpで書いた簡易掲示板です。</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
<style>
body{ padding:1em; }
.content { border:solid 1px #eee; padding:1em; }
.user { text-align:right; }
li { margin-bottom:1em; }
textarea { width:100%; height:10em;font-size:medium;padding:1em; }
.selector-for-some-widget { box-sizing: content-box; }
</style>
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<script>
     (adsbygoogle = window.adsbygoogle || []).push({
          google_ad_client: "ca-pub-6796132485841062",
          enable_page_level_ads: true
     });
</script>
</head>
<body>
<?php
$uri = explode('?',$_SERVER['REQUEST_URI'])[0];
$mode = $_GET['mode'];
$titlecode = <<< TC
  <h1><a href="{$uri}">The BBS!</a></h1>
  <p>phpで書いた超簡易掲示板システムです。htmlタグなどは機能しません。</p>
  <p><a href="{$uri}?mode=notice">IPアドレスとユーザーエージェントの取得について</a></p>
TC;
$newpostcode = <<< NPC
  <form action="{$uri}" method="post">
  <p>なまえ：<input type="text" name="user" size="20" /></p>
  <p>コメント：</p><textarea name="content" class="textbox" /></textarea>
  <p>パスワード：<input type="password" name="pass" size="20" /> <input type="submit" value="書き込み" /></p>
  <p>なまえとコメントのいずれか、または両方が空欄の場合、投稿はキャンセルされます。</p>
  <input type="hidden" name="db" value="entry" /></form>
NPC;
$singlepostcode = <<< SPC
  <form action="{$uri}" method="post">[IP/UA] {$_POST["remote"]}/{$_POST["agent"]} 
  <p>パスワード：<input type="password" name="pass" size="20" /> 
  <nobr><input type="submit" name="action" value="変更" /> 
  <input type="submit" name="action" value="削除" /></nobr></p>
  <p><textarea name="content" class="textbox my-3" />{$_POST["content"]}</textarea></p>
  <p class="user">{$_POST["date"]}<br>Witten by {$_POST["user"]}</p>
  <input type="hidden" name="id" value="{$_POST['id']}" /><input type="hidden" name="db" value="modify" /></form>
SPC;
$notice = <<< NOTE
  <h2>IPアドレスとユーザーエージェントの取得について</h2>
  <p>投稿ごとに表示されている[IP/UA]に続くそれぞれ８桁の文字列は、IPアドレスとユーザーエージェントから生成された、そこそこ固有の文字列です。</p>
  <p>したがってこれらを照合することで、なりすましをそれなりに見破ることができるわけですが、裏を返せばこのサイトではIPアドレスとユーザーエージェントを取得しているということです。</p>
  <p>これらは個人を特定できるような情報ではありませんが、IPアドレスは例えばあなたが使っているプロバイダや会社のネットワークを特定することができるかもしれませんし、ユーザーエージェントはあなたが使っているOSやブラウザの種類を特定することができるかもしれません。</p>
  <p>まぁそういうことです。</p>
NOTE;
print($titlecode);
date_default_timezone_set('Asia/Tokyo');
$currDatetime = new Datetime();
if ($mode == 'newpost') {
  print($newpostcode);
} elseif ($mode == 'single') {
  print($singlepostcode);
} elseif ($mode == 'notice') {
  print($notice);
} else {
  if ($_POST['db'] == 'entry' && $_POST['user'] != '' && $_POST['content'] != '') {
    $user = urlencode($_POST['user']);
    $content = urlencode(htmlspecialchars($_POST['content'],ENT_QUOTES));
    $sql = 'insert into posts (alive,created,user,passwd,remote,agent,content) values (1,"';
    $sql .= $currDatetime->format('Y-m-d H:i:s.u').'","'.$user.'","';
    $sql .= hash('sha256',$_POST['pass']).'","'.$_SERVER['REMOTE_ADDR'].'","';
    $sql .= htmlspecialchars($_SERVER['HTTP_USER_AGENT'],ENT_QUOTES).'","'.$content.'")';
    $mysqli = new mysqli('localhost','bbs',"7jyD)m6'",'bbs');
    $mysqli->set_charset('utf8');
    if ($mysqli->connect_error) {
      echo $mysqli->connect_error;
      exit();
    } else {
      $mysqli->query($sql);
      $mysqli->close();
    }
  } elseif ($_POST['db'] == 'modify') {
    $content = urlencode(htmlspecialchars($_POST['content'],ENT_QUOTES));
    if ($_POST['action'] == '変更') {
      $sql = 'update posts set content = "'.$content.'" where id = '.$_POST['id'];
    } elseif ($_POST['action'] == '削除') {
      $sql = 'update posts set alive = 0 where id = '.$_POST['id'];
    }
    $mysqli = new mysqli('localhost','bbs',"7jyD)m6'",'bbs');
    $mysqli->set_charset('utf8');
    if ($mysqli->connect_error) {
      echo $mysqli->connect_error;
      exit();
    } else {
      if ($result = $mysqli->query($sql)) {;
        $mysqli->close();
      } else {
        print($result);
      }
    }
  }
  $sql = 'select id,created,lastmodified,user,email,passwd,remote,agent,content from posts';
  $sql .= ' where alive = 1 order by created desc';
  $mysqli = new mysqli('localhost','bbs',"7jyD)m6'",'bbs');
  $mysqli->set_charset('utf8');
  if ($mysqli->connect_error) {
    echo $mysqli->connect_error;
    exit();
  } else {
    if ($result = $mysqli->query($sql)) {
      print('<form action="'.$uri.'?mode=newpost" method="post"><input type="submit" value="新規書き込み" /></form>');
      while ($row = $result->fetch_assoc()) {
        $remote = hash('crc32b',$row["remote"]);
        $agent = hash('crc32b',$row["agent"]);
        $user = urldecode($row['user']);
        $content = urldecode($row['content']);
        $view = nl2br($content);
$looppostcode = <<< LPC
  <div class="card card-light my-3"><div class="card-header">
  <form action="{$uri}?mode=single" method="post"><div class="float-left">[IP/UA] {$remote}/{$agent}</div>
  <div class="float-right"><input type="submit" value="編集/削除" /></div>
  <input type="hidden" name="date" value="{$row['created']}" />
  <input type="hidden" name="id" value="{$row['id']}" />
  <input type="hidden" name="user" value="{$user}" />
  <input type="hidden" name="pass" value="{$row['passwd']}" />
  <input type="hidden" name="content" value="{$content}" />
  <input type="hidden" name="remote" value="{$remote}" />
  <input type="hidden" name="agent" value="{$agent}" /></form></div>
  <p class="card-body">{$view}</p>
  <div class="card-footer bg-transparent text-secondary">
  <div class="user float-left">{$row['created']}</div><div class="float-right">Witten by {$user}</div>
  </div>
  </div>
LPC;
        print($looppostcode);
      }
    }
    $result->close();
  }
  $mysqli->close();
}
?>
</body>
</html>
