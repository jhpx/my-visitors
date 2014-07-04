<?php
/**
 * 記錄訪客資料
 */
defined('ABSPATH') or die('This file can not be loaded directly.');

 $options = get_option('my_visitors');
 $exclude_blogger  = $options[0];  # 博主不記錄 (未登入情況)
 $exclude_login    = $options[1];  # 配置管理員不記錄 (已登入情況)
 $exclude_custom   = $options[2];  # 自定義人員不記錄
 $exclude_n_404_bt = $options[19]; # 非 [404] 的爬蟲不記錄
 $exclude_n_dg_404 = $options[20]; # 非危險份子的 [404] 不記錄

 global $current_user;
 $name = isset($current_user->user_login) ? $current_user->user_login : ( isset($_COOKIE['comment_author_'. COOKIEHASH]) ? $_COOKIE['comment_author_'. COOKIEHASH] : '' );
 $mail = isset($_COOKIE['comment_author_email_'. COOKIEHASH]) ? $_COOKIE['comment_author_email_'. COOKIEHASH] : '';

/* 不記錄人員直接返回 */
if (!$arg || (int)$arg) { // not login page
 if (($exclude_blogger && $mail == get_bloginfo('admin_email'))
  || ($exclude_login   && current_user_can('manage_options'))
  || (stripos($exclude_custom, $name) > -1)
 ) return;
}

 $qqwry = ABSPATH . "wp-content/plugins/my-visitors/ip/qqwry.dat"; // 純真 IP 庫

/* 取得訪客資料 */
 $gmt = explode(' ', gmdate('Y-m-d H:i:s', time() + get_option('gmt_offset')*3600)); // explode GMT
 $ip  = $_SERVER["REMOTE_ADDR"];

if (is_file($qqwry)) {

  include('iplocation.php');
  $iplocation = new IpLocation($qqwry);
  $separator = $iplocation->separate(1000);

  $location = $iplocation->getlocation($ip, $separator);
  if ($location['area'] == '对方和您在同一内部网') $location['area'] = '';
  $region = $location['country'].' '.$location['area'];

  if (isset($_SERVER["HTTP_VIA"])) $region .= ' (代理:'.$_SERVER["HTTP_VIA"].')';

  if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
    $ip_x = $_SERVER["HTTP_X_FORWARDED_FOR"];

    $location = $iplocation->getlocation($ip_x, $separator);
    if ($location['area'] == '对方和您在同一内部网') $location['area'] = '';
    $region_x = $location['country'].' '.$location['area'];

    $region .= " (真实IP:$ip_x $region_x)";
  }
  //$region .= " (q)"; // 後台識別

} else {

  function whois($ip) {
    $whois = "http://www.ip138.com/ips138.asp?ip=$ip";
    $ch = curl_init();
     curl_setopt ($ch, CURLOPT_URL, $whois);
     curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);
       ob_start();
       curl_exec($ch);
       curl_close($ch);
       $html = mb_convert_encoding(ob_get_contents(), 'UTF-8', 'GB2312');
       ob_end_clean();
    $tmp = @strpos($html, '<li>本站主数据：') + 22;
    return $regn = ($tmp < 23) ? '' : substr($html, $tmp, strpos($html, '</li>', $tmp) - $tmp);
  }

  if (function_exists('curl_init')) {
   $region = whois($ip);

   if (isset($_SERVER["HTTP_VIA"])) $region .= ' (代理:'.$_SERVER["HTTP_VIA"].')';

   if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
    $ip_x = $_SERVER["HTTP_X_FORWARDED_FOR"];
    $region_x = whois($ip_x);
    $region .= " (真实IP:$ip_x $region_x)";
   }

  } else $region = '';

}

 $requrl = (is_404() ? '[404]' : '') . $_SERVER['REQUEST_URI'];
 $refurl = isset($_SERVER['HTTP_REFERER']) ? str_replace('http://'. $_SERVER['HTTP_HOST'], '', $_SERVER['HTTP_REFERER']) : '';


/* 訪客瀏覽器及作業系統識別
 * 可參考: http://www.useragentstring.com/pages/useragentstring.php
 */
$ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

if (!$ua) {
 $bs = '?'; $ver = $os = '';

} else {

 if ($exclude_n_dg_404 && is_404()) return; # 非危險份子的 [404] 不記錄

 $bs = $ver = $os = $b ='';

# ROBOT -------------------------------
$array = array(
# CRAWLERS
'ABACHOBot',
'Accoona-AI-Agent',
'AnyApexBot',
'Arachmo',
'B-l-i-t-z-B-O-T',
'Baiduspider',
'BecomeBot',
'Bimbot',
'BlitzBOT',
'boitho.com-dc',
'boitho.com-robot',
'btbot',
'Cerberian Drtrs',
'Charlotte',
'ConveraCrawler',
'cosmos',
'DataparkSearch',
'DiamondBot',
'Discobot',
'Dotbot',
'EmeraldShield.com WebBot',
'envolk[ITS]spider',
'EsperanzaBot',
'Exabot',
'FAST Enterprise Crawler',
'FAST-WebCrawler',
'FDSE robot',
'FindLinks',
'FurlBot',
'FyberSpider',
'g2crawler',
'Gaisbot',
'GalaxyBot',
'genieBot',
'Gigabot',
'Girafabot',
'Googlebot',
'Googlebot-Image',
'hl_ftien_spider',
'htdig',
'ia_archiver',
//'ichiro', // 有些 mobile 也用到
'IRLbot',
'IssueCrawler',
'Jyxobot',
'LapozzBot',
'Larbin',
'LinkWalker',
'lmspider',
'lwp-trivial',
'mabontland',
'magpie-crawler',
'Mediapartners-Google',
'MJ12bot',
'Mnogosearch',
'mogimogi',
'MojeekBot',
'Morning Paper',
'msnbot',
'MSRBot',
'MVAClient',
'NetResearchServer',
'NG-Search',
'nicebot',
'noxtrumbot',
'Nusearch Spider',
'NutchCVS',
'oegp',
'OmniExplorer_Bot',
'OOZBOT',
'Orbiter',
'PageBitesHyperBot',
'polybot',
'Pompos',
'Psbot',
'PycURL',
'RAMPyBot',
'RufusBot',
'SandCrawler',
'SBIder',
'Scrubby',
'SearchSight',
'Seekbot',
'semanticdiscovery',
'Sensis Web Crawler',
'SEOChat::Bot',
'Shim-Crawler',
'ShopWiki',
'Shoula robot',
'silk',
'Snappy',
'sogou spider',
'Speedy Spider',
'Sqworm',
'StackRambler',
'SurveyBot',
'SynooBot',
'Teoma',
'TerrawizBot',
'TheSuBot',
'Thumbnail.CZ robot',
'TinEye',
'TurnitinBot',
'updated',
'Vagabondo',
'VoilaBot',
'Vortex',
'voyager',
'VYU2',
'webcollage',
'Websquash.com',
'wf84',
'WoFindeIch Robot',
'Xaldon_WebSpider',
'yacy',
'Yahoo! Slurp',
'YahooSeeker',
'YahooSeeker-Testing',
'YandexBot',
'yoogliFetchAgent',
'Zao',
'Zealbot',
'zspider',
'ZyBorg',

# LINK CHECKERS
'AbiLogicBot',
'Link Valet',
'Link Validity Check',
'LinksManager.com_bot',
'Mojoo Robot',
'Notifixious',
'online link validator',
'Ploetz + Zeller',
'Reciprocal Link System PRO',
'REL Link Checker Lite',
'SiteBar',
'Vivante Link Checker',
'W3C-checklink',
'Xenu Link Sleuth',

# E-MAIL COLLECTORS
'EmailSiphon',

# VALIDATORS
'CSE HTML Validator',
'CSSCheck',
'Cynthia',
'HTMLParser',
'P3P Validator',
'W3C_CSS_Validator',
'W3C_Validator',
'WDG_Validator',

# FEED READERS
'Bloglines',
'everyfeed-spider',
'FeedFetcher-Google',
'Gregarius',

# LIBRARIES
'Java',
'libwww-perl',
'Peach',
'Python-urllib',

# OTHERS
'!Susie',
'Amaya',
'Cocoal.icio.us',
'DomainsDB.net MetaCrawler',
'GSiteCrawler',
'Snoopy',
'URD-MAGPIE',
'Windows-Media-Player',
'Playstation',
'Offline Explorer',
'SuperBot',
'Web Downloader',
'WebCopier',
'WebZIP',
'Wget',

# my addition
'AppEngine-Google',
'betaBot',
'bingbot',
'binlar',
'Birubot',
'bitlybot',
'BuiltWith',
'CakePHP',
'CC Metadata Scaper',
'Cityreview Robot',
'Comodo-Certificates-Spider',
'COMODOspider',
'Compression Test',
'Cssengine',
'dom2dom',
'FeedBurner',
'feeddemon',
'FriendFeedBot',
'Google Web Preview',
'guigou',
'heritrix',
'HtmlDownloader',
'HttpClient',
'HTTPGet',
'HTTP_Request',
'HuaweiSymantecSpider',
'iaskspider',
'ICS',
'Indy Library',
'InetURL',
'KoepaBot',
'libcurl-agent',
'libcurl',
'libghttp',
'LongURL',
'Mail.Ru',
'MetaURI API',
'Microsoft Data Access',
'MLBot',
'mobile goo',
'Moreoverbot',
'MS FrontPage',
'MS Search',
'NetcraftSurveyAgent',
'NING',
'NjuiceBot',
'panelbot',
'picsearch',
'PostPost',
'PostRank',
'Powermarks',
'Protocol',
'Purebot',
'qihoobot',
'R6_CommentReader',
'resolver',
'RSSMicro',
'RSSOwl',
'sabilulungan',
'Scout',
'Search17Bot',
'SiteBot',
'Sogou-Test-Spider',
'Sogou web spider',
'Sosospider',
'Sosoimagespider',
'spbot',
'Spinn3r',
'Superfeedr',
'Tsinghua AI Lab Robot',
'Toread-Crawler',
'TotalValidator',
'TweetmemeBot',
'Twingly Recon',
'UNTRUSTED',
'UnwindFetchor',
'urlfan-bot',
'Urlfilebot',
'vikspider',
'WebCapture',
'WinHttp',
'WordPress',
'woriobot',
'WTP Add-On',
'XML-RPC',
'yacybot',
'Yeti',
'YodaoBot',
'YoudaoBot',
'YRSpider',

# finally check
'bot',
'crawler',
'spider',
);
  for ($i = 0; isset($array[$i]) && !$bs; $i++) {
    if (stripos($ua, $array[$i]) > -1) $bs = $array[$i];
  }

if ($exclude_n_404_bt && !is_404() && $bs) return; # 非 [404] 的爬蟲不記錄

if (!$bs) { // 基本上 bot 最多, 如果訪客是 bot 就不用再往下走

# MOBILE (agent 太亂, 不一定準) -------------
$array = array(
'Android', '',
'BlackBerry', '',
'BREW', '',
'DoCoMo', '',
'iCab', '',
'iPhone', 'Version',
'iPod', 'Version',
'Motorola', 'Elaine',
'Nokia', '',
'Openwave', '',
'Opera Mini', '',
'Opera Mobi', '',
'Palm', '',
'Samsung', '',
'Sanyo', '',
'SonyEricsson', '',
'SymbianOS', '',
'UC Browser', '',
'UCWeb', '',

# finally check
'J2ME', 'UCWEB',
'mobile', '',
);
  for ($i = 0; isset($array[$i]) && !$os; $i+= 2) {
    if (stripos($ua, $array[$i]) > -1) {
      $bs = $array[$i];
      $vc = !$array[$i+1] ? $array[$i] : $array[$i+1];
      eregi($vc.'[\ |\w|\/]*([[:digit:]\.]+)', $ua, $b);
      $ver = @$b[1];
      $os = 'Mobile';
    }
  }


# BROWSERS ----------------------------
$array = array(
'ABrowse', '',
'Acoo Browser', 'MSIE',
'America Online Browser', '',
'AmigaVoyager', '',
'AOL', 'AOL',
'Arora', '',
'Avant Browser', 'MSIE',
'Beonex', 'Beonex',
'BonEcho', '',
'BrowseX', '',
'Camino', '',
'Charon', 'Mozilla',
'Cheshire', '',
'Chimera', '',
'CometBird', '',
'Comodo_Dragon', '',
'Crazy Browser', '',
'Cyberdog', '',
'Deepnet Explorer', '',
'DeskBrowse', '',
'Dillo', '',
'Dooble', '',
'Element Browser', '',
'Elinks', '',
'Enigma Browser', '',
'Epiphany', '',
'Escape', '',
'Fennec', '',
'Firebird', '',
'Flock', '',
'Fluid', '',
'Galaxy', '',
'Galeon', '',
'GranParadiso', '',
'GreenBrowser', '',
'Hana', '',
'HotJava', '',
'IBM WebExplorer', '',
'IBrowse', '',
'iCab', '',
'Iceape', '',
'IceCat', '',
'Iceweasel', '',
'iNet Browser', '',
'iRider', '',
'Iron', '',
'K-Meleon', '',
'K-Ninja', '',
'Kapiko', '',
'Kazehakase', '',
'KKman', '',
'KMLite', '',
'Konqueror', '',
'LeechCraft', '',
'Links', '',
'Lobo', '',
'lolifox', '',
'Lorentz', '',
'Lunascape', '',
'Lynx', '',
'Madfox', '',
'Maxthon', '',
'Midori', '',
'Minefield', '',
'Minimo', '',
'MultiZilla', '',
'myibrow', '',
'MyIE2', '',
'Namoroka', '',
'NCSA_Mosaic', '',
'NetFront', '',
'NetNewsWire', '',
'NetPositive', '',
'Netscape', 'Netscape[0-9]?',
'NetSurf', '',
'OmniWeb', '',
'Opera', 'Version',
'Orca', '',
'Oregano', '',
'Palemoon', '',
'Phoenix', '',
'Pogo', '',
'Prism', '',
'QtWeb Internet Browser', '',
'retawq', '',
'SeaMonkey', '',
'Shiira', '',
'Shiretoko', '',
'Sleipnir', '',
'SlimBrowser', '',
'Stainless', '',
'Sunrise', '',
'TeaShark', '',
'Thunderbird', '',
'uZard Web', '',
'uzbl', '',
'Vonkeror', '',
'w3m', '',
'WorldWideWeb', '',
'Wyzo', '',

# my addition
'360SE', '',
'Android', '',
'MegaCorpBrowser', '',
'MetaSr', '',
'MSN Explorer', '',
'QQBrowser', '',
'Reeder', '',
'SaaYaa', '',
'TheWorld', '',
'TencentTraveler', '',
'Webster Pro', '',


# finally check
'Firefox', '',
'ChromePlus', '',
'Chrome', '',
'Safari', 'Version',
'MSIE', '',
//'Mozilla', '',

);
  for ($i = 0; isset($array[$i]) && !$bs; $i+= 2) {
    if (stripos($ua, $array[$i]) > -1) {
      $bs = $array[$i];
      $vc = !$array[$i+1] ? $array[$i] : $array[$i+1];
      eregi($vc.'[\ |\w|\/]*([[:digit:]\.]+)', $ua, $b);
      $ver = $b[1];
    }
  }


# OS ----------------------------------
$array = array(
'Windows NT 7.0', 'Windows 7',
'Windows NT 6.1', 'Windows 7',
'Windows NT 6.0', 'Windows Vista',
'Windows NT 5.2', 'Windows Server 2003',
'Windows NT 5.1', 'Windows XP',
'Windows NT 5.0', 'Windows 2000',
'Windows NT', '',
'Windows 2000', '',
'Win 9x 4.90', 'Windows ME',
'Win98', 'Windows 98 SE',
'Windows 98', '',
'Win95', 'Windows 95',
'Windows 95', '',
'Windows', '',
'Darwin', 'Mac OS X',
'Mac OS X', '',
'Macintosh', 'Mac OS X Classic',
'AmigaOS', '',
'Xubuntu', '',
'Kubuntu', '',
'Ubuntu', '',
'Debian', '',
'Linux', '',
'Unix', '',
'OS/2', '',
'SunOS', '',
'BSD', '',
'Wii', '',
'webOS', '',
);
  for ($i = 0; isset($array[$i]) && !$os; $i+= 2) {
    if (stripos($ua, $array[$i]) > -1) $os = !$array[$i+1] ? $array[$i] : $array[$i+1];
  }

} // end if (!$bs)

} // end if (!$ua) else


   $requrl = utf8_uri_encode($requrl);
   $refurl = utf8_uri_encode($refurl);

 // for sub-folder blog
 $host = 'http://' . $_SERVER['HTTP_HOST'];
 $sub_url = str_replace($host, '', get_option('home'));
 if ($sub_url) {
   $requrl = str_replace($sub_url, '', $requrl); // 去除 $sub_url
   $refurl = str_replace($sub_url, '', $refurl);
 }

 if (!$options[3]) $ua = ''; # option $save_agent

 global $wpdb, $visitors, $error, $user_name;

 if ($arg) { // login 和 comment 才有 $arg

   if ($error) { // login error
     $arg = strip_tags($error);
     $tmp = strpos($arg, '：') + 3;
     $arg = substr($arg, $tmp, strpos($arg, '。') - $tmp);
     $name = $user_name . '　' . $arg . ' (e)';

     /*  郵件通知 */
     if ($options[9]) { # option $email_enable
      global $phpmailer;
      class_exists('PHPMailer') or require(ABSPATH . WPINC . '/class-phpmailer.php');
      $phpmailer = new PHPMailer();
      $phpmailer->AddAddress(get_bloginfo('admin_email'));
      $phpmailer->Body = "<div>错误：$arg<br/><br/>登陆名: $user_name<br/>IP: $ip $region</div>";
      $phpmailer->CharSet = 'UTF-8';
      $phpmailer->ContentType = 'text/html';
      $phpmailer->FromName = get_option('blogname');
      $phpmailer->From = 'no-reply@' . preg_replace('#^www\.#', '', strtolower($_SERVER['SERVER_NAME']));
      $phpmailer->Subject = "※ 登陆错误通知";
      $phpmailer->Send(); // 寄出
     }

   }
   elseif ((int)$arg) { // comment
     $comment = get_comment($arg);
     $name = $comment->comment_author . " $arg";
   }
   else $name = $arg . ' (l)'; // login
 }

 $wpdb->query("INSERT INTO ". $visitors ." VALUES ('', '$gmt[0]', '$gmt[1]', '$ip', '$region', '$name', '$requrl', '$refurl', '$ua', '$bs', '$ver', '$os')");

 if (!function_exists('process_postviews') && is_singular()) { // 訪問計數
   global $post;
   $post_ID = $post->ID;
   if ($post_ID) {
    if($options[22]) { # 是否啟用 Ajax 計數
      if ($options[23]) wp_print_scripts('jquery'); # 是否由本插件載入 jQ
      echo "\n<!-- postviews -->\n<script type='text/javascript'>\n//<![CDATA[\njQuery.ajax({type:'GET',url:'".plugins_url('my-visitors/postviews.php')."',data:'postviews_id=$post_ID',cache:false});\n//]]>\n</script>\n\n";
    } else {
      $post_views = (int)get_post_meta($post_ID, 'views', true);
      if(!update_post_meta($post_ID, 'views', ($post_views+1))) {
        add_post_meta($post_ID, 'views', 1, true);
      }
    }
   }
 }

?>