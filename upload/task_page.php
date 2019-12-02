<?php

    $param = $_SERVER["QUERY_STRING"];
    parse_str($param, $items);
    $task = $items["task"];

//    $if_show_code = false;
//    $acton = "";
//    if(array_key_exists("action", $items)){
//        $if_show_code = true;
//        $acton = $items["action"];
//    }

//    echo $page;

define( 'DVWA_WEB_PAGE_TO_ROOT', '' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'includes/dvwaPage.inc.php';

dvwaPageStartup( array( 'authenticated', 'phpids' ) );

$page = dvwaPageNewGrab();
$page[ 'title' ]   = 'Vulnerability: File Upload' . $page[ 'title_separator' ].$page[ 'title' ];
$page[ 'page_id' ] = "task_{$task}";
$page[ 'help_button' ]   = "task_{$task}";
$page[ 'source_button' ] = "task_{$task}";

dvwaDatabaseConnect();

//require_once DVWA_WEB_PAGE_TO_ROOT . "source/task-{$task}.php";

// Check if folder is writeable
$WarningHtml = '';
if( !is_writable( $PHPUploadPath ) ) {
    $WarningHtml .= "<div class=\"warning\">Incorrect folder permissions: {$PHPUploadPath}<br /><em>Folder is not writable.</em></div>";
}
// Is PHP-GD installed?
if( ( !extension_loaded( 'gd' ) || !function_exists( 'gd_info' ) ) ) {
    $WarningHtml .= "<div class=\"warning\">The PHP module <em>GD is not installed</em>.</div>";
}

ob_start();
require("source/pass-{$task}/task-{$task}.php");
include_once('foot.html');
$content = ob_get_contents();
ob_end_clean();

$page[ 'body' ] .= $content;
//$page[ 'body' ] .= eval(file_get_contents("source/task-{$task}.php"));
//$page[ 'body' ] .= file("source/task-{$task}.php");

$page[ 'body' ] .= "
<br />
<div>
    {$html}
	<h2>More Information</h2>
	<ul>
		<li>" . dvwaExternalLinkUrlGet( 'https://www.owasp.org/index.php/Unrestricted_File_Upload' ) . "</li>
		<li>" . dvwaExternalLinkUrlGet( 'https://blogs.securiteam.com/index.php/archives/1268' ) . "</li>
		<li>" . dvwaExternalLinkUrlGet( 'https://www.acunetix.com/websitesecurity/upload-forms-threat/' ) . "</li>
	</ul>
</div>";

dvwaHtmlEcho( $page );

?>

