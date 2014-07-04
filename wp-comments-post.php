<?php

require( dirname(__FILE__) . '/wp-load.php' );

$mark_as_spam = 1;
record_visitors();

wp_redirect( get_option('home') );

exit;

?>