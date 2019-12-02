
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
	<h1>欢迎来到Web安全移动靶场-文件上传专项</h1>
	<p>本页面用于演示各类<em>文件上传</em>漏洞。</p>
	<p>左侧列表中可以选择不同的文件上传漏洞任务，请按照要求和提示利用漏洞。其中每个任务均有<em>显示源码</em>和<em>查看提示</em>
	两个按钮，可分别用于查看当前页面的后台核心代码以及漏洞提示。</p>
	<p>Good Luck!</p>
	<hr />
	<br />
</div>";

dvwaHtmlEcho( $page );

?>
