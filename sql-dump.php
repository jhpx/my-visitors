<?php
/**
 * 導出數據 -- 用於 my-visitors.php 和 option.php
 * @param array  $table_name_array      導出的表名
 * @param string $file_dir              備份路徑
 * @param string $filename              備份文件名
 * @return       $success, $EZSQL_ERROR 儲存數量和錯誤訊息
 */
defined('ABSPATH') or die('This file can not be loaded directly.');

if (function_exists('fopen')) {

$fp = fopen($file_dir.$filename, 'w');

/* write to file */
function sql_dump($fp, $buffer) {
 global $success;
 if (function_exists('gzencode')) $buffer = gzencode($buffer); // gzip
 $success = fwrite($fp, $buffer);
}

$date_gmt  = gmdate('Y-m-d H:i:s', time() + get_option('gmt_offset')*3600);
$gm_date   = gmdate('Y-m-d H:i:s', time());
$mysql_ver = mysql_get_server_info();
$php_ver   = PHP_VERSION;

$max_limit = 49152; // 48KB

$buffer = "-- My Visitors SQL Dump version 1.4
-- Author: willin kan
-- URI: http://kan.willin.org/\n--
-- 主機: " . DB_HOST . "
-- 生成日期: $date_gmt ($gm_date GMT)
-- 服務器版本: $mysql_ver
-- PHP 版本: $php_ver\n
SET SQL_MODE=\"NO_AUTO_VALUE_ON_ZERO\";\n
--\n-- 數據庫: `" . DB_NAME . "`
--\n\n-- " . str_repeat('-', 56);
sql_dump($fp, $buffer);

foreach ($table_name_array as $table) {
$buffer = "\n
# 表的結構: `$table`\n
DROP TABLE IF EXISTS `$table`;\n";
$table_row = $wpdb->get_row("SHOW CREATE TABLE $table");
$buffer.= $table_row->{'Create Table'}.";\n
# 導出表中的數據 `$table`\n";
sql_dump($fp, $buffer);

$limit_start = 0;
$status = $wpdb->get_row("SHOW TABLE STATUS LIKE '$table'");
$limit = (int)(($max_limit / (($status->Data_length + 1) / ($status->Rows + 1)))); // 以 max_limit 切割長度 (ROWS_PER_SEGMENT)

 do {
   $buffer = '';
   $data = $wpdb->get_results("SELECT * FROM $table LIMIT $limit_start, $limit", ARRAY_A);
   if (count($data)) {
     $buffer .= "\nINSERT INTO `$table` VALUES";
     foreach ($data as $row) {
       foreach ($row as $val) {
         $fild[] = is_numeric($val) ? $val : ( $val === NULL ? 'NULL' // 注意 NULL 和 '' 不一樣, 否則導回來的資料是錯的.
                 : "'" . strtr($val, array("'" => "''", "\\" => "\\\\", "\x00" => "\\0", "\x0a" => "\\n", "\x0d" => "\\r", "\x1a" => "\\Z")) . "'" ); // 修改版 mysql_real_escape_string()
       }
       $fild = implode(", ", $fild);
       $buffer .= "\n($fild),";
       unset($fild);
     }
   $buffer = rtrim($buffer, ',') . ";";
  }
  sql_dump($fp, $buffer);
  $limit_start += $limit;
 } while (count($data));

 $buffer = "\n
# `$table` 結束
\n-- " . str_repeat('-', 56);
sql_dump($fp, $buffer);
}

fclose($fp);

 $dump_peak_usage = round(memory_get_peak_usage()/1024/1024, 2);

} else {

  $EZSQL_ERROR[0]['error_str'] = '無法將數據寫入目的文件.';

}
?>