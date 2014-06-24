<?php
/**
 * 導出數據 -- 用於 my-visitors.php 和 option.php
 * @param array  $table_name_array 導出的表名
 * @param string $file_dir         備份路徑
 * @param string $filename         備份文件名
 * @return       $success          儲存數量
 */
defined('ABSPATH') or die('This file can not be loaded directly.');

/* make sure the path exists */
 if (!is_dir($file_dir)) {
   if(!mkdir($file_dir, 0777)) {
     echo '<div id="message" class="updated fade"><p><strong>错误: 无法创建数据备份路径.</strong></p></div>';
     exit();
   }
 }

$fp = fopen($file_dir.$filename, 'w');

/* write to file */
function sql_dump($fp, $buffer) {
 global $success;
 if (@function_exists('gzencode')) $buffer = gzencode($buffer); // gzip
 $success = fwrite($fp, $buffer);
}

$date_gmt  = gmdate('Y-m-d H:i:s', time() + get_option('gmt_offset')*3600);
$gm_date   = gmdate('Y-m-d H:i:s', time());
$mysql_ver = mysql_get_server_info();
$php_ver   = PHP_VERSION;

$max_limit = 49152; // 48KB

$buffer = "-- My Visitors SQL Dump version 1.2.6
-- Author: willin kan
-- URI: http://kan.willin.org/\n--
-- 主机: " . DB_HOST . "
-- 生成日期: $date_gmt ($gm_date GMT)
-- 服务器版本: $mysql_ver
-- PHP 版本: $php_ver\n
SET SQL_MODE=\"NO_AUTO_VALUE_ON_ZERO\";\n
--\n-- 数据库: `" . DB_NAME . "`
--\n\n-- " . str_repeat('-', 56);
sql_dump($fp, $buffer);

foreach ($table_name_array as $table) {
$buffer = "\n
# 表的结构: `$table`\n
DROP TABLE IF EXISTS `$table`;\n";
$row = $wpdb->get_row("SHOW CREATE TABLE $table");
$buffer.= $row->{'Create Table'}.";\n
# 导出表中的数据 `$table`\n";
sql_dump($fp, $buffer);

$limit_start = 0;
$status = $wpdb->get_row("SHOW TABLE STATUS LIKE '$table'");
$limit = (int)(($max_limit / (($status->Data_length + 1) / ($status->Rows + 1)))); // 以 max_limit 切割長度 (ROWS_PER_SEGMENT)

 do {
   $buffer = '';
   $dbs = $wpdb->get_results("SELECT * FROM $table LIMIT $limit_start, $limit", ARRAY_A);
   if (count($dbs)) {
     $buffer .= "\nINSERT INTO `$table` VALUES";
     foreach ($dbs as $db) {
       foreach ($db as $str) {
         $str = strtr($str, array("'" => "''", "\\" => "\\\\", "\x00" => "\\0", "\x0a" => "\\n", "\x0d" => "\\r", "\x1a" => "\\Z")); // 修改版 mysql_real_escape_string()
         $fild[] = is_numeric($str) ? $str : "'" . $str . "'";
       }
       $fild = implode(", ", $fild);
       $buffer .= "\n($fild),";
       unset($fild);
     }
   $buffer = rtrim($buffer, ',') . ";";
  }
  sql_dump($fp, $buffer);
  $limit_start += $limit;
 } while (count($dbs));

 $buffer = "\n
# `$table` 结束
\n-- " . str_repeat('-', 56);
sql_dump($fp, $buffer);
}

fclose($fp);

/* delete oldest backup file */
 if ($handle = opendir("$file_dir")) {
   $pattern = "(\.sql*)";
   while (false !== ($file = readdir($handle))) { // 讀取每個文件名
     if (ereg($pattern, $file)) {
       $file_array[] = $file;
     }
   }
 $options = get_option('my_visitors');
 $keep_bkup = $options[13];

   if (count($file_array) > $keep_bkup) { // 若文件超過指定份數
    rsort($file_array);
    unlink($file_dir . array_pop($file_array)); // 刪除最舊的
   }
   closedir($handle);
 }

 $dump_peak_usage = round(memory_get_peak_usage()/1024/1024, 2);
 ?>