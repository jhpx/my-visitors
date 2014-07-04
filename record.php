<?php
/**
 * 記錄訪客資料
 */
defined('ABSPATH') or die('This file can not be loaded directly.');

$options = get_option('my_visitors');
$exclude_blogger  = $options[0];  # 博主不記錄 (未登入情況)
$exclude_login    = $options[1];  # 配置管理員不記錄 (已登入情況)
$exclude_custom   = $options[2] ? explode(',', strtr($options[2], ' ', ',')) : '';  # 自定義人員不記錄
$exclude_n_404_bt = $options[19]; # 非 [404] 的爬蟲不記錄
$exclude_n_dg_404 = $options[20]; # 非危險份子的 [404] 不記錄


/* 訪問計數 */
if (!function_exists('process_postviews') && is_singular()) {
  global $post;
  $post_ID = isset($post) ? $post->ID : '';
  if ($post_ID) {
    if($options[22]) { # 是否啟用 Ajax 計數
      if ($options[23]) wp_print_scripts('jquery'); # 是否由本插件載入 jQ
      echo "\n<!-- postviews -->\n<script type='text/javascript'>\n//<![CDATA[\njQuery.ajax({type:'GET',url:'".plugins_url('my-visitors/postviews.php')."',data:'postviews_id=$post_ID',cache:false});\n//]]>\n</script>\n\n";
    } else {
      $post_views = (int)get_post_meta($post_ID, 'views', true);
      if(!update_post_meta($post_ID, 'views', ($post_views+1))) {
        add_post_meta($post_ID, 'views', 1, true);
      }
    }
  }
}


/* 不記錄人員直接返回 */
global $current_user, $mark_as_spam;
$name = isset($mark_as_spam) ? '(spam)' : ( isset($current_user->user_login) ? $current_user->user_login : ( isset($_COOKIE['comment_author_'. COOKIEHASH]) ? $_COOKIE['comment_author_'. COOKIEHASH] : '' ) );

if (!$arg || is_numeric($arg)) { // not login page
  if (($exclude_blogger && (isset($_COOKIE['wp-settings-1']) || isset($_COOKIE['wp-settings-2'])))
   || ($exclude_login   && current_user_can('manage_options'))
   || ($exclude_custom  && in_array($name, $exclude_custom))
  ) return;
}


/* IP 查詢 */
$ip = $_SERVER["REMOTE_ADDR"];
$qqwry = ABSPATH . "../ip/data/QQWry.dat"; // 純真 IP 庫

if (is_file($qqwry)) {

  /* 純真 IP 查詢 */ 
  include('iplocation.php');
  $iplocation = new IpLocation($qqwry);
  $separator = $iplocation->separate(1000);

  $ip_location = $iplocation->getlocation($ip, $separator);
  //$region = $ip_location['country'].' '.$ip_location['area'];
  $region = $ip_location['country']; // 只取 country, 不取 area

  //if (isset($_SERVER["HTTP_VIA"])) $region .= ' (代理:'.$_SERVER["HTTP_VIA"].')';
  if (isset($_SERVER["HTTP_VIA"])) $region .= ' (代理)'; // 不取 HTTP_VIA

/*  if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
    $ip_x = $_SERVER["HTTP_X_FORWARDED_FOR"];

    $ip_location = $iplocation->getlocation($ip_x, $separator);
    //$region_x = $ip_location['country'].' '.$ip_location['area'];
    $region_x = $ip_location['country']; // 只取 country, 不取 area

    $region .= " (真實IP:$ip_x $region_x)";
  }*/
  //$region .= ' (qqwry)'; // 可識別是否用了純真 IP 庫

} else {

  /* ip138 查詢 */
  function whois($ip) {
    $whois = "http://www.ip138.com/ips.asp?ip=$ip";
    $ch = curl_init();
     curl_setopt ($ch, CURLOPT_URL, $whois);
     curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);
       ob_start();
       curl_exec($ch);
       curl_close($ch);
       $html = mb_convert_encoding(ob_get_contents(), 'UTF-8', 'GB2312');
       ob_end_clean();
    $tmp = @strpos($html, '<li>本站主数据：') + 22;
    return $regn = ($tmp < 23) ? '' : substr($html, $tmp, strpos($html, '</li>', $tmp) - $tmp);
  }

  if (@function_exists('curl_init')) {
    $region = whois($ip);

    //if (isset($_SERVER["HTTP_VIA"])) $region .= ' (代理:'.$_SERVER["HTTP_VIA"].')';
    if (isset($_SERVER["HTTP_VIA"])) $region .= ' (代理)'; // 不取 HTTP_VIA

    if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
      $ip_x = $_SERVER["HTTP_X_FORWARDED_FOR"];
      $region_x = whois($ip_x);
      $region .= " (真實IP:$ip_x $region_x)";
    }

  } else $region = '';

}


/* 取得訪客 user agent */
$ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
$bs = $ver = $os = $b = '';

if (!$ua) {
  $bs = '?';

} else {

  if ($exclude_n_dg_404 && is_404()) return; # 非危險份子的 [404] 不記錄

  include('user_agent.inc'); // 載入 user agent 查詢庫

  /* ROBOT */
  for ($i = 0; isset($bot_array[$i]) && !$bs; $i++) {
    if (stripos($ua, $bot_array[$i]) > -1) $bs = $bot_array[$i];
  }

  if ($exclude_n_404_bt && !is_404() && $bs) return; # 非 [404] 的爬蟲不記錄

  if (!$bs) { // 基本上 bot 最多, 如果訪客是 bot 就不用再往下走

    /* MOBILE */
    for ($i = 0; isset($mobile_array[$i]) && !$os; $i+= 2) {
      if (stripos($ua, $mobile_array[$i]) > -1) {
        $bs = $mobile_array[$i];
        $vc = $mobile_array[$i+1] ? $mobile_array[$i+1] : $mobile_array[$i];
        preg_match('/'.$vc.'[\s|\/]*([[:digit:]\.]+)/i', $ua, $b);
        if (isset($b[1])) $ver = $b[1];
        $os = 'Mobile';
      }
    }

    /* BROWSERS */
    for ($i = 0; isset($browsers_array[$i]) && !$bs; $i+= 2) {
      if (stripos($ua, $browsers_array[$i]) > -1) {
        $bs = $browsers_array[$i];
        $vc = $browsers_array[$i+1] ? $browsers_array[$i+1] : $browsers_array[$i];
        preg_match('/'.$vc.'[\s|\/]*([[:digit:]\.]+)/i', $ua, $b);
        if (isset($b[1])) $ver = $b[1];
      }
    }

    /* OS */
    for ($i = 0; isset($os_array[$i]) && !$os; $i+= 2) {
      if (stripos($ua, $os_array[$i]) > -1) $os = !$os_array[$i+1] ? $os_array[$i] : $os_array[$i+1];
    }

  } // end if (!$bs)

} // end if (!$ua) else


/* 取得 request 和 referer */
$requrl = (is_404() ? '[404]' : '') . $_SERVER['REQUEST_URI'];
$refurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
$requrl = utf8_uri_encode($requrl);
$refurl = utf8_uri_encode($refurl);

// 去除 $sub_url
/*$host = 'http://' . $_SERVER['HTTP_HOST'];
$sub_url = str_replace($host, '', get_option('home'));
if ($sub_url) {
  $requrl = str_replace($sub_url, '', $requrl);
  $refurl = str_replace($sub_url, '', $refurl);
}*/


/* login 和 comment */
global $error, $user_name;

if ($arg) { // login 和 comment 才有 $arg

  if ($error) { // login error
    $arg = strip_tags($error);
    $tmp = strpos($arg, '：') + 3;
    $arg = substr($arg, $tmp, strpos($arg, '。') - $tmp);
    $pwd = $_POST['pwd'];
    $name = $user_name . '(' . $pwd. ')　' . $arg . ' (e)';

    /*  郵件通知 */
    if ($options[9]) { # option $email_enable
      class_exists('PHPMailer') or require(ABSPATH . WPINC . '/class-phpmailer.php');
      $phpmailer = new PHPMailer();
      $phpmailer->AddAddress(get_bloginfo('admin_email'));
      $phpmailer->Body = "<div>錯誤：$arg<br/><br/>登入名: $user_name<br/>密碼: $pwd<br/>IP: $ip $region</div>";
      $phpmailer->CharSet = 'UTF-8';
      $phpmailer->ContentType = 'text/html';
      $phpmailer->FromName = get_option('blogname');
      $phpmailer->From = 'no-reply@' . preg_replace('#^www\.#', '', strtolower($_SERVER['SERVER_NAME']));
      $phpmailer->Subject = "※ 登入錯誤通知";
      $phpmailer->Send();
    }

  } elseif (is_numeric($arg)) { // comment
    $comment = get_comment($arg);
    $name = $comment->comment_author . " $arg";

  } else $name = $arg . ' (l)'; // login success

}


/* user agent 是否存入數據庫 */
//if ($bs && !in_array($bs, array('?', 'bot', 'crawler', 'spider'))) $ua = ''; // my test
if (!$options[3]) $ua = ''; # option $save_agent


/* 存入數據庫 */
global $wpdb, $visitors;
$gmt = explode(' ', gmdate('Y-m-d H:i:s', time() + get_option('gmt_offset')*3600)); // explode GMT
$wpdb->query("INSERT INTO ". $visitors ." VALUES ('', '$gmt[0]', '$gmt[1]', '$ip', '$region', '$name', '$requrl', '$refurl', '$ua', '$bs', '$ver', '$os')");

