
<?php

define( 'DVWA_WEB_PAGE_TO_ROOT', '' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'includes/dvwaPage.inc.php';

dvwaPageStartup( array( 'authenticated', 'phpids' ) );

$page = dvwaPageNewGrab();
$page[ 'title' ]   = 'Welcome' . $page[ 'title_separator' ].$page[ 'title' ];
$page[ 'page_id' ] = 'home';

$page[ 'body' ] .= "
<div class=\"body_padded\">
    <br />
	<h1>欢迎来到Web安全移动靶场-SQL注入专项</h1>
	<p>本页面用于演示各类<em>SQL注入漏洞</em>的利用方式。</p>
	<p>这个专项页面采用独立的数据库，若第一次使用，请先点击右边的 <em>设置/重置数据库</em>选项初始化或重置数据库。
	 再从左侧边栏中选择<em>不同的任务</em>开始实验。</p>
	<p>Good Luck!</p>
	<hr />
	<br />
</div>";

dvwaHtmlEcho( $page );

?>
