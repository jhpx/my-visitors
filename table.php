<?php
/**
 * 輸出表格 -- 用於 details.php 和 search.php
 */
defined('ABSPATH') or die('This file can not be loaded directly.'); ?>

 <table class="widefat fixed">
  <thead>
   <tr>
    <th class="manage-column" style="width:50px">ID</th>
    <th class="manage-column" style="width:10%">日期 / 时间</th>
    <th class="manage-column" style="width:25%">IP / 地区 / 名称</th>
    <th class="manage-column" style="width:50%">请求 / 来路 / 用户代理</th>
    <th class="manage-column" style="width:15%">浏览器 / 操作系统</th>
   </tr>
  </thead>

  <tbody id="tb">

<?php
  $cnt = 0;
  foreach( $results as $db ) {
   $id      = str_pad($db->id, 6, '0', STR_PAD_LEFT);
   $name    = $db->name;
   $requrl  = $db->requrl;
   $refurl  = $db->refurl;
   $agent   = $db->agent;
   $browser = $db->browser;
   $os      = $db->os;

   // table css
   $css = $id % 2 ? 'alternate ' : '';
   $css .= $name ? 'friend ' : 'anymus ';

   if (strpos($requrl, '[404]') > -1) {
     $css .= 'e404 ';
     $requrl = substr($requrl, 5);
     $req_404 = '<span class="attention">[404]</span>';
   } else {
     $css .= 'h200 ';
     $req_404 = '';
   }
   $requrl_d = $requrl;
   $refurl_d = $refurl;


   // for sub-folder blog
   $host = 'http://' . $_SERVER['HTTP_HOST'];
   $sub_url = str_replace($host, '', get_option('home'));
   if (!empty($sub_url)) {
     if (ord($requrl) == 47) $requrl = $sub_url. $requrl; // 加上 $sub_url
     if (ord($refurl) == 47) $refurl = $sub_url. $refurl;
   }

    if (!$refurl) $css .= 'drct ';
    elseif (strpos($refurl, 'q=') || strpos($refurl, 'bs=') || strpos($refurl, 'query=') || strpos($refurl, 'word=') || strpos($refurl, 'wd=') || strpos($refurl, 'imgurl=')) $css .= 'srch ';
    elseif (ord($refurl) != 47) $css .= 'out ';
    else $css .= 'in ';

    if (strpos($os, 'Windows') > -1) $css .= 'win ';
    elseif (strpos($os, 'Mac') > -1) $css .= 'mac ';
    elseif (stripos($os, 'Ubuntu') > -1) $css .= 'ubt ';
    elseif (strpos($os, 'Linux') > -1) $css .= 'lnx ';
    elseif (!$os && $browser && $browser != '?') $css .= 'bo ';
    elseif (!$browser || $browser == '?') $css .= 'un ';
    else $css .= 'ots ';

    if (strpos($browser, 'MSIE') > -1) {$css .= 'ie'; if (strpos($browser, 'MSIE') == 0) $browser = str_replace('MSIE', 'Internet Explorer', $browser);}
    elseif (strpos($browser, 'Firefox') > -1) $css .= 'fx';
    elseif (strpos($browser, 'Chrome') > -1) $css .= 'gc';
    elseif (strpos($os, 'Mobile') > -1) $css .= 'mb'; // mobil 要優先於 opera
    elseif (strpos($browser, 'Opera') > -1) $css .= 'op';
    elseif (strpos($browser, 'Safari') > -1) $css .= 'sf';
    elseif ((!$os && $browser && $browser != '?') || !$browser || $browser == '?') {}
    else $css .= 'ot';

    // comment
    $name_array = explode(" ", $name); $commt_link = '';
    if (!empty($name_array[1]) && !(int)$name_array[1]) {
     if ($name_array[1] == '(l)') $name_array[0] .= '<span style="color:green"> (登陆)</span>';
     elseif ($name_array[1] == '(e)') $name_array[0] .= '<span style="color:red"> (登陆错误)</span>';
    } else {
      $comment = get_comment($name_array[1]);
      if ($comment) {
        $apv = $comment->comment_approved;
        $mark = $apv == '0' ? '待审评论' : ($apv == 'spam' ? '垃圾评论' : ($apv == 'trash' ? '评论已刪除' : '评论'));
        if ($apv == '0' || $apv == '1') $mark.= strlen($comment->comment_content) > 20 ? ': ' . mb_substr($comment->comment_content, 0, 20, 'utf-8') . '...' : ': ' . $comment->comment_content;
        if ($apv == '0') $apv = 'moderated';
        $commt_link = $apv == '1' ? " <a href='" . esc_attr(get_comment_link($comment, array('type' => 'comment'))) . "' target='_blank'>$mark</a>"
                                  : " <a href='" . get_bloginfo('wpurl') . "/wp-admin/edit-comments.php?comment_status=$apv' target='_blank'>$mark</a>";
      }
    }
    echo "<tr class='$css'><td valign='top'> $id </td><td valign='top'>", substr($db->date_gmt, 5), "<br/>", $db->time_gmt, "</td><td valign='top'>";
    $whois = "http://www.ip138.com/ips.asp?ip=$db->ip";
    echo "<a href='$whois' target='_blank'>$db->ip</a>　", esc_html($db->region), "<br/>", $name_array[0], $commt_link, "</td><td valign='top'>"; 

    // request
    $requrl_d = urldecode(urldecode($requrl_d)); // some stupid platform urlencode twice
    $requrl_d = @mb_convert_encoding($requrl_d, 'UTF-8', 'UTF-8,GBK,BIG-5'); // Chinese encoding
    $requrl_d = esc_attr($requrl_d);
    $requrl   = esc_attr(utf8_uri_encode($requrl));

    // referer
    $refurl   = str_replace('\\', '/', $refurl); // for local referer from windows
    $refurl   = preg_replace('/\[url=?\]*(.*?)(\[\/url)?\].*/e', "\"$1\"",  $refurl); // for stupid platform referer
    $refurl_d = urldecode(urldecode($refurl_d)); // some stupid platform urlencode twice
    $refurl_d = @mb_convert_encoding($refurl_d, 'UTF-8', 'UTF-8,GBK,BIG-5'); // Chinese encoding
    if (strlen($refurl_d) > 60) $refurl_d = mb_substr($refurl_d, 0, 60, 'UTF-8').'...'; // cut off a long string
    $refurl_d = esc_attr($refurl_d);
    $refurl   = esc_attr(utf8_uri_encode($refurl));

    // echo html
    $refurl_link = $refurl == $_SERVER['HTTP_HOST'] ? "from: $refurl_d <span style='color:red'>(伪造)</span>": ( $refurl ? "from: <a href='$refurl' target='_blank'>$refurl_d</a>" : '(direct visit)' );
    echo "Req: $req_404 <a href='$requrl' target='_blank'> $requrl_d </a> $refurl_link <br/>", esc_attr($agent), "</td><td valign='top'>";

    if ((!$browser || $browser == '?') && $req_404) {
      echo '<span style="color:red">(危险份子)</span>';
    } elseif ($browser == '?') {
      echo '<span style="color:#c0c">(神秘人)</span>';
    } elseif (!$browser) {
      echo '<span style="color:green">(未知浏览器)</span>';
    } elseif ($browser == 'bot' || $browser == 'spider') {
      echo '<span style="color:blue">(未知爬虫)</span>';
    } else {
      echo $browser, ' ', $db->ver, '<br/>', $os;
    }
    echo "</td></tr>\n";
    $cnt++;
  }
?>

  </tbody>
 </table>
 
 <b onclick="scroll(0,0)" title="goTop" style="color:#75f;cursor:pointer;float:right;margin-right:30px">Top<b style="font-size:150%">↑</b></b>