<?php
/**
 * 管理選項
 */
defined('ABSPATH') or die('This file can not be loaded directly.');
$system_usage = round(memory_get_usage()/1024/1024, 2);

if (!current_user_can('administrator'))
  wp_die(__('You do not have sufficient permissions to manage options for this blog.')); ?>

<div class="wrap">
 <div class="icon32" style="background:url(<?php echo plugins_url('my-visitors/images/manage.png'); ?>) no-repeat;"></div>
 <h2>管理選項</h2>

<br />

<?php
 global $wpdb, $visitors, $EZSQL_ERROR;
 $wpdb->hide_errors(); // for custom error msg

/* 數據備份路徑 */
 $key = substr(md5(DB_NAME), -10);
 $backup_dir = "wp-content/plugins/my-visitors/backup_$key/";
 $file_dir = ABSPATH . $backup_dir;
 $filename = DB_NAME . '-' . gmdate('ymd', time() + get_option('gmt_offset')*3600) . '.sql';
 if (function_exists('gzencode')) $filename .= '.gz';
 $file_url = plugins_url("/my-visitors/backup_$key/$filename");
 $rnd = rand(1000, 9999); // for download without browser cache


/* make sure the path exists */
 if (!is_dir($file_dir)) {
   if(!mkdir($file_dir, 0777)) {
     echo '<div id="message" class="updated fade"><p><strong>錯誤: 無法創建數據備份路徑. 請確認服務器有 fopen() 功能.</strong></p></div>';
   }
 }


if (isset($_POST['clear_ua'])) { // when clear_ua submit
  $ua_n = $wpdb->get_var("SELECT COUNT(*) FROM $visitors WHERE agent != ''"); // 數目
  $wpdb->query("update $visitors set agent = ''"); // 清空
  $status = $wpdb->get_row("SHOW TABLE STATUS LIKE '$visitors'");
  $ua_length = $status->Data_length + $status->Index_length; // 大小合計
  $ua_free   = $status->Data_free;
  $wpdb->query("OPTIMIZE TABLE $visitors"); // 優化
}

global $current_user;
  $hash = md5($current_user->user_login); // 安全密鑰, 別人無法得知此數值

if (isset($_POST[$hash])) { // when $hash submit
  $j = $_POST[$hash]; $table_name = '';
  for ($i = 0; $i < $j; $i++){
    $table_name .= isset($_POST["tb$i"]) ? $_POST["tb$i"] . ", " : '';
  }

  if (!$table_name) {
    $msg = '請至少勾選一個數據表, 再進行此動作.';

  } else {
   $table_name = rtrim($table_name, ', ');
   $db_query = $_POST['db_query'];

   if ($db_query == 'EXPORT') {
     $table_name_array = explode(', ', $table_name);

     $timer = timer_stop();
     include('sql-dump.php');
     $access_time = timer_stop() - $timer;
     $dump_usage = isset($dump_peak_usage) ? ", 峰值佔用內存: ".($dump_peak_usage - $system_usage)."M" : '';

     global $success;
     $msg = ($success > 0) ? "已導出數據: <span style='color:green'>$table_name</span><br/>
                              <br/>-- 路徑: '<span style='color:blue'>$backup_dir</span>'<br/>
                              <br/>-- 文件名: '<a href='$file_url?$rnd' title='下載剛才備份的文件'>$filename</a>'　耗時: $access_time 秒$dump_usage"
                           : "導出失敗.";
   } else {
     $success = $wpdb->query("$db_query TABLE $table_name"); // OPTIMIZE
     $msg = ($success > 0) ? "MySql: <span style='color:purple'>$db_query TABLE</span> <span style='color:green'>$table_name</span><br/><br/>OK." : '處理失敗.';
   }
  }

  $error_msg = $EZSQL_ERROR[0]['error_str'];
  $error_msg = !empty($error_msg) ? "WordPress database error: [<span style='color:red'>$error_msg</span>]<br/><br/>" : '';

  echo '<div id="message" class="updated fade"><p><strong>', $error_msg, $msg, '</strong></p></div>';
}

?>

<div class="cb" style="width:80px">
數據庫管理:
</div>

<div style="color:blue;margin:8px 0">
<img src="<?php echo plugins_url('my-visitors/images/s_host.png'); ?>" alt="" style="margin:0 8px;vertical-align:middle" />服務器: <?php echo DB_HOST; ?>　
<span style="color:#000">&#9658;</span>
<?php

/* delete oldest backup file */
$file_array = '';
$files = scandir($file_dir);
foreach($files as $file){  // 讀取每個文件名
  if (strpos($file, '.sql')) $file_array[] = $file;
}
$options = get_option('my_visitors');
$keep_bkup = $options[13];
if (count($file_array) > $keep_bkup) { // 若文件超過指定份數
  unlink($file_dir . current($file_array)); // 刪除最舊的
}


/* looking for latest file */
 if (is_array($file_array)) {
   $filename = array_pop($file_array);
   $file_url = plugins_url("/my-visitors/backup_$key/$filename");
 }

 $download_link = is_file($file_dir.$filename) ? "<a href='$file_url?$rnd' class='cc' title='下載最新的備份文件'>數據庫: " . DB_NAME . "</a>"
                                            : "<span class='cc' title='尚無備份文件'>數據庫: " . DB_NAME . "</span>";
?>
<img src="<?php echo plugins_url('my-visitors/images/s_db.png'); ?>" alt="" style="margin:0 8px;vertical-align:middle" /><?php echo $download_link ?>
</div>

<form method="post" action="">
<table id='db_head'>
 <thead>
  <tr>
   <td style='width:30px'></td>
   <th style='width:200px'>數據表</th>
   <th style='width:90px'>記錄數</th>
   <th style='width:110px'>數據大小</th>
   <th style='width:110px'>索引鍵大小</th>
   <th style='width:120px'>大小合計</th>
   <th style='width:90px'>多餘</th>
  </tr>
 </thead>
 <tbody id='db'>

<?php

$i = $total_rows = $total_data_length = $total_index_length = $total_sum_length = $total_free = 0;

function fsize($size) {
  $units = array(' B', ' KB', ' MB', ' GB', ' TB');
  for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
  return number_format($size, 1) . $units[$i];
}

$results = $wpdb->get_results('SHOW TABLE STATUS');  // 查詢表頭

foreach($results as $db){
    $tb_name = $db->Name;
    $tb_rows = $db->Rows;
    $tb_data_length = $db->Data_length;
    $tb_index_length = $db->Index_length;
    $tb_free = $db->Data_free;

    $rows = number_format($tb_rows);
    $data_length = fsize($tb_data_length);
    $index_length = fsize($tb_index_length);
    $sum_length = fsize($tb_data_length + $tb_index_length);
    $free_length = fsize($tb_free);

    $free = ($tb_free == 0) ? '-' : "<span style='color:blue'>$free_length</span>";
    $css = $i % 2 ? 'alternate ' : ''; if ($free != '-') $css .= 'free';

    echo "<tr class='$css'><td><input type='checkbox' name='tb$i' value='$tb_name'/></td><td class='tb_name'>$tb_name</td><td>$rows</td><td>$data_length</td><td>$index_length</td><td>$sum_length</td><td>$free</td></tr>";

    $total_rows += $tb_rows;
    $total_data_length += $tb_data_length;
    $total_index_length += $tb_index_length;
    $total_sum_length += $tb_data_length + $tb_index_length;
    $total_free += $tb_free;
    $i++;
}

$total_rows = number_format($total_rows);
$total_sum_length = fsize($total_data_length + $total_index_length);
$total_data_length = fsize($total_data_length);
$total_index_length = fsize($total_index_length);
$total_free = ($total_free == 0) ? '-' : fsize($total_free);
echo"</tbody><tbody><tr><td></td><th>$i 個表</th><th style='text-align:right'>$total_rows</th><th style='text-align:right'>$total_data_length</th><th style='text-align:right'>$total_index_length</th><th style='text-align:right'>$total_sum_length</th><th style='text-align:right'>$total_free</th></tr></tbody></table><input name='$hash' value='$i' type='hidden' />";

?>

<img src="<?php echo plugins_url('my-visitors/images/arrow_ltr.png'); ?>" alt="" style="margin-left:8px" />
<span class="cc" onclick="jQuery('input:checkbox').attr('checked',true);jQuery('#db tr').css('background','#fc9')">全選</span> /
<span class="cc" onclick="jQuery('input:checkbox').attr('checked',false);jQuery('#db tr').css('background','')">全不選</span> /
<span class="cc" onclick="jQuery('input:checkbox').attr('checked',false);jQuery('#db tr').css('background','');jQuery('.free input:checkbox').attr('checked',true);jQuery('#db .free').css('background','#fc9')">只選取多餘項</span>

<select name="db_query" onchange="this.form.submit();" style="margin: 0 33px;font-size:12px">
    <option value="選中項:" selected="selected">選中項:</option>
    <option value="OPTIMIZE">優化表</option>
    <option value="REPAIR">修復表</option>
    <option value="EXPORT">導出數據</option>
</select>

</form>
<?php

// 刪除修訂版本
if (isset($_POST['del_revisions'])) {
  $ok = $wpdb->query("DELETE FROM $wpdb->posts WHERE post_type = 'revision'");
  $wpdb->query("OPTIMIZE TABLE $wpdb->posts"); // 刪除後進行優化
  if ($ok) echo "自動保存的草稿已刪除.<br/><br/>";
}
$revisions_num = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = 'revision'");
if ($revisions_num) echo "<form method='post' action=''><span style='color:red'>在您的存檔中, 發現有 $revisions_num 篇自動保存的修訂版本草稿, 是否要刪除?</span>　<input type='submit' class='button' name='del_revisions' value='刪除' /></form><br/>";

?>

<div>
提示: 
<ol>
<li>數據庫在預設日期 0:00 之後由第一位訪客觸發, 會自動備份一次, 如果達到預設天數將會郵寄給管理者, 若用手動導出, 會更新當天備份. (可直接點擊下載, 不郵寄.)</li>
<li>在備份路徑中, 若備份文件超過指定份數, 會自動刪除最舊的.</li>
<li>停用插件時, 只要不刪除插件, 備份文件還會保留在備份路徑中, 可用 ftp 工具下載.</li>
<li>本插件只包含較常用的優化表、修復表和導出數據功能, 其它功能請在 phpMyAdmin 中進行.</li>
<li>如果發現有自動保存的修訂版本草稿, 本插件會自動提示您刪除.</li>
</ol>
</div>

<hr style="margin:30px 0"/>

<div class="cb" style="width:80px">
一般選項:
</div>

<?php

/* option */
 $exclude_blogger  = $options[0];  # 博主不記錄 (未登入情況)
 $exclude_login    = $options[1];  # 配置管理員不記錄 (已登入情況)
 $exclude_custom   = $options[2];  # 自定義人員不記錄
 $save_agent       = $options[3];  # 是否將 user agent 存入數據庫
 $keep_days        = $options[4];  # 記錄保存最多天數
 $keep_days_custom = $options[5];  # 自定義記錄保存最多天數
 $show_days        = $options[6];  # 查看統計圖表最多天數
 $show_days_custom = $options[7];  # 自定義查看統計圖表最多天數
 $max_tophit       = $options[8];  # 熱門排行顯示數量
 $email_enable     = $options[9];  # 是否郵寄數據備份
 $last_backup      = $options[10]; # 上次備份日期
 $mail_days        = $options[11]; # 郵寄備份天數
 $mail_days_custom = $options[12]; # 自定義郵寄備份天數
 $keep_bkup        = $options[13]; # 存放備份數
 $keep_bkup_custom = $options[14]; # 自定義存放備份數
 $drop_table       = $options[15]; # 停用是否刪除數據
 $del_option       = $options[16]; # 停用是否刪除選項
 $next_mail        = $options[17]; # 下次郵寄日期
 $last_check       = $options[18]; # 上次檢查日期及測試用
 $exclude_n_404_bt = $options[19]; # 非 [404] 的爬蟲不記錄
 $exclude_n_dg_404 = $options[20]; # 非危險份子的 [404] 不記錄
 $daily_backup     = $options[21]; # 是否天天備份
 $postviews_ajax   = $options[22]; # 是否啟用 Ajax 計數
 $postviews_jq     = $options[23]; # 是否由本插件載入 jQ

if (isset($_POST['option']) && $_POST['option'] == $hash) {  // when option submit

 if (isset($_POST['clear_ua'])) {
   $ua_n = number_format($ua_n);
   $ua_percent = round(($ua_free/$ua_length)*100, 2);
   $ua_free = fsize($ua_free);
   $msg = "<span style='color:green'>共有 $ua_n 筆 user agent 已清空, 節省了 $ua_free 空間, 節省比例 $ua_percent%.</span>";

 } else {

 $exclude_blogger  = isset($_POST['exclude_blogger']) ? (int)$_POST['exclude_blogger'] : 0;
 $exclude_login    = isset($_POST['exclude_login'])   ? (int)$_POST['exclude_login']   : 0;
 $exclude_custom   = isset($_POST['exclude_custom'])  ? $_POST['exclude_custom']       : $exclude_custom;
 $save_agent       = isset($_POST['save_agent'])      ? (int)$_POST['save_agent']      : $save_agent;
 $keep_days_custom = (int)$_POST['keep_days_custom'] && $_POST['keep_days_custom'] > 0 ? (int)$_POST['keep_days_custom'] : $keep_days_custom;
 $keep_days        = $_POST['keep_days'] == 'custom'  ? $keep_days_custom              : (int)$_POST['keep_days'];
 $show_days_custom = (int)$_POST['show_days_custom'] && $_POST['show_days_custom'] > 0 ? (int)$_POST['show_days_custom'] : $show_days_custom;
 $show_days        = $_POST['show_days'] == 'custom'  ? $show_days_custom              : (int)$_POST['show_days'];
 $max_tophit       = isset($_POST['max_tophit'])      ?(int)$_POST['max_tophit']       : $max_tophit;
 $email_enable     = isset($_POST['email_enable'])    ?(int)$_POST['email_enable']     : $email_enable;
 if ($email_enable) {
   $mail_days_custom = (int)$_POST['mail_days_custom'] && $_POST['mail_days_custom'] > 0 ? (int)$_POST['mail_days_custom'] : (($mail_days_custom != '') ? $mail_days_custom : 10);
   $mail_days        = $_POST['mail_days'] == 'custom'  ? $mail_days_custom              : (int)$_POST['mail_days'];
   $next_mail        = gmdate("Y-m-d", strtotime("$mail_days day") + get_option('gmt_offset')*3600);
 }
 $keep_bkup_custom = (int)$_POST['keep_bkup_custom'] && $_POST['keep_bkup_custom'] > 0 ? (int)$_POST['keep_bkup_custom'] : (($keep_bkup_custom != '') ? $keep_bkup_custom : 30);
 $keep_bkup        = $_POST['keep_bkup'] == 'custom'  ? $keep_bkup_custom              : (int)$_POST['keep_bkup'];
 $drop_table       = isset($_POST['drop_table'])      ?(int)$_POST['drop_table']       : $drop_table;
 $del_option       = isset($_POST['del_option'])      ?(int)$_POST['del_option']       : $del_option;
 $last_check       = 'option_updated'; // 用於每日運行一次和功能測試: ftp 刪除當日備份後, 按下保存更改, 必須進行備份且寄出郵件
 $exclude_n_404_bt = isset($_POST['exclude_n_404_bt']) ? (int)$_POST['exclude_n_404_bt'] : 0;
 $exclude_n_dg_404 = isset($_POST['exclude_n_dg_404']) ? (int)$_POST['exclude_n_dg_404'] : 0;
 $daily_backup     = isset($_POST['daily_backup'])     ? (int)$_POST['daily_backup']     : 0;
 $postviews_ajax   = isset($_POST['postviews_ajax'])   ? (int)$_POST['postviews_ajax']   : 0;
 $postviews_jq     = isset($_POST['postviews_jq'])     ? (int)$_POST['postviews_jq']     : 0;

 $options = array(
    $exclude_blogger,
    $exclude_login,
    $exclude_custom,
    $save_agent,
    $keep_days,
    $keep_days_custom,
    $show_days,
    $show_days_custom,
    $max_tophit,
    $email_enable,
    $last_backup,
    $mail_days,
    $mail_days_custom,
    $keep_bkup,
    $keep_bkup_custom,
    $drop_table,
    $del_option,
    $next_mail,
    $last_check,
    $exclude_n_404_bt,
    $exclude_n_dg_404,
    $daily_backup,
    $postviews_ajax,
    $postviews_jq
  );

 update_option('my_visitors', $options);
  $msg = '<span style="color:green">設置已保存.</span>';
 }

echo '<div id="message" class="updated fade"><p><strong>', $msg, '</strong></p></div>';

}
?>

<form method="post" action="">
<input name="option" value="<?php echo $hash ?>" type="hidden" />

<table class="form-table">

<tr>
<th scope="row">查看統計圖表最多天數</th>
<td>
  <label title="圖表最多顯示 15 天"><input type="radio" name="show_days" value="15" <?php if ($show_days==15) echo 'checked="checked"' ?>/> 15 天</label><br />
  <label title="圖表最多顯示 30 天"><input type="radio" name="show_days" value="30" <?php if ($show_days==30) echo 'checked="checked"' ?>/> 30 天</label><br />
  <label title="圖表最多顯示 60 天"><input type="radio" name="show_days" value="60" <?php if ($show_days==60) echo 'checked="checked"' ?>/> 60 天</label><br />
  <label title="建議不要顯示太多天數, 以免圖表過度擁擠"><input type="radio" name="show_days" value="custom" <?php if ($show_days != 15 && $show_days != 30 && $show_days != 60) echo 'checked="checked"' ?>/> 自定義： <input type="text" name="show_days_custom" value="<?php echo $show_days_custom ?>" class="small-text" /> 天</label>
</td>
</tr>

<tr>
<th scope="row">不記錄的訪問者</th>
<td>
  <label title="非 [404] 的爬蟲最多, 不記錄的話可大量節省空間, 記錄的話統計數據較準確."><input type="checkbox" name="exclude_n_404_bt" value="1" <?php if ($exclude_n_404_bt) echo 'checked="checked"' ?>/> 非 [404] 的爬蟲</label><br />
  <label title="[404] 太多的話, 可暫時不記錄, 要先解決問題, 否則浪費空間."><input type="checkbox" name="exclude_n_dg_404" value="1" <?php if ($exclude_n_dg_404) echo 'checked="checked"' ?>/> 非危險份子的 [404]</label><br />
  <label title="以 $_COOKIE['wp-settings-2'] 識別博主身份"><input type="checkbox" name="exclude_blogger" value="1" <?php if ($exclude_blogger) echo 'checked="checked"' ?>/> 博主 ( 未登入情況, 以 cookie 判斷 )</label><br />
  <label title="以 current_user_can('manage_options') 識別配置管理員身份"><input type="checkbox" name="exclude_login" value="1" <?php if ($exclude_login) echo 'checked="checked"' ?>/> 配置管理員權限以上人員 ( 已登入情況 )</label><br />
  <label title="以 $comment_author 識別不記錄人員身份">自定義不記錄人員： ( 輸入昵稱, 不同人員以逗號或空格分隔 )<br/><input type="text" name="exclude_custom" value="<?php echo $exclude_custom ?>" class="regular-text" /></label><br />
</td>
</tr>

<tr>
<th scope="row">是否將 user agent 存入數據庫</th>
<td>
  <label title="不存入數據庫, 可節省空間"><input type="radio" name="save_agent" value="0" <?php if (!$save_agent) echo 'checked="checked"' ?>/> 不要</label><br />
  <label title="存入數據庫, 可供分析比對"><input type="radio" name="save_agent" value="1" <?php if ($save_agent) echo 'checked="checked"' ?>/> 要</label><br />
<?php $ua_n = $wpdb->get_var("SELECT COUNT(*) FROM $visitors WHERE agent != ''");
      $ua_n = number_format($ua_n);
  if ($ua_n > 0) echo "<label title='清空 user agent 不會影響統計數字'>已有 $ua_n 筆 user agent　<input type='submit' class='button' name='clear_ua' value='立即清空'/></label><br />";
?>
</td>
</tr>

<tr>
<th scope="row">熱門排行顯示數量</th>
<td><select name="max_tophit" id="max_tophit">
  <option value="20"  <?php if ($max_tophit==20)  echo 'selected="selected"' ?>>Top 20</option>
  <option value="50"  <?php if ($max_tophit==50)  echo 'selected="selected"' ?>>Top 50</option>
  <option value="100" <?php if ($max_tophit==100) echo 'selected="selected"' ?>>Top 100</option>
  <option value="200" <?php if ($max_tophit==200) echo 'selected="selected"' ?>>Top 200</option>
</select></td>
</tr>

<tr>
<th scope="row">記錄保存最多天數</th>
<td>
  <label title="自動刪除 15 天前的過期數據"><input type="radio" name="keep_days" value="15" <?php if ($keep_days==15) echo 'checked="checked"' ?>/> 15 天</label><br />
  <label title="自動刪除 30 天前的過期數據"><input type="radio" name="keep_days" value="30" <?php if ($keep_days==30) echo 'checked="checked"' ?>/> 30 天</label><br />
  <label title="自動刪除 60 天前的過期數據"><input type="radio" name="keep_days" value="60" <?php if ($keep_days==60) echo 'checked="checked"' ?>/> 60 天</label><br />
  <label title="建議不要保存過多數據, 以免影響讀取速度和內存的使用"><input type="radio" name="keep_days" value="custom" <?php if ($keep_days != 15 && $keep_days != 30 && $keep_days != 60) echo 'checked="checked"' ?>/> 自定義： <input type="text" name="keep_days_custom" value="<?php echo $keep_days_custom ?>" class="small-text" /> 天</label>
</td>
</tr>

<tr>
<th scope="row">是否將數據備份郵寄給管理者</th>
<td>
  <label title="不郵寄, 可節省郵箱空間"><input type="radio" name="email_enable" value="0" <?php if (!$email_enable) echo 'checked="checked"' ?> onclick="jQuery('.mail_days input').attr('disabled',true)" /> 不郵寄</label><br />
  <label title="郵寄出去, 可兩地備份"><input type="radio" name="email_enable" value="1" <?php if ($email_enable) echo 'checked="checked"' ?> onclick="jQuery('.mail_days input').attr('disabled',false)" /> 寄到 <?php echo get_bloginfo ('admin_email') ?></label><br />
</td>
</tr>

<tr>
<th scope="row">郵寄數據備份<?php if ($email_enable) echo '<br/>(下次郵寄: ', $next_mail, ')'; ?></th>
<td class="mail_days">
  <label title="每天郵寄"><input type="radio" name="mail_days" value="1" <?php if (!$email_enable) echo 'disabled="disabled"'; if ($mail_days==1) echo 'checked="checked"' ?>/> 每天一次</label><br />
  <label title="每二天郵寄一次"><input type="radio" name="mail_days" value="2" <?php if (!$email_enable) echo 'disabled="disabled"'; if ($mail_days==2) echo 'checked="checked"' ?>/> 二天一次</label><br />
  <label title="每一週郵寄一次"><input type="radio" name="mail_days" value="7" <?php if (!$email_enable) echo 'disabled="disabled"'; if ($mail_days==7) echo 'checked="checked"' ?>/> 一週一次</label><br />
  <label title="自定義郵寄天數"><input type="radio" name="mail_days" value="custom" <?php if (!$email_enable) echo 'disabled="disabled"'; if ($mail_days != 1 && $mail_days != 2 && $mail_days != 7) echo 'checked="checked"' ?>/> 自定義： <input type="text" name="mail_days_custom" <?php if (!$email_enable) echo 'disabled="disabled"'; ?> value="<?php echo $mail_days_custom ?>" class="small-text" /> 天一次</label>
</td>
</tr>

<tr>
<th scope="row">服務器是否天天備份</th>
<td>
  <label title="不存入數據庫, 可節省空間"><input type="radio" name="daily_backup" value="0" <?php if (!$daily_backup) echo 'checked="checked"' ?>/> 郵寄才備份</label><br />
  <label title="存入數據庫, 可供分析比對"><input type="radio" name="daily_backup" value="1" <?php if ($daily_backup) echo 'checked="checked"' ?>/> 天天備份</label><br />
</td>
</tr>

<tr>
<th scope="row">服務器存放備份數</th>
<td>
  <label title="超過 5 份, 自動刪除最舊的備份"><input type="radio" name="keep_bkup" value="5" <?php if ($keep_bkup==5) echo 'checked="checked"' ?>/> 5 份</label><br />
  <label title="超過 10 份, 自動刪除最舊的備份"><input type="radio" name="keep_bkup" value="10" <?php if ($keep_bkup==10) echo 'checked="checked"' ?>/> 10 份</label><br />
  <label title="超過 20 份, 自動刪除最舊的備份"><input type="radio" name="keep_bkup" value="20" <?php if ($keep_bkup==20) echo 'checked="checked"' ?>/> 20 份</label><br />
  <label title="請視服務器空間大小而定"><input type="radio" name="keep_bkup" value="custom" <?php if ($keep_bkup != 5 && $keep_bkup != 10 && $keep_bkup != 20) echo 'checked="checked"' ?>/> 自定義： <input type="text" name="keep_bkup_custom" value="<?php echo $keep_bkup_custom ?>" class="small-text" /> 份</label>
</td>
</tr>

<tr>
<th scope="row">postviews 是否啟用 Ajax 計數</th>
<td>
  <label title="一般情況不用 Ajax 也能正常計數"><input type="radio" name="postviews_ajax" value="0" <?php if (!$postviews_ajax) echo 'checked="checked"' ?> onclick="jQuery('.postviews_jq input').attr('disabled',true)" /> 不要</label><br />
  <label title="使用頁面緩存不能正常計數時, 才用 Ajax 計數"><input type="radio" name="postviews_ajax" value="1" <?php if ($postviews_ajax) echo 'checked="checked"' ?> onclick="jQuery('.postviews_jq input').attr('disabled',false)" /> 要　　
  <label title="如果主題已有 jQuery, 就不需重覆載入" class="postviews_jq"><input type="checkbox" class="postviews_jq" name="postviews_jq" value="1" <?php if (!$postviews_ajax) echo 'disabled="disabled"'; if ($postviews_jq) echo 'checked="checked"' ?>/> 由本插件載入 jQuery 庫</label></label>
</td>
</tr>

<tr>
<th scope="row">停用插件是否刪除所有統計數據</th>
<td>
  <label title="不刪除, 可供以後繼續使用"><input type="radio" name="drop_table" value="0" <?php if (!$drop_table) echo 'checked="checked"' ?>/> 不刪除</label><br />
  <label title="刪除, 不留下任何垃圾"><input type="radio" name="drop_table" value="1" <?php if ($drop_table) echo 'checked="checked"' ?>/> 刪除</label><br />
</td>
</tr>

<tr>
<th scope="row">停用插件是否刪除本插件選項</th>
<td>
  <label title="不刪除, 可供以後繼續使用"><input type="radio" name="del_option" value="0" <?php if (!$del_option) echo 'checked="checked"' ?>/> 不刪除</label><br />
  <label title="刪除, 不留下任何垃圾"><input type="radio" name="del_option" value="1" <?php if ($del_option) echo 'checked="checked"' ?>/> 刪除</label><br />
</td>
</tr>

</table>

<p class="submit">
  <input type="submit" class="button-primary" value="保存更改" />
</p>
</form>

<div>
提示: 
<ol>
<li>按下 '保存更改' 之後, 超過 '保存最多天數' 的記錄和超過 '服務器存放備份數' 的最舊文件會被刪除!</li>
<li>停用插件時, 可選擇刪除 '所有統計數據' 和 '本插件選項', 數據庫不會留下任何垃圾.</li>
<li>統計不只是用來看看數字和圖表而已, 統計是讓您: '發現問題'、'自我檢討'、'安全防範'、'未雨綢繆'.</li>
<li>最近一週的數據是最有時效性的, 過時的數據很少有人會看, 建議不要存放太多垃圾在數據庫內.</li>
<li>統計只是輔助工具, 沒必要花太多時間看統計內容, 要把時間多用在寫博文上.</li>
</ol>
</div>

<script type="text/javascript">
//<![CDATA[
jQuery(document).ready(function($){
 $('input:radio,input:submit').bind('focus',function(){if(this.blur)this.blur()});
 $('#db tr')
  .mouseover(function(){if($(this).children().children().attr('checked')=='checked')return;$(this).css('background','#cfc')})
  .mouseout(function(){if($(this).children().children().attr('checked')=='checked')return;$(this).css('background','')})
  .click(function(){ts=$(this).children().children();if(ts.attr('checked')=='checked'){ts.attr('checked',false);$(this).css('background','#cfc')}else{ts.attr('checked',true);$(this).css('background','#fc9')}});
 $('#db input:checkbox')
  .click(function(){if($(this).attr('checked')=='checked'){$(this).attr('checked',false)}else{$(this).attr('checked',true)}});

});
//]]>

</script>

<?php
 $memory_limit = (int)ini_get('memory_limit');
 $memory_get_peak_usage = round(memory_get_peak_usage()/1024/1024, 2);
 $memory_peak_usage_percent = round($memory_get_peak_usage/$memory_limit*100);
 $plug_usage = $memory_get_peak_usage - $system_usage;
 $now_usage = round(memory_get_usage()/1024/1024, 2) - $system_usage;
 echo "<br/><span class='subsubsub'>loading ", get_num_queries(), " queries, ", timer_stop(), " seconds.
 <br/>可用內存: $memory_limit MB, 峰值佔用: $memory_get_peak_usage MB, 佔用比例: $memory_peak_usage_percent%
 <br/>系統佔用: $system_usage MB, 本插件峰值佔用: $plug_usage MB, 本插件目前佔用: $now_usage MB</span>";
?>

</div>
