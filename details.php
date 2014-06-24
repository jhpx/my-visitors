<?php
/**
 * 訪客細節清單
 */
defined('ABSPATH') or die('This file can not be loaded directly.');
$system_usage = round(memory_get_usage()/1024/1024, 2); ?>

<div class="wrap">
 <div class="icon32"><img src="<?php echo plugins_url('images/details.gif', __FILE__) ?>" alt="" /></div>
 <h2>访客细节清单</h2>

<?php
// database query
 global $wpdb, $visitors;

 $query_date = $wpdb->get_results("SELECT COUNT(id) AS cnt, date_gmt FROM $visitors GROUP BY date_gmt ORDER BY date_gmt DESC"); // query 1/3 .. 查询所有日期及当日记录数

 if (!empty($query_date)) {

 $days = 0;
 foreach ($query_date as $sq_date) {
   $all_date[$days] = $sq_date->date_gmt;
   $day_hits[$days] = $sq_date->cnt;
   $days++;
 }

 $query_date = isset($_POST['req_date']) ? $_POST['req_date'] : (isset($_GET['d']) ? $_GET['d'] : $all_date[0]); // 選擇日期
 $status     = $wpdb->get_results("SHOW TABLE STATUS LIKE '$visitors'"); // query 2/3 .. 查詢表頭
    foreach ($status as $stus) {
      $total_hits   = $stus->Rows;
      $avg_length   = $stus->Avg_row_length;
    }

 $results = $wpdb->get_results("SELECT * FROM $visitors WHERE date_gmt LIKE '$query_date' ORDER BY id DESC"); // query 3/3 .. 取出當日記錄, $wpdb->get_results 會自動分段取資料, 查詢次數不定

  $browser_query = isset($_GET['b']) ? $_GET['b'] : 'browser';
  $tr_css = $browser_query != 'browser' ? 'none' : '';
?>

<br/>

<div id="ld" style="display:none; color:#D54E21; opacity: 0.5; font-weight:700; margin-left:10px; padding:6px; border 1px solid #f00;">资料尚未完全载入, 请稍待...</div>
<div id="hover_area" onmouseover="jQuery('#cb').show()" onmouseout="jQuery('#cb').hide()" style="display:block; width:90px; margin-left:20px; padding:5px 0; text-align:center; background:#eef; border:1px solid #ccc; -moz-border-radius:12px; -khtml-border-radius:12px; -webkit-border-radius:12px; border-radius:12px;">筛选项目
 <div id="cb" style="display:none; position:absolute; width:820px; margin-top:-24px; margin-left:-1px; padding:5px 20px; text-align:center; background:#eef; border:1px solid #ccc; -moz-border-radius:12px; -khtml-border-radius:12px; -webkit-border-radius:12px; border-radius:12px;">

<script type="text/javascript">
//<![CDATA[
document.getElementById('hover_area').style.display='none';
document.getElementById('ld').style.display='';

if(navigator.appName != 'Microsoft Internet Explorer'){t=40;txt_fs();}
function txt_fs(){ld=document.getElementById("ld");if(t!=0){ld.style.color='#'+parseInt(Math.random()*899)+100;t--;setTimeout(txt_fs,100);}else{ld.style.color ='green';}}

jQuery(document).ready(function($){
 $('input:button, input:submit').bind('focus',function(){if(this.blur)this.blur()});
 $('input:checkbox').attr('checked',false);
 $('#<?php echo $browser_query ?> input:checkbox').attr('checked',true);
 $('#tb tr').css('display','<?php echo $tr_css ?>');
 $('.<?php echo $browser_query ?>').css('display','');
 $('input').click(function(){ck=$('.'+$(this).val());ck.css('display')=='none'?ck.css('display',''):ck.css('display','none')});
 $('#hover_area').show();$('#ld').hide();
 $('#tb tr').mouseover(function(){$(this).css('background','#def')}).mouseout(function(){$(this).css('background','')});
});
//]]>
</script>

  <p id="browser" class="popular-tags">浏览器: 　
   <label id="bo"><input type="checkbox" value="bo" /> 爬虫</label>　
   <label id="ie"><input type="checkbox" value="ie" /> Internet Explorer</label>　
   <label id="fx"><input type="checkbox" value="fx" /> Firefox</label>　
   <label id="gc"><input type="checkbox" value="gc" /> Chrome</label>　
   <label id="op"><input type="checkbox" value="op" /> Opera</label>　
   <label id="sf"><input type="checkbox" value="sf" /> Safari</label>　
   <label id="mb"><input type="checkbox" value="mb" /> 手机</label>　
   <label id="ot"><input type="checkbox" value="ot" /> 其它</label>　
   <label id="un"><input type="checkbox" value="un" /> 未知</label>
  </p>

  <p class="popular-tags">操作系统:　
   <label><input type="checkbox" value="win" /> Windows</label>　
   <label><input type="checkbox" value="mac" /> Mac</label>　
   <label><input type="checkbox" value="ubt" /> Ubuntu</label>　
   <label><input type="checkbox" value="lnx" /> Linux</label>　
   <label><input type="checkbox" value="ots" /> 其它</label>
  </p>

  <p class="popular-tags">访问方式:　
   <label><input type="checkbox" value="drct" /> 直接访问</label>　
   <label><input type="checkbox" value="in"   /> 站內停留</label>　
   <label><input type="checkbox" value="out"  /> 来自其它网站</label>　
   <label><input type="checkbox" value="srch" /> 关键字或图片搜索</label>
  </p>

  <p class="popular-tags">访客类型:　
   <label><input type="checkbox" value="friend" /> 已知访客</label>　
   <label><input type="checkbox" value="anymus" /> 未知访客</label>
  </p>

  <p class="popular-tags">出错页面:　
   <label><input type="checkbox" value="e404" /> 404</label>　
   <label><input type="checkbox" value="h200" /> 非 404</label>
  </p>

  <p>筛选重置:　
   <input type="button" class="button" name="alchk" value="全选" onclick="jQuery('input:checkbox').attr('checked',false);jQuery('#browser input:checkbox').attr('checked',true);jQuery('#tb tr').css('display','')"/>　
   <input type="button" class="button" name="unchk" value="全不选" onclick="jQuery('input:checkbox').attr('checked',false);jQuery('#tb tr').css('display','none')"/>　<span style="color:#e66">跨栏选取的方式为 "排除". (注意: 排除的范围可能会重叠, 不一定正确)</span>
  </p>

 </div><!-- cb -->
</div>


<form id="date-settings" action="" method="post" style="float:left; padding:6px;">
<select name='req_date'>

<?php
 $i = 0;
 while ($i < $days) {
   $the_date = $all_date[$i];
   $selected = '';
   if ($query_date == $the_date) {$selected = "selected='selected'"; $the_day_hits = $day_hits[$i]; }
   echo "<option value='$the_date' $selected>$the_date</option>\n";
   $i++;
 }
?>

</select> <input type='submit' class='button' value='应用' />　
</form>

<ul class="subsubsub">
<li>当日 (<?php echo number_format($the_day_hits) ?>) |</li>
<li>全部 (<?php echo number_format($total_hits) ?>) |</li>
<li>记录 (<?php echo $days ?> 天) |</li>
<li>每笔记录平均 (<?php echo $avg_length ?> Bytes)</li>
</ul>

<?php
  // table layout
  include('table.php');

 } else {
  echo "(No data to display yet...)";

 }
 $memory_limit = (int)ini_get('memory_limit');
 $memory_get_peak_usage = round(memory_get_peak_usage()/1024/1024, 2);
 $memory_peak_usage_percent = round($memory_get_peak_usage/$memory_limit*100, 0);
 $plug_usage = $memory_get_peak_usage - $system_usage;
 $now_usage = round(memory_get_usage()/1024/1024, 2) - $system_usage;
 echo "<br/><span class='subsubsub'>loading ", get_num_queries(), " queries, ", timer_stop(), " seconds.
 <br/>可用内存: $memory_limit MB, 峰值佔用: $memory_get_peak_usage MB, 佔用比例: $memory_peak_usage_percent%
 <br/>系統佔用: $system_usage MB, 本插件峰值佔用: $plug_usage MB, 本插件目前佔用: $now_usage MB</span>";
?>

</div>