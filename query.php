<?php
/**
 * 記錄查詢
 */
defined('ABSPATH') or die('This file can not be loaded directly.');
$system_usage = round(memory_get_usage()/1024/1024, 2); ?>

<div class="wrap">
 <div class="icon32"><img src="<?php echo plugins_url('images/query32.png', __FILE__) ?>" alt="" /></div>
 <h2>记录查询</h2>

<?php

 global $wpdb, $visitors;
 $req_date    = isset($_POST['req_date'])    ? $_POST['req_date']    : '';
 $req_ip      = isset($_POST['req_ip'])      ? $_POST['req_ip']      : '';
 $req_region  = isset($_POST['req_region'])  ? $_POST['req_region']  : '';
 $req_name    = isset($_POST['req_name'])    ? $_POST['req_name']    : '';
 $req_browser = isset($_POST['req_browser']) ? $_POST['req_browser'] : '';
 $req_bot     = isset($_POST['req_bot'])     ? $_POST['req_bot']     : '';
 $req_os      = isset($_POST['req_os'])      ? $_POST['req_os']      : '';
 $req_ots     = isset($_POST['req_ots'])     ? $_POST['req_ots']     : '';

if (isset($_POST['search'])) {

 $sql = "WHERE id!='' ";
 if ($req_date)    $sql.= "AND date_gmt LIKE '$req_date' ";
 if ($req_ip)      $sql.= "AND ip LIKE '$req_ip' ";
 if ($req_region)  $sql.= "AND region LIKE '%$req_region%' ";
 if ($req_name)    $sql.= "AND name LIKE '$req_name%' ";
 if ($req_browser) $sql.= "AND browser LIKE '$req_browser' ";
 if ($req_os)      $sql.= "AND os LIKE '%$req_os%' ";
 if ($req_bot)     $sql.= "AND os='' AND browser LIKE '$req_bot' ";

 // others
 if ($req_ots == '[404]')      $sql.= "AND requrl LIKE '[404]%' ";
 if ($req_ots == '神秘人')     $sql.= "AND browser = '?' ";
 if ($req_ots == '使用代理')   $sql.= "AND (region LIKE '%代理%' OR region LIKE '%真实%') ";
 if ($req_ots == '未知爬蟲')   $sql.= "AND (browser = 'bot' OR browser = 'spider') ";
 if ($req_ots == '未知浏览器') $sql.= "AND browser = '' AND requrl NOT LIKE '[404]%' ";
 if ($req_ots == '危险份子')   $sql.= "AND (browser = '' OR browser = '?') AND requrl LIKE '[404]%' ";
 if ($req_ots == '登入成功')   $sql.= "AND name LIKE '%(l)' ";
 if ($req_ots == '登入错误')   $sql.= "AND name LIKE '%(e)' ";

 // database query
 if ($sql != "WHERE id!='' ") $results = $wpdb->get_results("SELECT * FROM $visitors $sql ORDER BY id DESC"); // query 8/8 .. 桉 $_POST 取出查詢記錄

}

  $date_gmt = $wpdb->get_col("SELECT date_gmt FROM $visitors GROUP BY date_gmt ORDER BY date_gmt DESC"); // query 1/8
  $ip       = $wpdb->get_col("SELECT ip FROM $visitors GROUP BY ip ORDER BY ip+0"); // query 2/8
  $region   = $wpdb->get_col("SELECT region FROM $visitors WHERE region != '' GROUP BY region"); // query 3/8
  $name     = $wpdb->get_col("SELECT name FROM $visitors WHERE name != '' AND name NOT LIKE '%(e)' AND browser != 'WordPress' GROUP BY name"); // query 4/8
  $browser  = $wpdb->get_col("SELECT browser FROM $visitors WHERE os != '' AND browser != '' AND browser != '?' GROUP BY browser ORDER BY browser"); // query 5/8
  $bot      = $wpdb->get_col("SELECT browser FROM $visitors WHERE os = '' AND browser != '' AND browser != '?' AND browser != 'bot' GROUP BY browser ORDER BY browser"); // query 6/8
  $os       = $wpdb->get_col("SELECT os FROM $visitors WHERE os != '' AND os != 'Windows' AND browser != '?' GROUP BY os ORDER BY os"); // query 7/8

 if (!empty($date_gmt)) {

 for ($i = 0; isset($region[$i]); $i++) {
   $tmp = explode(" ", $region[$i]);
   $region[$i] = $tmp[0];
 }
 $region = array_values(array_unique($region));

 for ($i = 0; isset($name[$i]); $i++) {
   $tmp = explode(" ", $name[$i]);
   $name[$i] = $tmp[0];
 }
 $name = array_values(array_unique($name));

   $ots = array('[404]', '神秘人', '使用代理', '未知爬虫', '未知浏览器', '危险份子', '登入成功', '登入错误');
?>

<form id="search" action="" method="post" style="float:left; padding:6px;">
 <p class="popular-tags" style="padding:20px; text-align:center; background:#eef; border:1px solid #ccc; -moz-border-radius:12px; -khtml-border-radius:12px; -webkit-border-radius:12px; border-radius:12px;">
 <input name="search" value="true" type="hidden" />

<?php
// date
 echo "　日期: <select name='req_date'><option value=''>----</option>";
 for ($i = 0; isset($date_gmt[$i]); $i++) {
   $selected = '';
   if ($req_date == $date_gmt[$i]) $selected = "selected='selected'";
   echo "<option value='$date_gmt[$i]' $selected>$date_gmt[$i]</option>";
 }
 echo "</select>\n";

// ip
 echo "　IP: <select name='req_ip'><option value=''>----</option>";
 for ($i = 0; isset($ip[$i]); $i++) {
   $selected = '';
   if ($req_ip == $ip[$i]) $selected = "selected='selected'";
   echo "<option value='$ip[$i]' $selected>$ip[$i]</option>";
 }
 echo "</select>\n";

// region
if (function_exists('curl_init')) {
 echo "　地区: <select name='req_region'><option value=''>----</option>";
 for ($i = 0; isset($region[$i]); $i++) {
   $selected = '';
   if ($req_region == $region[$i]) $selected = "selected='selected'";
   if ($region[$i]) echo "<option value='$region[$i]' $selected>$region[$i]</option>";
 }
 echo "</select>\n";
}

// name
 echo "　名称: <select name='req_name'><option value=''>----</option>";
 for ($i = 0; isset($name[$i]); $i++) {
   $selected = '';
   if ($req_name == $name[$i]) $selected = "selected='selected'";
   echo "<option value='$name[$i]' $selected>$name[$i]</option>";
 }
 echo "</select>\n";

echo "<br class='clear' /><br/>\n";

// browser
 echo "　浏览器: <select name='req_browser'><option value=''>----</option>";
 for ($i = 0; isset($browser[$i]); $i++) {
   $selected = '';
   if ($req_browser == $browser[$i]) $selected = "selected='selected'";
   echo "<option value='$browser[$i]' $selected>$browser[$i]</option>";
 }
 echo "</select>\n";

// bot
 echo "　爬虫: <select name='req_bot'><option value=''>----</option>";
 for ($i = 0; isset($bot[$i]); $i++) {
   $selected = '';
   if ($req_bot == $bot[$i]) $selected = "selected='selected'";
   echo "<option value='$bot[$i]' $selected>$bot[$i]</option>";
 }
 echo "</select>\n";

// os
 echo "　操作系統: <select name='req_os'><option value=''>----</option>";
 for ($i = 0; isset($os[$i]); $i++) {
   $selected = '';
   if ($req_os == $os[$i]) $selected = "selected='selected'";
   echo "<option value='$os[$i]' $selected>$os[$i]</option>";
 }
 echo "</select>\n";

echo "<br class='clear' /><br/>\n";

// others
 echo "　其它: <select name='req_ots' style='margin-right:70px'><option value=''>----</option>";
 for ($i = 0; isset($ots[$i]); $i++) {
   $selected = '';
   if ($req_ots == $ots[$i]) $selected = "selected='selected'";
   echo "<option value='$ots[$i]' $selected>$ots[$i]</option>";
 }
 echo "</select>\n";


?>

 <input type='submit' class='button-highlighted' value='查询'/>　
 </p>
</form>

<script type="text/javascript">
//<![CDATA[
jQuery(document).ready(function($){
 $('input:submit').bind('focus',function(){if(this.blur)this.blur()});
 $('#tb tr').mouseover(function(){$(this).css('background','#def')}).mouseout(function(){$(this).css('background','')});
});
//]]>
</script>

<?php
echo "<br class='clear' />\n";

 // table layout
 if (!empty($results)) {
  include('table.php');
  echo "共找到 ", $cnt, " 笔资料.";

 } elseif (isset($_POST['search'])) {
  echo "<br class='clear' /><div style='margin:0 20px'>", __('Search Results'), ': ', __('No results found.'), "</div>";
 }

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