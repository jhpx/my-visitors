<?php
/**
 * 文章点击 (postviews)
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
 <div class="icon32"><img src="<?php echo plugins_url('images/arrow.gif', __FILE__) ?>" alt="" /></div>
 <h2>文章点击</h2>
 　<b>(简易的 PostViews)</b><br/>

<br/>

<div style="width:400px; margin-bottom:10px; background:#eef; border:1px solid #ccc; -moz-border-radius:12px; -khtml-border-radius:12px; -webkit-border-radius:12px; border-radius:12px; padding:6px 20px;">
文章点击排名:
<div style="text-align:center">echo '&lt;ol>', get_most_viewed('both', 25, 1), '&lt;/ol>';</div>
</div>
<?php echo '<ol>', get_most_viewed('both', 25, 1), '</ol>';?>

<br/><br/>

<div style="width:150px; margin-bottom:10px; background:#eef; border:1px solid #ccc; -moz-border-radius:12px; -khtml-border-radius:12px; -webkit-border-radius:12px; border-radius:12px; padding:6px 20px;">
函数使用说明:
</div>

<div style="font-weight:700">
post_views();<br/>
</div><br/>
语法: post_views($before, $after, $echo);<br/><br/>

<div style="color:#339">
文章点击次数(只能在 loop 中使用)<br/>
因为 WP-PostViews 的 the_views()未写重覆函数的判断, 所以要新建函数, 避免共用时冲突.<br/>
主题模板的所有 the_views() 请改用 post_views().<br/>
</div><br/>
$before: 前提示语, 可选, 字串, 默认值 ='(点击 ';<br/>
$after: 后提示语, 可选, 字串, 默认值 =' 次)';<br/>
$echo: 是否显示, 可选, 布林, 默认值 = 1 显示, (0 用於运算, 不显示);<br/><br/>
范例: post_views();<br/>
显示: (点击 xxx 次)<br/><br/>
范例: post_views('共有 ', '次围观');<br/>
显示: 共有 xxx 次围观<br/><br/>

<div style="font-weight:700">
get_totalviews();<br/>
</div><br/>
语法: get_totalviews($echo);<br/><br/>

<div style="color:#339">
所有点击次数合计<br/>
此函数有重覆函数的判断, 与 WP-PostViews 的 get_totalviews() 完全相同.<br/>
</div><br/>
$echo: 是否显示, 可选, 布林, 默认值 = 1 显示, (0 用於运算, 不显示);<br/><br/>
范例: get_totalviews();<br/>
显示: <?php get_totalviews();?><br/><br/>

<div style="font-weight:700">
get_most_viewed();<br/>
</div><br/>
语法: get_most_viewed($mode, $limit, $show_date, $term_id, $after);<br/><br/>

<div style="color:#339">
点击最多文章<br/>
此函数与 WP-PostViews 的 get_most_viewed() 很接近, 新建的函数.<br/>
get_most_viewed_category 和 get_most_viewed_tag() 可以用这个 get_most_viewed() 取代, 但要改变参数.<br/>
</div><br/>
$mode: 文章或页面, 可选, 字串. 'page' 或 'post' 或 'both', 默认值 = 'post';<br/>
$limit: 数量, 可选, 正整数, 默认值 = 10;<br/>
$show_date: 是否显示日期, 可选, 布林, 默认值 = 0 不顯示;<br/>
$term_id: 分类或标签 ID, 可选, 正整数, 默认值 = 0;<br/>
$after: 后提示语, 可选, 字串, 默认值 = ' 次点击';<br/><br/>
范例:  echo '&lt;ul>', get_most_viewed(), '&lt;/ul>';<br/>
显示: <?php echo '<ul>', get_most_viewed(), '</ul>'; ?>
(可应用於侧边栏)<br/><br/>
范例:  echo '&lt;ol>', get_most_viewed('', 5, 1, array(1, 3)), '&lt;/ol>';<br/>
显示: <?php echo '<ol>', get_most_viewed('', 5, 1, array(1, 3)), '</ol>';?>
(多個 category 或 tag 的相关文章, 加日期)<br/><br/>
范例:  echo '&lt;ol>', get_most_viewed('', 5, 0, 4), '&lt;/ol>';<br/>
显示: <?php echo '<ol>', get_most_viewed('', 5, 0, 4), '</ol>';?>
(只用一個 category 或 tag 的相关文章)<br/><br/>
<div>
提示: 
<ol>
<li>执行错误请先比对参数用法.</li>
<li>这只能取代 WP-PostViews 的部份功能, 若需要高级功能, 请安装 WP-PostViews.</li>
<li>若要改写函数, 这三个函数在 my-visitors.php 最下面可找到.</li>
<li>计数器在 record.php 最下面可找到.</li>
<li>可在 '管理选项' 中设定 '不记录的访问者'.</li>
</ol>
</div>

<?php
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