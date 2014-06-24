<?php
/*
  Plugin Name: My Visitors
  Version: 1.2.6
  Plugin URI: http://kan.willin.org/?p=1335
  Description: My personal visitor statistics.
  Author: Willin Kan
  Author URI: http://kan.willin.org/
  Note: 只適用於 PHP 5.0 及 WordPress 2.8 以上
*/
defined('ABSPATH') or die('This file can not be loaded directly.');

 global $wpdb, $visitors;
 $table_prefix = (isset($table_prefix)) ? $table_prefix : $wpdb->prefix;
 $visitors = $table_prefix . 'visitors';

if (is_admin()) {

/* 後台管理頁面 */

// 访客統計
function statistics_page() {
 include('statistics.php');
}
// 访客細節
function details_page() {
 include('details.php');
}
// 熱門排行
function tophits_page() {
 include('top-hits.php');
}
// 記錄查詢
function query_page() {
 include('query.php');
}
// 文章点击
function postviews_page() {
 include('postviews.php');
}
// 管理選項
function option_page() {
 include('option.php');
}

// 加入菜單
function add_pluglin_menu() {
 $visitor_statistics = add_menu_page('My Visitors',    '访客',     'manage_options', 'visitor-statistics', '', plugins_url('images/stat.gif', __FILE__));
  add_submenu_page('visitor-statistics', 'Statistics', '访客统计', 'manage_options', 'visitor-statistics', 'statistics_page');
  add_submenu_page('visitor-statistics', 'Details',    '访客细节', 'manage_options', 'visitor-details',    'details_page');
  add_submenu_page('visitor-statistics', 'Tophits',    '热门排行', 'manage_options', 'visitor-tophits',    'tophits_page');
  add_submenu_page('visitor-statistics', 'Query',      '记录查询', 'manage_options', 'visitor-query',      'query_page');
  add_submenu_page('visitor-statistics', 'Postviews',  '文章点击', 'manage_options', 'visitor-postviews',  'postviews_page');
  $visitor_option = add_submenu_page('visitor-statistics', 'Option', '管理选项', 'administrator', 'visitor-options', 'option_page');

function statistics_css() { // 访客統計用的 css
echo '
<style type="text/css">
#radio_area{width:640px; margin-left:20px; background:#eef; border:1px solid #ccc; -moz-border-radius:12px; -khtml-border-radius:12px; -webkit-border-radius:12px; border-radius:12px; padding:6px 20px; padding:4px 20px\9}
.board{height:50px; border-bottom:1px dashed #ddf;}
.th-box{width:12px; height:12px; float:left; margin-right:5px;}
tr .c-ie{border:1px solid #49c; background:#adf;}
tr .c-fx{border:1px solid #a94; background:#ec7;}
tr .c-gc{border:1px solid #594; background:#bd6;}
tr .c-op{border:1px solid #77e; background:#bbf;}
tr .c-sf{border:1px solid #294; background:#6db;}
tr .c-mb{border:1px solid #889; background:#bbc;}
tr .c-ot{border:1px solid #b77; background:#fbe;}
tr .c-dt{border:1px solid #dde; background:#eef; text-align:center;}
tr .c-dt:hover{cursor:pointer; font-weight:700}
tr .block{padding:0; -moz-border-radius:3px; -khtml-border-radius:3px; -webkit-border-radius:3px; border-radius:3px;-moz-box-shadow: rgba(0,0,0,.4) 0 4px 7px;-webkit-box-shadow: rgba(0,0,0,.4) 0 4px 7px;-khtml-box-shadow: rgba(0,0,0,.4) 0 4px 7px;box-shadow: rgba(0,0,0,.4) 0 4px 7px;}
.absolute{position:absolute;}
</style>
';
}
add_action( 'admin_head-' . $visitor_statistics, 'statistics_css' );

function option_css() { // 管理選項用的 css
echo '
<style type="text/css">
.cb{margin-bottom:10px; background:#eef; border:1px solid #ccc; -moz-border-radius:12px; -khtml-border-radius:12px; -webkit-border-radius:12px; border-radius:12px; padding:6px 20px;}
.cc{color:blue;cursor:pointer;text-decoration:none}
.cc:hover{color:red;text-decoration:underline}
#db_head th{background:#d3dce3;padding:2px 8px}
#db tr{background:#ddd;text-align:right}
#db .tb_name{text-align:left;font-weight:700;cursor:default}
#db .alternate{background:#ccc}
#db td{padding:3px 8px}
input[type=checkbox]{margin:0 auto}
</style>
';
}
add_action( 'admin_head-' . $visitor_option, 'option_css' );

}
add_action('admin_menu', 'add_pluglin_menu');


/* 啟用插件 */
function myvisitors_activate() {
 global $wpdb, $visitors;

// 數據庫若無 'visitors' table 則建立
 if ($wpdb->get_var("show tables like '$visitors'") != $visitors) {
  $wpdb->query("CREATE TABLE ". $visitors ." (
  id       smallint(8)  NOT NULL auto_increment,
  date_gmt date         NOT NULL DEFAULT '0000-00-00',
  time_gmt time         NOT NULL DEFAULT '00:00:00',
  ip       varchar(32)  NOT NULL,
  region   varchar(100) NOT NULL,
  name     varchar(100) NOT NULL,
  requrl   varchar(300) NOT NULL,
  refurl   varchar(300) NOT NULL,
  agent    varchar(200) NOT NULL,
  browser  varchar(32)  NOT NULL,
  ver      varchar(32)  NOT NULL,
  os       varchar(32)  NOT NULL,
  UNIQUE KEY id (id)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");
 }

// 建立 'my_visitors' option
 if (!get_option('my_visitors')) {
  $options = array(1, 1, '', 1, 60, 90, 30, 90, 20, 0, '', 1, 10, 20, 30, 0, 0, '', 0, 0, 0, 1, 0, 0); // 內容請參考 option.php
  update_option('my_visitors', $options);
 }
}
register_activation_hook(__FILE__, 'myvisitors_activate');

/* 停用插件 */
function myvisitors_deactivate() {
 global $wpdb, $visitors;

 $options = get_option('my_visitors');
 $drop_table = $options[15];
 $del_option = $options[16];

// 刪除 'visitors' table 和 'my_visitors' option
 if ($drop_table) $wpdb->query("DROP TABLE IF EXISTS $visitors");
 if ($del_option) delete_option('my_visitors');
}
register_deactivation_hook(__FILE__, 'myvisitors_deactivate');

} else {

/* 前台執行 */
// 記錄访客資料 & 訪問計數
function record_visitors($arg = '') {
 include('record.php');
}
add_action('wp_head', 'record_visitors');      // $arg = ''
add_action('comment_post', 'record_visitors'); // $arg = $comment_id
add_action('wp_login', 'record_visitors');     // $arg = $user_login
add_filter('login_errors', 'record_visitors'); // $arg = $errors

}

$options = get_option('my_visitors');
$keep_days    = $options[4];
$email_enable = $options[9];
$last_backup  = $options[10];
$mail_days    = $options[11];
$next_mail    = $options[17];
$test         = $options[18];
$daily_backup = $options[21];

$today = gmdate('Y-m-d', time() + get_option('gmt_offset')*3600); // 當天日期

/* 每天只運行一次, 由訪問頁面觸發 */
if ((($last_backup && $last_backup != $today && $daily_backup) || $next_mail == $today || $test) && ($email_enable || $daily_backup)) {
 $end_date = gmdate('Y-m-d', time() + get_option('gmt_offset')*3600 - ($keep_days-1)*24*3600); // 算出分界
 $excess = "FROM $visitors WHERE date_gmt < '$end_date'";

 if ($wpdb->get_var("SELECT COUNT(*) $excess") > 0) {
  $wpdb->query("DELETE $excess"); // 達到保存最多天數, 自動刪除多餘記錄
  $wpdb->query("OPTIMIZE TABLE $visitors"); // 刪除後進行優化
 }

/* 備份路徑 */
 $key = substr(md5(DB_NAME), -10);
 $file_dir = ABSPATH . "wp-content/plugins/my-visitors/backup_$key/";
 $filename = DB_NAME . '-' . gmdate('ymd', time() + get_option('gmt_offset')*3600) . '.sql';
 if (@function_exists('gzencode')) $filename .= '.gz'; // gzip
 
 if (!is_file($file_dir.$filename)) {
   $table_name_array = $wpdb->get_col('SHOW TABLES'); // 查詢所有表名
   include('sql-dump.php'); // 導出數據庫

    /* 郵寄數據備份 */
   if ($email_enable && ($next_mail == $today || $test) && @filesize($file_dir . $filename) > 500) {
    global $phpmailer; // 採用 phpmailer 方式
    class_exists('PHPMailer') or require(ABSPATH . WPINC . '/class-phpmailer.php');
    $phpmailer = new PHPMailer();
    $phpmailer->AddAddress(get_bloginfo('admin_email'));
    $phpmailer->AddAttachment("$file_dir$filename");
    $phpmailer->Body = '这是由 My Visitors 插件所自动生成的数据库备份.';
    $phpmailer->CharSet = 'UTF-8';
    $phpmailer->ContentType = 'text/plain';
    $phpmailer->FromName = get_option('blogname');
    $phpmailer->From = 'no-reply@' . preg_replace('#^www\.#', '', strtolower($_SERVER['SERVER_NAME']));
    $phpmailer->Subject = "数据库备份- $filename";
    $phpmailer->Send(); // 寄出
    if (!$test) $options[17] = gmdate("Y-m-d", strtotime("$mail_days day") + get_option('gmt_offset')*3600); # option $next_mail
   }
 }

 $options[10] = $today; # option $last_backup
 $options[18] = 0;      # option $test
 update_option('my_visitors', $options); // 日期存入 $options
}



// 簡易 PostViews
if (!function_exists('post_views')) {
 function post_views($before = '(点击 ', $after = ' 次)', $echo = 1) {
  global $post;
  $post_ID = $post->ID;
  $views = (int)get_post_meta($post_ID, 'views', true);
  if ($echo) echo $before, number_format($views), $after;
  else return $views;
 }
}

if (!function_exists('get_most_viewed')) {
 function get_most_viewed($mode = '', $limit = 10, $show_date = 0, $term_id = 0, $after = ' 次点击') {
  global $wpdb, $post;
  $output = '';
  $mode = ($mode == '') ? 'post' : $mode;
  $type_sql = ($mode != 'both') ? "AND post_type='$mode'" : '';
  $term_sql = (is_array($term_id)) ? "AND $wpdb->term_taxonomy.term_id IN (" . join(',', $term_id) . ')' : ($term_id != 0 ? "AND $wpdb->term_taxonomy.term_id = $term_id" : '');
  $term_sql.= $term_id ? " AND $wpdb->term_taxonomy.taxonomy != 'link_category'" : '';
  $inr_join = $term_id ? "INNER JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id) INNER JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)" : '';

  // database query
  $most_viewed = $wpdb->get_results("SELECT ID, post_date, post_title, (meta_value+0) AS views FROM $wpdb->posts LEFT JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id) $inr_join WHERE post_status = 'publish' AND post_password = '' $term_sql $type_sql AND meta_key = 'views' GROUP BY ID ORDER BY views DESC LIMIT $limit");
  if ($most_viewed) {
   foreach ($most_viewed as $viewed) {
    $post_ID    = $viewed->ID;
    $post_views = number_format($viewed->views);
    $post_title = esc_attr($viewed->post_title);
    $get_permalink = esc_attr(get_permalink($post_ID));
    $output .= "<li><a href='$get_permalink' title='$post_title'>$post_title</a>";
    if ($show_date) {
      $posted = date(get_option('date_format'), strtotime($viewed->post_date));
      $output .= " - $posted";
    }
    $output .= " - $post_views $after</li>";
   }   
  } else {
   $output = "<li>N/A</li>\n";
  }
  echo $output;
 }
}

if (!function_exists('get_totalviews')) {
 function get_totalviews($echo = 1) {
  global $wpdb;
  $total_views = $wpdb->get_var("SELECT SUM(meta_value+0) FROM $wpdb->postmeta WHERE meta_key = 'views'");
  if ($echo) echo $total_views;
  else return $total_views;
 }
}

if (!function_exists('delete_postviews_meta_fields')) {
 function delete_postviews_meta_fields($post_ID) {
  delete_post_meta($post_ID, 'views');
 }
 add_action('delete_post', 'delete_postviews_meta_fields');
}

?>