<?php
/**
 * 访客统计圖
 */
defined('ABSPATH') or die('This file can not be loaded directly.');
$system_usage = round(memory_get_usage()/1024/1024, 2); ?>

<div id="w" class="wrap">
 <div class="icon32"><img src="<?php echo plugins_url('images/stat32.gif', __FILE__) ?>" alt="" /></div>
 <h2>访客统计图</h2>
<br/>
<div id="radio_area">
图标类型:　
   <label><input type="radio" name="tu" onclick="jQuery('.ht, .hc, .sc').hide();jQuery('.st').show()" value="st" /> 点击量堆叠图</label>　
   <label><input type="radio" name="tu" onclick="jQuery('.st, .hc, .sc').hide();jQuery('.ht').show()" value="ht" /> 点击量直方图</label>　
   <label><input type="radio" name="tu" onclick="jQuery('.ht, .st, .hc').hide();jQuery('.sc').show()" value="sc" /> 访客人数堆叠图</label>　
   <label><input type="radio" name="tu" onclick="jQuery('.ht, .st, .sc').hide();jQuery('.hc').show()" value="hc" /> 访客人数直方图</label>
</div>
<div style="float:left;width:0;margin:10px 0 0 5px;color:#bbb">数量</div>
<div style="width:100%;height:250px;margin:8px 0 24px 0;border-top:1px solid #ccd;border-bottom:3px solid #aac;background:#f2f2f8">
  <div class="board"></div><div class="board"></div><div class="board"></div><div class="board"></div>
</div>
<div id="t" style="float:right;color:#bbb;margin-bottom:8px">日期</div>

<?php

 if (!isset($_GET['w'])) { // clientWidth & offsetTop
   echo "<script type='text/javascript'>\nlocation.href='${_SERVER['SCRIPT_NAME']}?${_SERVER['QUERY_STRING']}&c='+document.body.clientWidth+'&w='+document.getElementById('w').offsetWidth+'&t='+document.getElementById('t').offsetTop;\n</script>\n";
   exit();
 }

 $options = get_option('my_visitors');
 $show_days = $options[6]; # option $show_days
 $end_date = gmdate('Y-m-d', time() + get_option('gmt_offset')*3600 - $show_days*24*3600); // 算出分界

// database query
 global $wpdb, $visitors;
 $sel = "SELECT date_gmt, COUNT(ip) AS hit, COUNT(DISTINCT ip) AS cnt FROM $visitors WHERE";
 $rq = "date_gmt > '$end_date' GROUP BY date_gmt ORDER BY date_gmt DESC";

 $date = $wpdb->get_col("SELECT date_gmt FROM $visitors WHERE $rq");              // query 1/10

if (!empty($date)) {

 $bo = $wpdb->get_results("$sel os='' AND browser!='' AND browser!='?' AND $rq"); // query 2/10
 $ie = $wpdb->get_results("$sel os!='' AND browser='MSIE' AND $rq");              // query 3/10
 $fx = $wpdb->get_results("$sel os!='' AND browser='Firefox' AND $rq");           // query 4/10
 $gc = $wpdb->get_results("$sel os!='' AND browser LIKE 'Chrome%' AND $rq");      // query 5/10
 $op = $wpdb->get_results("$sel os!='' AND browser LIKE 'Opera' AND $rq");        // query 6/10
 $sf = $wpdb->get_results("$sel os!='' AND browser='Safari' AND $rq");            // query 7/10
 $mb = $wpdb->get_results("$sel os='Mobile' AND $rq");                            // query 8/10
 $un = $wpdb->get_results("$sel (browser='' OR browser='?') AND $rq");            // query 9/10
 $ot = $wpdb->get_results("$sel os!='' AND browser!='' AND browser!='?' AND browser!='MSIE' AND browser!='Firefox' AND browser NOT LIKE 'Chrome%' AND browser NOT LIKE 'Opera%' AND browser!='Safari' AND os!='Mobile' AND $rq"); // query 10/10

 foreach($bo as $a) {$d = $a->date_gmt; $bo_hit[$d] = $a->hit; $bo_cnt[$d] = $a->cnt;}
 foreach($ie as $a) {$d = $a->date_gmt; $ie_hit[$d] = $a->hit; $ie_cnt[$d] = $a->cnt;}
 foreach($fx as $a) {$d = $a->date_gmt; $fx_hit[$d] = $a->hit; $fx_cnt[$d] = $a->cnt;}
 foreach($gc as $a) {$d = $a->date_gmt; $gc_hit[$d] = $a->hit; $gc_cnt[$d] = $a->cnt;}
 foreach($op as $a) {$d = $a->date_gmt; $op_hit[$d] = $a->hit; $op_cnt[$d] = $a->cnt;}
 foreach($sf as $a) {$d = $a->date_gmt; $sf_hit[$d] = $a->hit; $sf_cnt[$d] = $a->cnt;}
 foreach($mb as $a) {$d = $a->date_gmt; $mb_hit[$d] = $a->hit; $mb_cnt[$d] = $a->cnt;}
 foreach($un as $a) {$d = $a->date_gmt; $un_hit[$d] = $a->hit; $un_cnt[$d] = $a->cnt;}
 foreach($ot as $a) {$d = $a->date_gmt; $ot_hit[$d] = $a->hit; $ot_cnt[$d] = $a->cnt;}

  $days = count($date);
  $max_h_hit = $max_s_hit = $max_h_cnt = $max_s_cnt = 0;

  for ($i = 0; isset($date[$i]); $i++) {
   if (!isset($bo_hit[$date[$i]])) $bo_hit[$date[$i]] = 0;
   if (!isset($ie_hit[$date[$i]])) $ie_hit[$date[$i]] = 0;
   if (!isset($fx_hit[$date[$i]])) $fx_hit[$date[$i]] = 0;
   if (!isset($gc_hit[$date[$i]])) $gc_hit[$date[$i]] = 0;
   if (!isset($op_hit[$date[$i]])) $op_hit[$date[$i]] = 0;
   if (!isset($sf_hit[$date[$i]])) $sf_hit[$date[$i]] = 0;
   if (!isset($mb_hit[$date[$i]])) $mb_hit[$date[$i]] = 0;
   if (!isset($un_hit[$date[$i]])) $un_hit[$date[$i]] = 0;
   if (!isset($ot_hit[$date[$i]])) $ot_hit[$date[$i]] = 0;
   if (!isset($bo_cnt[$date[$i]])) $bo_cnt[$date[$i]] = 0;
   if (!isset($ie_cnt[$date[$i]])) $ie_cnt[$date[$i]] = 0;
   if (!isset($fx_cnt[$date[$i]])) $fx_cnt[$date[$i]] = 0;
   if (!isset($gc_cnt[$date[$i]])) $gc_cnt[$date[$i]] = 0;
   if (!isset($op_cnt[$date[$i]])) $op_cnt[$date[$i]] = 0;
   if (!isset($sf_cnt[$date[$i]])) $sf_cnt[$date[$i]] = 0;
   if (!isset($mb_cnt[$date[$i]])) $mb_cnt[$date[$i]] = 0;
   if (!isset($un_cnt[$date[$i]])) $un_cnt[$date[$i]] = 0;
   if (!isset($ot_cnt[$date[$i]])) $ot_cnt[$date[$i]] = 0;

    // hit counter
    $ex_hit[$date[$i]] = $ie_hit[$date[$i]] + $fx_hit[$date[$i]] + $gc_hit[$date[$i]] + $op_hit[$date[$i]] + $sf_hit[$date[$i]] + $mb_hit[$date[$i]] + $ot_hit[$date[$i]];
    $dy_hit[$date[$i]] = $bo_hit[$date[$i]] + $ex_hit[$date[$i]] + $un_hit[$date[$i]];

    // cnt counter
    $ex_cnt[$date[$i]] = $ie_cnt[$date[$i]] + $fx_cnt[$date[$i]] + $gc_cnt[$date[$i]] + $op_cnt[$date[$i]] + $sf_cnt[$date[$i]] + $mb_cnt[$date[$i]] + $ot_cnt[$date[$i]];
    $dy_cnt[$date[$i]] = $bo_cnt[$date[$i]] + $ex_cnt[$date[$i]] + $un_cnt[$date[$i]];

    // array push
    $hit[] = array($dy_hit[$date[$i]], $bo_hit[$date[$i]], $ie_hit[$date[$i]], $fx_hit[$date[$i]], $gc_hit[$date[$i]], $op_hit[$date[$i]], $sf_hit[$date[$i]], $mb_hit[$date[$i]], $ot_hit[$date[$i]], $un_hit[$date[$i]], $ex_hit[$date[$i]]);
    $cnt[] = array($dy_cnt[$date[$i]], $bo_cnt[$date[$i]], $ie_cnt[$date[$i]], $fx_cnt[$date[$i]], $gc_cnt[$date[$i]], $op_cnt[$date[$i]], $sf_cnt[$date[$i]], $mb_cnt[$date[$i]], $ot_cnt[$date[$i]], $un_cnt[$date[$i]], $ex_cnt[$date[$i]]);

    // max value
    $max_vr = max($ie_hit[$date[$i]], $fx_hit[$date[$i]], $gc_hit[$date[$i]], $op_hit[$date[$i]], $sf_hit[$date[$i]], $mb_hit[$date[$i]], $ot_hit[$date[$i]]);
    if ($max_vr > $max_h_hit)  $max_h_hit = $max_vr;
    if ($ex_hit[$date[$i]] > $max_s_hit)  $max_s_hit = $ex_hit[$date[$i]];

    $max_vr = max($ie_cnt[$date[$i]], $fx_cnt[$date[$i]], $gc_cnt[$date[$i]], $op_cnt[$date[$i]], $sf_cnt[$date[$i]], $mb_cnt[$date[$i]], $ot_cnt[$date[$i]]);
    if ($max_vr > $max_h_cnt)  $max_h_cnt = $max_vr;
    if ($ex_cnt[$date[$i]] > $max_s_cnt)  $max_s_cnt = $ex_cnt[$date[$i]];

  }

if ($max_s_hit * $max_h_hit * $max_s_cnt * $max_h_cnt != 0) { 

/* thead */
?>
 <table class="widefat post fixed">
  <thead>
   <tr>
    <th class="manage-column" style="width:7%">日期      </th>
    <th class="manage-column" style="width:10%">综合浏览量</th>
    <th class="manage-column" style="width:9%">爬虫      </th>
    <th class="manage-column" style="width:9%">MSIE      <div class="th-box c-ie"></div></th>
    <th class="manage-column" style="width:8%">Firefox   <div class="th-box c-fx"></div></th>
    <th class="manage-column" style="width:8%">Chrome    <div class="th-box c-gc"></div></th>
    <th class="manage-column" style="width:7%">Opera     <div class="th-box c-op"></div></th>
    <th class="manage-column" style="width:7%">Safari    <div class="th-box c-sf"></div></th>
    <th class="manage-column" style="width:6%">手机      <div class="th-box c-mb"></div></th>
    <th class="manage-column" style="width:7%">其它      <div class="th-box c-ot"></div></th>
    <th class="manage-column" style="width:7%">未知      </th>
    <th class="manage-column" style="width:9%">访问数    </th>
   </tr>
  </thead>

<?php

/* 圖形基本參數 (高度用 px 固定, 寬度用 % 自適應窗口寬度)
 * 這邊用的是 absolute-top-right, 窗口右上角為座標起點.
 */
  $client_width = isset($_GET['c']) ? $_GET['c'] : 1280;     // 取 js 記錄的窗口寬度
  $wrap_width   = isset($_GET['w']) ? $_GET['w'] : 821;      // 取 js 記錄的物件寬度
  $bottom       = isset($_GET['t']) ? $_GET['t'] - 27 : 408; // 取 js 記錄的圖表基準位置

  $max_height = 240;                       // max-height(240px)
  $st_unit_hight = $max_height/$max_s_hit; // 點擊量堆疊單位高度(px)
  $ht_unit_hight = $max_height/$max_h_hit; // 點擊量直方單位高度(px)
  $sc_unit_hight = $max_height/$max_s_cnt; // 访客人數堆疊單位高度(px)
  $hc_unit_hight = $max_height/$max_h_cnt; // 访客人數直方單位高度(px)

  $margin_left = $client_width - $wrap_width + 12; // margin-left(px)
  $margin_right= 18;                       // margin-right(18px)
  $right_space = $margin_right/$client_width*100; // margin-right 化為 % (用於加法要乘100)

  $scale_rate = .8;                        // 方塊縮小寬度(80%), 相當於留出 padding
  $padding_rate = (1-$scale_rate)/2;       // 方塊縮小後, 左右的 padding(%)

  $week_name = array("日", "一", "二", "三", "四", "五", "六"); // 中文星期名
  $bs = array('','bo','ie','fx','gc','op','sf','mb','ot','un',''); // browser 簡稱
  $browser = array('','','Internet Explorer','Firefox','Chrome','Opera','Safari','手機','其它');
  $total_hit = array_fill(0, 11, 0);       // hit 合計初始值
  $total_cnt = array_fill(0, 11, 0);       // cnt 合計初始值

  $i = 0;

while (isset($hit[$i])) {

  // 日期換算星期
  $date_array = explode("-", $date[$i]);
  $week = $week_name[date('w', mktime(0, 0, 0, $date_array[1], $date_array[2], $date_array[0]))];

  // 圖形位置計算
  $avg_width = ($client_width-$margin_left-$margin_right)/$client_width/$days*100; // 總寬度(px)減去左右 margin 換算為圖形區域(%)後, 按天數平分(%)
  $day_pos = $right_space + $avg_width*($i+1-$padding_rate); // 每日左邊線位置(%)
  $s_width = $avg_width*$scale_rate;             // 堆疊寬度(%)
  $s_right = $day_pos-$s_width;                  // 堆疊右邊線位置(%)
  $split_width = $s_width/7;                     // 直方平分 7 單元(%)
  $h_width = round($split_width*$scale_rate, 3); // 直方寬度(%)
  $split_padding = $split_width*$padding_rate;   // 直方 padding(%)


/* 點擊量堆疊圖 */
  echo "<tr class='st'>\n";
  for ($j = 2; $j < 9; $j++) {
    $right  = round($s_right, 3) .'%';
    $width  = round($s_width, 3) .'%';
    $tmp = 0;
    for ($k = $j; $k < 9; $k++) {
      $tmp += $hit[$i][$k];
    }
    $top    = round($bottom - $tmp*$st_unit_hight) .'px';
    $height = round($hit[$i][$j]  *$st_unit_hight) .'px';
    $count  = $hit[$i][$j];
    echo " <td class='c-$bs[$j] block absolute' style='right:$right; width:$width; top:$top; height:$height' abbr=\"$browser[$j]: <b>$count</b>\"></td>\n";
  }

  echo "</tr>\n";

/* 點擊量直方圖 */
  echo "<tr class='ht hidden'>\n";
  for ($j = 2; $j < 9; $j++) {
    $right  = round($day_pos - $split_width*($j-1) + $split_padding, 3) .'%';
    $width  = $h_width .'%';
    $top    = round($bottom - $hit[$i][$j]*$ht_unit_hight) .'px';
    $height = round($hit[$i][$j]          *$ht_unit_hight) .'px';
    $count  = $hit[$i][$j];
    echo " <td class='c-$bs[$j] block absolute' style='right:$right; width:$width; top:$top; height:$height' abbr='$browser[$j]: <b>$count</b>'></td>\n";
  }
  echo "</tr>\n";

/* 访客人數堆疊圖 */
  echo "<tr class='sc hidden'>\n";
  for ($j = 2; $j < 9; $j++) {
    $right  = round($s_right, 3) .'%';
    $width  = round($s_width, 3) .'%';
    $tmp = 0;
    for ($k = $j; $k < 9; $k++) {
      $tmp += $cnt[$i][$k];
    }
    $top    = round($bottom - $tmp*$sc_unit_hight) .'px';
    $height = round($cnt[$i][$j]  *$sc_unit_hight) .'px';
    $count  = $cnt[$i][$j];
    echo " <td class='c-$bs[$j] block absolute' style='right:$right; width:$width; top:$top; height:$height' abbr=\"$browser[$j]: <b>$count</b>\"></td>\n";
  }
  echo "</tr>\n";

/* 访客人數直方圖 */
  echo "<tr class='hc hidden'>\n";
  for ($j = 2; $j < 9; $j++) {
    $right  = round($day_pos - $split_width*($j-1) + $split_padding, 3) .'%';
    $width  = $h_width .'%';
    $top    = round($bottom - $cnt[$i][$j]*$hc_unit_hight) .'px';
    $height = round($cnt[$i][$j]          *$hc_unit_hight) .'px';
    $count  = $cnt[$i][$j];
    echo " <td class='c-$bs[$j] block absolute' style='right:$right; width:$width; top:$top; height:$height' abbr='$browser[$j]: <b>$count</b>'></td>\n";
  }
  echo "</tr>\n";

/* 橫座標 */
  $right = round($s_right, 3) .'%';
  $width = round($s_width, 3) .'%';
  $top   = ($bottom + 3) .'px';
  $abbr  = "$date[$i] ($week)";
  $hits = $hit[$i][10];
  $count = $cnt[$i][10];
  $s_date  = substr($date[$i], strrpos($date[$i], "-") + 1);
  echo "<tr>\n <td class='c-dt block absolute' style='right:$right; width:$width; top:$top' abbr=\"$abbr<br/> <span style='color:#21759b'>訪問數:</span> <b>$hits ($count)</b>\">$s_date</td>\n</tr>\n";

/* 表格 */
  $guery_link = $_SERVER['SCRIPT_NAME'] .'?page=visitor-details&amp;d='. $date[$i];
  $tr_color = $i % 2 ? 'class="alternate"' : '';
?>
<tr id="<?php echo 'date-', $date[$i] ?>"<?php echo $tr_color; ?>>
 <td><?php echo substr($date[$i], 5), " (", $week, ")" ?></td>

<?php

  for ($j = 0; $j < 11; $j++) {
    $hits  = $hit[$i][$j];
    $count = $cnt[$i][$j];

    $span1 = $span2 = $ac1 = $ac2 = '';
    $att = ($j == 0) ? ' style="background:#d8d8df"' : (($j == 1 || $j == 9) ? ' style="background:#eef"' : '');
    if ($j == 10) {$att = ' class="ex" style="background:#ccd;color:#21759b;font-weight:700"'; $span1 = '<span style="color:#555">'; $span2 = '</span>';}
    $bsq = ($j > 0 && $j < 10) ? "&amp;b=$bs[$j]" : '';
    if ($j < 10) {$ac1 = "<a href='$guery_link$bsq' target='_blank'>"; $ac2 = '</a>';}

    echo " <td$att>$ac1$hits$ac2 $span1($count)$span2</td>\n";

    $total_hit[$j] = $total_hit[$j] + $hit[$i][$j];
    $total_cnt[$j] = $total_cnt[$j] + $cnt[$i][$j];
  }

  echo "</tr>\n";
  $i++;

} // end while

/* 縱座標 */
 $right = ($avg_width*$days + 1) .'%';
 $unit_st = round($max_s_hit*255/240/5, 1);
 $unit_sc = round($max_s_cnt*255/240/5, 1);
 $unit_ht = round($max_h_hit*255/240/5, 1);
 $unit_hc = round($max_h_cnt*255/240/5, 1);

 echo "<tr style='color:#aaa'>";
 for ($i = 0; $i < 250; $i += 51) {
  $top = ($bottom - 3 - $i) .'px';
  $scale_st = $max_s_hit < 10 ? round($i*$unit_st/52,1) : round($i*$unit_st/52);
  $scale_sc = $max_s_cnt < 10 ? round($i*$unit_sc/52,1) : round($i*$unit_sc/52);
  $scale_ht = $max_h_hit < 10 ? round($i*$unit_ht/52,1) : round($i*$unit_ht/52);
  $scale_hc = $max_h_cnt < 10 ? round($i*$unit_hc/52,1) : round($i*$unit_hc/52);
  echo " <td class='st absolute' style='right:$right; top:$top; border:none;font-size:8px;'>$scale_st</td>\n";
  echo " <td class='sc absolute hidden' style='right:$right; top:$top; border:none;font-size:8px;'>$scale_sc</td>\n";
  echo " <td class='ht absolute hidden' style='right:$right; top:$top; border:none;font-size:8px;'>$scale_ht</td>\n";
  echo " <td class='hc absolute hidden' style='right:$right; top:$top; border:none;font-size:8px;'>$scale_hc</td>\n";

 }
 echo "</tr>";

/* 合計 */
 echo "<tr class='tt' style='background:#cfc;color:#256;font-weight:700'>\n <td>合計</td>";
 for ($i = 0; $i < 11; $i++) {
   echo "<td>$total_hit[$i] ($total_cnt[$i])</td>";
 }
 echo "\n</tr>\n</table>\n";

/* 百分比 */
 echo "<br/>\n<div style=''>各浏览器所占比例: <span style='font-size:11px;color:#666'>(以 \"唯一 IP\" 计数)</span></div>\n<table style='width:100%;height:24px'>\n<tr>\n";
 $sum = array_sum(array_slice($total_cnt, 2, 7));
 for ($i = 2; $i < 9; $i++) {
   $percent[$i] = round($total_cnt[$i]*100 / $sum, 2). "%";
   echo " <td class='c-$bs[$i] block' style='width:$percent[$i];cursor:default' abbr='$browser[$i]: <b>$percent[$i]</b>'>&nbsp;</td>\n";
 }
 echo "</tr>\n</table>";
?>

<br/>
<div>
提示: 
<ol>
<li>表格前面数字是 "点击量", 后面括号内的数字是 "唯一 IP 的访客人数". 例: 某人点击 3 个页面, 就是 3 (1).</li>
<li>把"点击量" 除以 "唯一 IP 访客人数", 就是平均浏页数.</li>
</ol>
</div>

<script type="text/javascript">
//<![CDATA[
jQuery(document).ready(function($){
 $('input:radio').bind('focus',function(){if(this.blur)this.blur()});
 $('input:radio').attr('checked',false); $('input:radio:first').attr('checked',true);
 $('tr .block').prepend("<span style='display:block;margin:0 2px;border:1px solid #eee;-moz-box-shadow: rgba(255,255,255,1) 0 2px 5px;-webkit-box-shadow: rgba(255,255,255,1) 0 2px 5px;-khtml-box-shadow: rgba(255,255,255,1) 0 2px 5px;box-shadow: rgba(255,255,255,1) 0 2px 5px;'><\/span>");
 $('.block').mouseover(function(){ts=$(this).attr('abbr');$('#footer').after('<div id="ts" style="width:250px;position:absolute;bottom:20000px;z-index:999"><div style="float:right;background:#fff;border:1px solid #666;padding:4px 10px;-moz-border-radius:5px;-khtml-border-radius:5px;-webkit-border-radius:5px;border-radius:5px;">'+ts+'<div><\/div>');$('#ts').show();}).mousemove(function(e){$('#ts').css({left: e.pageX-270, top: e.pageY});}).mouseout(function(){$('#ts').hide(0,function(){$(this).remove()});})
 $('.board,td').click(function(){$('tr').not($('.tt')).css({'background':''});$('tr td.ex').css({'background':'#ccd','color':'#21759b'});})
 $('.c-dt').click(function(){dt = $(this).attr('abbr').substr(0,10);$('#date-'+dt).css({'background':'#def'});$('#date-'+dt).children('td.ex').css({'background':'#abe','color':'#245'});})
});
//]]>
</script>

<?php
  } else {
   echo "(There seems only robots, no human yet...)";
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
 <br/>可用内存: $memory_limit MB, 峰值占用: $memory_get_peak_usage MB, 占用比例: $memory_peak_usage_percent%
 <br/>系統占用: $system_usage MB, 本插件峰值占用: $plug_usage MB, 本插件目前占用: $now_usage MB</span>";
?>
</div>