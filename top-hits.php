<?php
/**
 * 熱門排行
 */
defined('ABSPATH') or die('This file can not be loaded directly.');
$system_usage = round(memory_get_usage()/1024/1024, 2); ?>

<div class="wrap">
 <div class="icon32" style="width:50px;height:40px;margin:6px 6px 0 0;background:url(<?php echo plugins_url('my-visitors/images/top50.gif'); ?>) no-repeat;"></div>
 <h2>熱門排行</h2>

<?php
// max_tophit
 global $max_tophit;
 $options = get_option('my_visitors');
 $max_tophit = $options[8]; # option $max_tophit

// database query
 global $wpdb, $visitors;
 $rq = "ORDER BY cnt DESC LIMIT $max_tophit";
 $rq_all = $wpdb->get_results("SELECT requrl, COUNT(id) as cnt FROM $visitors GROUP BY requrl $rq"); // query 1/8

if (!empty($rq_all)) {
 $http_host = "'". $_SERVER['HTTP_HOST'] ."'";
 $sel_req = "SELECT requrl, COUNT(id) as cnt FROM $visitors WHERE";
 $sel_ref = "SELECT refurl, COUNT(id) as cnt FROM $visitors WHERE";

 $rq_bot = $wpdb->get_results("$sel_req os=''  GROUP BY requrl $rq"); // query 2/8
 $rq_man = $wpdb->get_results("$sel_req os!='' GROUP BY requrl $rq"); // query 3/8
 $eng_sh = $wpdb->get_results("$sel_ref (refurl LIKE '%q=%' OR refurl LIKE '%bs=%' OR refurl LIKE '%query=%' OR refurl LIKE '%word=%' OR refurl LIKE '%wd=%' OR refurl LIKE '%imgurl=%') GROUP BY refurl $rq"); // query 4/8
 $not_sh = $wpdb->get_results("$sel_ref os!='' AND refurl != $http_host AND refurl NOT LIKE '/%' AND refurl!='' AND refurl NOT LIKE '%q=%' AND refurl NOT LIKE '%bs=%' AND refurl NOT LIKE '%query=%' AND refurl NOT LIKE '%word=%' AND refurl NOT LIKE '%wd=%' AND refurl NOT LIKE '%imgurl=%' AND refurl NOT LIKE '%cache%' GROUP BY refurl $rq"); // query 5/8
 $sit_sh = $wpdb->get_results("$sel_req requrl LIKE '%s=%' GROUP BY requrl $rq"); // query 6/8

 foreach($rq_all as $a) $req_all[$a->requrl] = $a->cnt;
 foreach($rq_bot as $a) $req_bot[$a->requrl] = $a->cnt;
 foreach($rq_man as $a) $req_man[$a->requrl] = $a->cnt;
 foreach($eng_sh as $a) $eng_srch[$a->refurl] = $a->cnt;
 foreach($not_sh as $a) $not_srch[$a->refurl] = $a->cnt;
 foreach($sit_sh as $a) $sit_srch[$a->requrl] = $a->cnt;

  if (get_option('permalink_structure') == '') { // 默認鏈接才能用
    global $q_posts, $q_cats;
    $q_posts  = $wpdb->get_results("SELECT ID, post_title FROM $wpdb->posts"); // query 7/8 .. 查詢文章標題
    $q_cats = $wpdb->get_results("SELECT term_id, name FROM $wpdb->terms");    // query 8/8 .. 查詢分類名稱
  }

// for sub-folder blog
// global $sub_url;
// $host = 'http://' . $_SERVER['HTTP_HOST'];
// $sub_url = str_replace($host, '', get_option('home'));

?>

<br/>

<div id="col-right" style="width:51%">

<div id="radio_post" style="width:300px; margin-bottom:10px; background:#eef; border:1px solid #ccc; -moz-border-radius:12px; -khtml-border-radius:12px; -webkit-border-radius:12px; border-radius:12px; padding:6px 20px; padding:4px 20px\9">
頁面點擊排行:　
   <label><input type="radio" name="top" onclick="jQuery('#top_hit .all, #top_hit .bot').hide();jQuery('#top_hit .man').show()" value="man" /> 訪客</label>　
   <label><input type="radio" name="top" onclick="jQuery('#top_hit .all, #top_hit .man').hide();jQuery('#top_hit .bot').show()" value="bot" /> 爬蟲</label>　
   <label><input type="radio" name="top" onclick="jQuery('#top_hit .bot, #top_hit .man').hide();jQuery('#top_hit .all').show()" value="all" /> 全部</label>
</div>

 <table class="widefat fixed">
  <thead>
   <tr>
    <th class="manage-column" style="width:8%">排行</th>
    <th class="manage-column" style="width:27%">頁面請求</th>
  <?php if (get_option('permalink_structure') == '') { ?>
    <th class="manage-column" style="width:50%">文章標題</th>
  <?php } ?>
    <th class="manage-column" style="width:15%">訪問次數</th>
   </tr>
  </thead>

  <tbody id="top_hit">

<?php
function top_hit($req, $class) {
  global $q_posts, $q_cats, $sub_url, $max_tophit;
  $post_title = '';
  if (!empty($req)) $key_nams = array_keys($req);

  for ($i = 0; isset($key_nams[$i]); $i++) {

   $url = $key_nams[$i];
   $cnt = $req[$url];

     $url_d = htmlspecialchars(urldecode(urldecode($url)));
     $url = htmlspecialchars($url);

     // table css
     $css = $i % 2 ? $class .' alternate' : $class;

     // for [404]
     if (strpos($url, '[404]') > -1) $url = substr($url, 5);

     // for sub-folder blog
     //if ($sub_url && ord($url) == 47) $url = $sub_url. $url;

   // post title
   if (get_option('permalink_structure') == '') { // 默認鏈接才能用

     $post_id = $post_title = '<span class="attention">[404]</span>';

     if (strpos($url_d,'s=')) {
       $tmp = strpos($url_d, '=') + 1;
       $pid = substr($url_d, strpos($url_d, '=')+1);
       $pid = strpos($url_d, '&') ? substr($url_d, $tmp, strpos($url_d, "&")-$tmp) : substr($url_d, $tmp);
       $page = strpos($url_d, '&') ? ' p.'. substr($url_d, strpos($url_d, "&")+11) : '';
       if ($pid == '') $pid = "( 空 )";
       $post_title = '搜索: <span style="color:blue">'. $pid. $page .'</span>';
     }
     elseif (strpos($url_d, 'cat=')) {
       $tmp = strpos($url_d, '=') + 1;
       $pid = substr($url_d, strpos($url_d, '=')+1);
       $pid = strpos($url_d, '&') ? substr($url_d, $tmp, strpos($url_d, "&")-$tmp) : substr($url_d, $tmp);
       $page = strpos($url_d, '&') ? ' p.'. substr($url_d, strpos($url_d, "&")+11) : '';
       foreach($q_cats as $q_cat) {
         if ($pid == $q_cat->term_id) $post_title = '分類: '. $q_cat->name . $page;
       }
     }
     elseif (strpos($url_d, 'tag=')) {
       $tmp = strpos($url_d, '=') + 1;
       $pid = substr($url_d, strpos($url_d, '=')+1);
       $pid = strpos($url_d, '&') ? substr($url_d, $tmp, strpos($url_d, "&")-$tmp) : substr($url_d, $tmp);
       $page = strpos($url_d, '&') ? ' p.'. substr($url_d, strpos($url_d, "&")+11) : '';
       $post_title = '標簽: '. $pid. $page;
     }
     elseif (stripos($url_d, 'p=')|| strpos($url_d, 'page_id=')){
       $tmp = strpos($url_d, '=') + 1;
       $pid = strpos($url_d, '&') ? substr($url_d, $tmp, strpos($url_d, "&")-$tmp) : substr($url_d, $tmp);
       $page = strpos($url_d, '&amp;cpage') ? ' cpage='. substr($url_d, strpos($url_d, "&amp;cpage")+11) : '';
       $pid = strpos($pid, '/') ? substr($pid, 0, strpos($pid, "/")) : $pid;
       $post_title = '<span style="color:#c0c">( 文章不存在 )</span>';
       foreach($q_posts as $q_post) {
       if ($pid == $q_post->ID) $post_title = $q_post->post_title.$page;
       }
       if ($pid == 404) $post_title = '404 測試頁';
     }
     elseif (strpos($url_d,'m=')) {
       $tmp = strpos($url_d, '=') + 1;
       $pid = substr($url_d, strpos($url_d, '=')+1);
       $pid = strpos($url_d, '&') ? substr($url_d, $tmp, strpos($url_d, "&")-$tmp) : substr($url_d, $tmp);
       $page = strpos($url_d, '&') ? ' p.'. substr($url_d, strpos($url_d, "&")+11) : '';
       $post_title = '存檔: '. $pid. $page;
     }
     elseif (strpos($url_d,'v_sortby=')) {
       $page = substr($url_d, strpos($url_d, "&")+24);
       $post_title = '存檔排序 '. $page;
     }
     elseif ($url_d == '/' || rtrim($url_d, '?') == '/') $post_title = '首頁';
     elseif (strpos($url_d,'TB_iframe=')) {
       $post_title = '首頁 (浮動框架)';
     }
     elseif (strpos($url_d,'paged=')) {
       $post_title = '首頁 p.'. substr($url_d,strpos($url_d,'=')+1);
     }
     elseif (strpos($url_d,'comments-')) {
       $post_title = '( 評論 )';
     }
     elseif (strpos($url_d,'?feed=')) {
       $post_title = '( feed )';
     }
     elseif (strpos($url_d,'wp-login')) {
       $post_title = '( 登入 )';
     }

     $post_title = "<td>$post_title</td>";
   }

    $i_d = str_pad($i + 1, 3, '0', STR_PAD_LEFT);
    echo "<tr class='$css'><td style='background:#eee'>$i_d</td><td>";

    if (!empty($url)) {
     echo "<a href='$url' target='_blank'>$url_d</a>";
    }
    elseif (get_option('permalink_structure') == '') {
     $post_title = '<td></td>';
    }

    echo "</td>$post_title<td>$cnt</td></tr>\n";

  }
}

 if (!empty($req_man)) {top_hit($req_man, 'man'); unset($req_man);}
 if (!empty($req_bot)) {top_hit($req_bot, 'bot hidden'); unset($req_bot);}
 top_hit($req_all, 'all hidden'); unset($req_all);
?>

  </tbody>
 </table>

<br/>
<br/>
<div style="width:200px; margin-bottom:10px; background:#eef; border:1px solid #ccc; -moz-border-radius:12px; -khtml-border-radius:12px; -webkit-border-radius:12px; border-radius:12px; padding:6px 20px;">
來源網站排行:　(不計爬蟲)
</div>
 <table class="widefat fixed">
  <thead>
   <tr>
    <th class="manage-column" style="width:10%">排行</th>
    <th class="manage-column" style="width:70%">來源網站</th>
    <th class="manage-column" style="width:20%">訪問次數</th>
   </tr>
  </thead>

  <tbody id="top_reffer">

<?php
if (!empty($not_srch)) {
  global $max_tophit;
  $key = array_keys($not_srch);
  $value = array_values($not_srch);
  $array_num = count($not_srch);

  for ($i = 0; $i < $array_num; $i++) {
    $i_d = str_pad($i + 1, 3, '0', STR_PAD_LEFT);
    $tr_color = $i % 2 ? '' : 'class="alternate"';
    $key_d = rawurldecode(rawurldecode($key[$i]));
    $key_d = htmlspecialchars(mb_convert_encoding($key_d, 'UTF-8', 'UTF-8,GBK,BIG-5'));
    $key_a = htmlspecialchars($key[$i]);
    echo "<tr $tr_color><td class='nc' style='background:#eee'>$i_d</td><td><a href='$key_a' target='_blank'>$key_d</a></td><td>$value[$i]</td></tr>";
  }

} else {
  echo "<tr><td></td><td>(No data to display yet...)</td></tr>\n";

}
?>

  </tbody>
 </table>

</div>


<div id="col-left" style="width:48%">

<div id="radio_search" style="width:270px; margin-bottom:10px; background:#eef; border:1px solid #ccc; -moz-border-radius:12px; -khtml-border-radius:12px; -webkit-border-radius:12px; border-radius:12px; padding:6px 20px; padding:4px 20px\9">
關鍵字排行:　
   <label><input type="radio" name="search" onclick="jQuery('#col-left .sit').hide();jQuery('#col-left .eng').show()" value="eng" /> 搜索引擎</label>　
   <label><input type="radio" name="search" onclick="jQuery('#col-left .eng').hide();jQuery('#col-left .sit').show()" value="sit" /> 站內搜索</label>
</div>

 <table class="widefat fixed">
  <thead>
   <tr>
    <th class="manage-column" style="width:8%">排行</th>
    <th class="manage-column" style="width:37%">原始鏈接</th>
    <th class="manage-column" style="width:40%">關鍵字</th>
    <th class="manage-column" style="width:15%">訪問次數</th>
   </tr>
  </thead>

  <tbody id="top_search">

<?php
if (!empty($eng_srch) || !empty($sit_srch)) {

function top_search($req, $class) {
  global $sub_url, $kw_array, $max_tophit;
  $key_nams = array_keys($req);

  for ($i = 0; isset($key_nams[$i]); $i++) {

   $url = $key_nams[$i];
   $cnt = $req[$url];

     $url_d = urldecode(urldecode($url)); // some stupid platform urlencode twice
     $url_d = @mb_convert_encoding($url_d, 'UTF-8', 'UTF-8,GBK,BIG-5'); // Chinese encoding

     // for sub-folder blog
     //if (!empty($sub_url) && ord($url) == 47) $url = $sub_url. $url;
     $url = htmlspecialchars($url);

     // keyword
    $kw_d = strtr($url_d, array('aq=' => '', 'oq=' => ''));
    $kw = '';

      if (strpos($kw_d,'imgurl=')) {
       $bgn = strpos($kw_d,'imgurl=')+7;
       $end = strpos($kw_d,'&',$bgn) ? strpos($kw_d,'&',$bgn)-$bgn : 500;
       if (strpos($kw_d,'q=') > -1) {
         $bgn_q = strpos($kw_d,'q=')+2;
         $end_q = strpos($kw_d,'&',$bgn_q) ? strpos($kw_d,'&',$bgn_q)-$bgn_q : 500;
         $kw = substr($kw_d, $bgn_q, $end_q);
       }
       elseif (strpos($kw_d,'p=') > -1) {
         $bgn_q = strpos($kw_d,'p=')+2;
         $end_q = strpos($kw_d,'&',$bgn_q) ? strpos($kw_d,'&',$bgn_q)-$bgn_q : 500;
         $kw = substr($kw_d, $bgn_q, $end_q);
         if ((int)$kw) $kw = '';
       }
       $kw_d = '(圖片) '. mb_convert_encoding($kw, 'UTF-8') .': '. substr($kw_d, $bgn, $end);
      }
      elseif (strpos($kw_d,'imagesa')) { // youdao
         $bgn_q = strpos($kw_d,'q=')+2;
         $end_q = strpos($kw_d,'&',$bgn_q) ? strpos($kw_d,'&',$bgn_q)-$bgn_q : 500;
         $kw = substr($kw_d, $bgn_q, $end_q);
       $kw_d = '(圖片) '. mb_convert_encoding($kw, 'UTF-8');
      }
      elseif (strpos($kw_d,'query=')) {
       $bgn = strpos($kw_d,'query=')+6;
       $end = strpos($kw_d,'&',$bgn) ? strpos($kw_d,'&',$bgn)-$bgn : 500;
       $kw  = substr($kw_d, $bgn, $end);
       if ($kw == '') {
        $kw_d = substr($kw_d, $bgn);
        $bgn = strpos($kw_d,'query=')+6;
        $end = strpos($kw_d,'&',$bgn) ? strpos($kw_d,'&',$bgn)-$bgn : 500;
        $kw  = substr($kw_d, $bgn, $end);
       }
      }
      elseif (strpos($kw_d,'word=')) {
       $bgn = strpos($kw_d,'word=')+5;
       $end = strpos($kw_d,'&',$bgn) ? strpos($kw_d,'&',$bgn)-$bgn : 500;
       $kw  = substr($kw_d, $bgn, $end);
      }
      elseif (strpos($kw_d,'wd=')) {
       $bgn = strpos($kw_d,'wd=')+3;
       $end = strpos($kw_d,'&',$bgn) ? strpos($kw_d,'&',$bgn)-$bgn : 500;
       $kw  = substr($kw_d, $bgn, $end);
      }
      elseif (strpos($kw_d,'q=')) {
       $bgn = strpos($kw_d,'q=')+2;
       $end = strpos($kw_d,'&',$bgn) ? strpos($kw_d,'&',$bgn)-$bgn : 500;
       $kw  = substr($kw_d, $bgn, $end);
       if (strpos($kw,'cache:') > -1) { // webcache.google
         $bgn = strpos($kw,' ') ? strpos($kw,' ')+1 : 0;
         $kw  = substr($kw, $bgn);
       }
      }
      elseif (strpos($kw_d,'p=')) {
       $bgn = strpos($kw_d,'p=')+2;
       $end = strpos($kw_d,'&',$bgn) ? strpos($kw_d,'&',$bgn)-$bgn : 500;
       $kw  = substr($kw_d, $bgn, $end);
      }
      elseif (strpos($kw_d,'s=')) {
       $bgn = strpos($kw_d,'s=')+2;
       $end = strpos($kw_d,'&',$bgn) ? strpos($kw_d,'&',$bgn)-$bgn : 500;
       $kw  = substr($kw_d, $bgn, $end);
      }

    if (strpos($url_d,'http') > -1) {
       $url_d = substr($url_d, 0, strpos($url_d,'/', 8));
    }

    $url_d = htmlspecialchars
($url_d);
    $url   = htmlspecialchars
($url);
    $kw_d  = htmlspecialchars
(trim($kw_d));
    $kw    = htmlspecialchars
(trim($kw));

    $i_d   = str_pad($i + 1, 3, '0', STR_PAD_LEFT);

    if ($kw != '') @$kw_array[strtolower($kw)] += $cnt;
    if (strpos($kw_d,'(圖片)') > -1) $kw = "<span style='color:green'>$kw_d</span>";

    $css = $i % 2 ? $class .' alternate' : $class;

    if ($i < $max_tophit) echo "<tr class='$css'><td style='background:#eee'>$i_d</td><td><a href='$url' target='_blank'>$url_d</a></td><td>$kw</td><td>$cnt</td></tr>\n";

  }
}

if (!empty($eng_srch)) {top_search($eng_srch, 'eng'); unset($eng_srch);}
if (!empty($sit_srch)) {top_search($sit_srch, 'sit hidden'); unset($sit_srch);}

} else {
  echo "<tr><td></td><td>(No data to display yet...)</td></tr>\n";

}
?>

  </tbody>
 </table>

<br/>
<div>
提示:
<ul>
<li>不同來路或不同中文編碼, 會有相同的關鍵字. 分開顯示的目的, 是可查看到搜索的原始鏈接.</li>
</ul>
</div>

<br/>
<div style="width:250px; margin-bottom:10px; background:#eef; border:1px solid #ccc; -moz-border-radius:12px; -khtml-border-radius:12px; -webkit-border-radius:12px; border-radius:12px; padding:6px 20px;">
關鍵字綜合排行:　(相同關鍵字合併計數)
</div>
 <table class="widefat fixed">
  <thead>
   <tr>
    <th class="manage-column" style="width:6%">排行</th>
    <th class="manage-column" style="width:20%">關鍵字</th>
    <th class="manage-column" style="width:7%">訪問次數</th>
    <th class="manage-column" style="width:6%">排行</th>
    <th class="manage-column" style="width:20%">關鍵字</th>
    <th class="manage-column" style="width:7%">訪問次數</th>
   </tr>
  </thead>

  <tbody id="keyword">

<?php

global $kw_array;
if (!empty($kw_array)) {

  arsort($kw_array);
  $key = array_keys($kw_array);
  $value = array_values($kw_array);
  $array_num = $max_tophit < count($kw_array) ? $max_tophit : count($kw_array);

  for ($i = 0; $i < $array_num / 2; $i++) {
    $j = ceil($array_num / 2) + $i; // 左右分欄, 由 $i 算得 $j
    $i_d = str_pad($i + 1, 3, '0', STR_PAD_LEFT);
    $j_d = str_pad($j + 1, 3, '0', STR_PAD_LEFT);

    $tr_color = $i % 2 ? '' : 'class="alternate"';

    echo "<tr $tr_color><td class='nc' style='background:#eee'>$i_d</td><td>$key[$i]</td><td>$value[$i]</td>";
    if ($j < $array_num) echo "<td class='nc' style='background:#eee;border-left:1px solid #ddd'>$j_d</td><td>$key[$j]</td><td>$value[$j]</td>";
    echo "</tr>";
  }

} else {
  echo "<tr><td></td><td>(No data to display yet...)</td></tr>\n";

}
?>

  </tbody>
 </table>

</div>

<br class="clear" />

<script type="text/javascript">
//<![CDATA[
jQuery(document).ready(function($){
 $('#radio_post input:radio,#radio_search input:radio').bind('focus',function(){if(this.blur)this.blur()});
 $('#radio_post input:radio,#radio_search input:radio').attr('checked',false);$('#radio_post input:radio:first,#radio_search input:radio:first').attr('checked',true);
 $('#top_hit tr,#top_reffer tr,#top_search tr').mouseover(function(){$(this).css('background','#def')}).mouseout(function(){$(this).css('background','')});
 $('#keyword td').not('.nc').mouseover(function(){$(this).css('background','#def');$(this).prev().not('.nc').css('background','#def');$(this).next().not('.nc').css('background','#def')}).mouseout(function(){$(this).css('background','');$(this).prev().not('.nc').css('background','');$(this).next().not('.nc').css('background','')});
});
//]]>
</script>

<?php
 } else {
  echo "(No data to display yet...)";

 }
 $memory_limit = (int)ini_get('memory_limit');
 $memory_get_peak_usage = round(memory_get_peak_usage()/1024/1024, 2);
 $memory_peak_usage_percent = round($memory_get_peak_usage/$memory_limit*100, 0);
 $plug_usage = $memory_get_peak_usage - $system_usage;
 $now_usage = round(memory_get_usage()/1024/1024, 2) - $system_usage;
 echo "<br/><span class='subsubsub'>loading ", get_num_queries(), " queries, ", timer_stop(), " seconds.
 <br/>可用內存: $memory_limit MB, 峰值佔用: $memory_get_peak_usage MB, 佔用比例: $memory_peak_usage_percent%
 <br/>系統佔用: $system_usage MB, 本插件峰值佔用: $plug_usage MB, 本插件目前佔用: $now_usage MB</span>";
?>

</div>