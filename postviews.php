<?php
/**
 * 文章點擊 (postviews)
 */

if (isset($_GET['postviews_id'])) { // for Ajax
 require_once('../../../wp-load.php');
 global $wpdb;
 $post_ID = $_GET['postviews_id'];
 if($post_ID) {
  $post_views = (int)get_post_meta($post_ID, 'views', true);
  if(!update_post_meta($post_ID, 'views', ($post_views+1))) {
   add_post_meta($post_ID, 'views', 1, true);
  }
  exit;
 }
} 

else defined('ABSPATH') or die('This file can not be loaded directly.');
$system_usage = round(memory_get_usage()/1024/1024, 2); ?>

<div class="wrap">
 <div class="icon32" style="background:url(<?php echo plugins_url('my-visitors/images/arrow.gif'); ?>) no-repeat;"></div>
 <h2>文章點擊</h2>
 　<b>(簡易的 PostViews)</b><br/>

<br/>

<div style="width:400px; margin-bottom:10px; background:#eef; border:1px solid #ccc; -moz-border-radius:12px; -khtml-border-radius:12px; -webkit-border-radius:12px; border-radius:12px; padding:6px 20px;">
文章點擊排名:
<div style="text-align:center">echo '&lt;ol>', get_most_viewed('both', 25, 1), '&lt;/ol>';</div>
</div>
<?php echo '<ol>', get_most_viewed('both', 25, 1), '</ol>';?>

<br/><br/>

<div style="width:150px; margin-bottom:10px; background:#eef; border:1px solid #ccc; -moz-border-radius:12px; -khtml-border-radius:12px; -webkit-border-radius:12px; border-radius:12px; padding:6px 20px;">
函數使用說明:
</div>

<div style="font-weight:700">
post_views();<br/>
</div><br/>
語法: post_views($before, $after, $echo);<br/><br/>

<div style="color:#339">
文章點擊次數 (只能在 loop 中使用)<br/>
因為 WP-PostViews 的 the_views() 未寫重覆函數的判斷, 所以要新建函數, 避免共用時衝突.<br/>
主題模板的所有 the_views() 請改用 post_views().<br/>
</div><br/>
$before: 前提示語, 可選, 字串, 默認值 = '(點擊 ';<br/>
$after: 後提示語, 可選, 字串, 默認值 = ' 次)';<br/>
$echo: 是否顯示, 可選, 布林, 默認值 = 1 顯示, (0 用於運算, 不顯示);<br/><br/>
範例: post_views();<br/>
顯示: (點擊 xxx 次)<br/><br/>
範例: post_views('共有 ', ' 次圍觀');<br/>
顯示: 共有 xxx 次圍觀<br/><br/>

<div style="font-weight:700">
get_totalviews();<br/>
</div><br/>
語法: get_totalviews($echo);<br/><br/>

<div style="color:#339">
所有點擊次數合計<br/>
此函數有重覆函數的判斷, 與 WP-PostViews 的 get_totalviews() 完全相同.<br/>
</div><br/>
$echo: 是否顯示, 可選, 布林, 默認值 = 1 顯示, (0 用於運算, 不顯示);<br/><br/>
範例: get_totalviews();<br/>
顯示: <?php get_totalviews();?><br/><br/>

<div style="font-weight:700">
get_most_viewed();<br/>
</div><br/>
語法: get_most_viewed($mode, $limit, $show_date, $term_id, $after);<br/><br/>

<div style="color:#339">
點擊最多文章<br/>
此函數與 WP-PostViews 的 get_most_viewed() 很接近, 新建的函數.<br/>
get_most_viewed_category 和 get_most_viewed_tag() 可以用這個 get_most_viewed() 取代, 但要改變參數.<br/>
</div><br/>
$mode: 文章或頁面, 可選, 字串. 'page' 或 'post' 或 'both', 默認值 = 'post';<br/>
$limit: 數量, 可選, 正整數, 默認值 = 10;<br/>
$show_date: 是否顯示日期, 可選, 布林, 默認值 = 0 不顯示;<br/>
$term_id: 分類或標簽 ID, 可選, 正整數, 默認值 = 0;<br/>
$after: 後提示語, 可選, 字串, 默認值 = ' 次點擊';<br/><br/>
範例:  echo '&lt;ul>', get_most_viewed(), '&lt;/ul>';<br/>
顯示: <?php echo '<ul>', get_most_viewed(), '</ul>'; ?>
(可應用於側邊欄)<br/><br/>
範例:  echo '&lt;ol>', get_most_viewed('', 5, 1, array(1, 3)), '&lt;/ol>';<br/>
顯示: <?php echo '<ol>', get_most_viewed('', 5, 1, array(1, 3)), '</ol>';?>
(多個 category 或 tag 的相關文章, 加日期)<br/><br/>
範例:  echo '&lt;ol>', get_most_viewed('', 5, 0, 4), '&lt;/ol>';<br/>
顯示: <?php echo '<ol>', get_most_viewed('', 5, 0, 4), '</ol>';?>
(只用一個 category 或 tag 的相關文章)<br/><br/>
<div><br/>



提示: 
<ol>
<li>執行錯誤請先比對參數用法.</li>
<li>這只能取代 WP-PostViews 的部份功能, 若需要高級功能, 請安裝 WP-PostViews.</li>
<li>若要改寫函數, 這三個函數在 my-visitors.php 最下面可找到.</li>
<li>計數器在 record.php 最上面可找到.</li>
<li>可在 '管理選項' 中設定 '不記錄的訪問者'.</li>
</ol>
</div>

<?php
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