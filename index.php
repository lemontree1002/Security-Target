
<?php

define( 'DVWA_WEB_PAGE_TO_ROOT', '' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaPageStartup( array( 'authenticated', 'phpids' ) );

$page = dvwaPageNewGrab();
$page[ 'title' ]   = 'Welcome' . $page[ 'title_separator' ].$page[ 'title' ];
$page[ 'page_id' ] = 'home';

$page[ 'body' ] .= "
<div class=\"body_padded\">
    <br />
	<h1>欢迎来到Web安全移动靶场</h1>
	<p>本站点用于演示各类Web安全漏洞的利用方式和实现。</p>
	<p>请先点击右边的 <em>设置/重置数据库</em>选项初始化或重置数据库。 再从左侧边栏中选择<em>对应的漏洞类型</em>开始实验。</p>
	<p>Good Luck!</p>
	<hr />
	<br />
</div>";

dvwaHtmlEcho( $page );

?>
