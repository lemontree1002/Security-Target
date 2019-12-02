<?php

if( !defined( 'DVWA_WEB_PAGE_TO_ROOT' ) ) {
	die( 'DVWA System error- WEB_PAGE_TO_ROOT undefined' );
	exit;
}

session_start(); // Creates a 'Full Path Disclosure' vuln.

if (!file_exists(DVWA_WEB_PAGE_TO_ROOT . '../config/config.inc.php')) {
	die ("DVWA System error - config file not found. Copy config/config.inc.php.dist to config/config.inc.php and configure to your environment.");
}

// Include configs
require_once DVWA_WEB_PAGE_TO_ROOT . '../config/config.inc.php';
//require_once( 'dvwaPhpIds.inc.php' );

// Declare the $html variable
if( !isset( $html ) ) {
	$html = "";
}

// Valid security levels
$security_levels = array('low', 'medium', 'high', 'impossible');
if( !isset( $_COOKIE[ 'security' ] ) || !in_array( $_COOKIE[ 'security' ], $security_levels ) ) {
	// Set security cookie to impossible if no cookie exists
	if( in_array( $_DVWA[ 'default_security_level' ], $security_levels) ) {
		dvwaSecurityLevelSet( $_DVWA[ 'default_security_level' ] );
	}
	else {
		dvwaSecurityLevelSet( 'impossible' );
	}

	if( $_DVWA[ 'default_phpids_level' ] == 'enabled' )
		dvwaPhpIdsEnabledSet( true );
	else
		dvwaPhpIdsEnabledSet( false );
}

// DVWA version
function dvwaVersionGet() {
	return '1.10 *Development*';
}

// DVWA release date
function dvwaReleaseDateGet() {
	return '2015-10-08';
}


// Start session functions --

function &dvwaSessionGrab() {
	if( !isset( $_SESSION[ 'dvwa' ] ) ) {
		$_SESSION[ 'dvwa' ] = array();
	}
	return $_SESSION[ 'dvwa' ];
}


function dvwaPageStartup( $pActions ) {
	if( in_array( 'authenticated', $pActions ) ) {
		if( !dvwaIsLoggedIn()) {
			dvwaRedirect( DVWA_WEB_PAGE_TO_ROOT . '../login.php' );
		}
	}

	if( in_array( 'phpids', $pActions ) ) {
		if( dvwaPhpIdsIsEnabled() ) {
			dvwaPhpIdsTrap();
		}
	}
}


function dvwaPhpIdsEnabledSet( $pEnabled ) {
	$dvwaSession =& dvwaSessionGrab();
	if( $pEnabled ) {
		$dvwaSession[ 'php_ids' ] = 'enabled';
	}
	else {
		unset( $dvwaSession[ 'php_ids' ] );
	}
}


function dvwaPhpIdsIsEnabled() {
	$dvwaSession =& dvwaSessionGrab();
	return isset( $dvwaSession[ 'php_ids' ] );
}


function dvwaLogin( $pUsername ) {
	$dvwaSession =& dvwaSessionGrab();
	$dvwaSession[ 'username' ] = $pUsername;
}


function dvwaIsLoggedIn() {
	$dvwaSession =& dvwaSessionGrab();
	return isset( $dvwaSession[ 'username' ] );
}


function dvwaLogout() {
	$dvwaSession =& dvwaSessionGrab();
	unset( $dvwaSession[ 'username' ] );
}


function dvwaPageReload() {
	dvwaRedirect( $_SERVER[ 'PHP_SELF' ] );
}

function dvwaCurrentUser() {
	$dvwaSession =& dvwaSessionGrab();
	return ( isset( $dvwaSession[ 'username' ]) ? $dvwaSession[ 'username' ] : '') ;
}

// -- END (Session functions)

function &dvwaPageNewGrab() {
	$returnArray = array(
		'title'           => 'Web安全漏洞靶场' . dvwaVersionGet() . '',
		'title_separator' => ':',
		'body'            => '',
		'page_id'         => '',
		'help_button'     => '',
		'source_button'   => '',
	);
	return $returnArray;
}


function dvwaSecurityLevelGet() {
	return isset( $_COOKIE[ 'security' ] ) ? $_COOKIE[ 'security' ] : 'impossible';
}


function dvwaSecurityLevelSet( $pSecurityLevel ) {
	if( $pSecurityLevel == 'impossible' ) {
		$httponly = true;
	}
	else {
		$httponly = false;
	}
	setcookie( session_name(), session_id(), null, '/', null, null, $httponly );
	setcookie( 'security', $pSecurityLevel, NULL, NULL, NULL, NULL, $httponly );
}


// Start message functions --

function dvwaMessagePush( $pMessage ) {
	$dvwaSession =& dvwaSessionGrab();
	if( !isset( $dvwaSession[ 'messages' ] ) ) {
		$dvwaSession[ 'messages' ] = array();
	}
	$dvwaSession[ 'messages' ][] = $pMessage;
}


function dvwaMessagePop() {
	$dvwaSession =& dvwaSessionGrab();
	if( !isset( $dvwaSession[ 'messages' ] ) || count( $dvwaSession[ 'messages' ] ) == 0 ) {
		return false;
	}
	return array_shift( $dvwaSession[ 'messages' ] );
}


function messagesPopAllToHtml() {
	$messagesHtml = '';
	while( $message = dvwaMessagePop() ) {   // TODO- sharpen!
		$messagesHtml .= "<div class=\"message\">{$message}</div>";
	}

	return $messagesHtml;
}

// --END (message functions)

function dvwaHtmlEcho( $pPage ) {
	$menuBlocks = array();

	$menuBlocks[ 'home' ] = array();
	if( dvwaIsLoggedIn() ) {
		$menuBlocks[ 'home' ][] = array( 'id' => 'home', 'name' => '主页', 'url' => '.' );
//		$menuBlocks[ 'home' ][] = array( 'id' => 'instructions', 'name' => 'Instructions', 'url' => 'instructions.php' );
		$menuBlocks[ 'home' ][] = array( 'id' => 'setup', 'name' => '设置/重置数据库', 'url' => 'source/sql-connections/setup-db.php' );
	}
	else {
		$menuBlocks[ 'home' ][] = array( 'id' => 'setup', 'name' => '设置', 'url' => '../setup.php' );
		$menuBlocks[ 'home' ][] = array( 'id' => 'instructions', 'name' => '说明', 'url' => '../instructions.php' );
	}

	if( dvwaIsLoggedIn() ) {
		$menuBlocks[ 'vulnerabilities' ] = array();
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql01', 'name' => 'SQL注入01', 'url' => 'source/Less-1/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql02', 'name' => 'SQL注入02', 'url' => 'source/Less-2/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql03', 'name' => 'SQL注入03', 'url' => 'source/Less-3/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql04', 'name' => 'SQL注入04', 'url' => 'source/Less-4/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql05', 'name' => 'SQL注入05', 'url' => 'source/Less-5/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql06', 'name' => 'SQL注入06', 'url' => 'source/Less-6/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql07', 'name' => 'SQL注入07', 'url' => 'source/Less-7/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql08', 'name' => 'SQL注入08', 'url' => 'source/Less-8/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql09', 'name' => 'SQL注入09', 'url' => 'source/Less-9/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql10', 'name' => 'SQL注入10', 'url' => 'source/Less-10/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql11', 'name' => 'SQL注入11', 'url' => 'source/Less-11/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql12', 'name' => 'SQL注入12', 'url' => 'source/Less-12/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql13', 'name' => 'SQL注入13', 'url' => 'source/Less-13/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql14', 'name' => 'SQL注入14', 'url' => 'source/Less-14/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql15', 'name' => 'SQL注入15', 'url' => 'source/Less-15/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql16', 'name' => 'SQL注入16', 'url' => 'source/Less-16/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql17', 'name' => 'SQL注入17', 'url' => 'source/Less-17/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql18', 'name' => 'SQL注入18', 'url' => 'source/Less-18/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql19', 'name' => 'SQL注入19', 'url' => 'source/Less-19/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql20', 'name' => 'SQL注入20', 'url' => 'source/Less-20/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql21', 'name' => 'SQL注入21', 'url' => 'source/Less-21/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql22', 'name' => 'SQL注入22', 'url' => 'source/Less-22/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql23', 'name' => 'SQL注入23', 'url' => 'source/Less-23/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql24', 'name' => 'SQL注入24', 'url' => 'source/Less-24/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql25', 'name' => 'SQL注入25', 'url' => 'source/Less-25/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql25a', 'name' => 'SQL注入25a', 'url' => 'source/Less-25a/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql26', 'name' => 'SQL注入26', 'url' => 'source/Less-26/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql26a', 'name' => 'SQL注入26a', 'url' => 'source/Less-26a/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql27', 'name' => 'SQL注入27', 'url' => 'source/Less-27/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql27a', 'name' => 'SQL注入27a', 'url' => 'source/Less-27a/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql28', 'name' => 'SQL注入28', 'url' => 'source/Less-28/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql28a', 'name' => 'SQL注入28a', 'url' => 'source/Less-28a/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql29', 'name' => 'SQL注入29', 'url' => 'source/Less-29/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql30', 'name' => 'SQL注入30', 'url' => 'source/Less-30/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql31', 'name' => 'SQL注入31', 'url' => 'source/Less-31/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql32', 'name' => 'SQL注入32', 'url' => 'source/Less-32/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql33', 'name' => 'SQL注入33', 'url' => 'source/Less-33/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql34', 'name' => 'SQL注入34', 'url' => 'source/Less-34/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql35', 'name' => 'SQL注入35', 'url' => 'source/Less-35/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql36', 'name' => 'SQL注入36', 'url' => 'source/Less-36/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql37', 'name' => 'SQL注入37', 'url' => 'source/Less-37/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql38', 'name' => 'SQL注入38', 'url' => 'source/Less-38/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql39', 'name' => 'SQL注入39', 'url' => 'source/Less-39/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql40', 'name' => 'SQL注入40', 'url' => 'source/Less-40/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql41', 'name' => 'SQL注入41', 'url' => 'source/Less-41/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql42', 'name' => 'SQL注入42', 'url' => 'source/Less-42/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql43', 'name' => 'SQL注入43', 'url' => 'source/Less-43/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql44', 'name' => 'SQL注入44', 'url' => 'source/Less-44/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql45', 'name' => 'SQL注入45', 'url' => 'source/Less-45/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql46', 'name' => 'SQL注入46', 'url' => 'source/Less-46/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql47', 'name' => 'SQL注入47', 'url' => 'source/Less-47/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql48', 'name' => 'SQL注入48', 'url' => 'source/Less-48/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql49', 'name' => 'SQL注入49', 'url' => 'source/Less-49/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql50', 'name' => 'SQL注入50', 'url' => 'source/Less-50/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql51', 'name' => 'SQL注入51', 'url' => 'source/Less-51/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql52', 'name' => 'SQL注入52', 'url' => 'source/Less-52/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql53', 'name' => 'SQL注入53', 'url' => 'source/Less-53/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql54', 'name' => 'SQL注入54', 'url' => 'source/Less-54/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql55', 'name' => 'SQL注入55', 'url' => 'source/Less-55/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql56', 'name' => 'SQL注入56', 'url' => 'source/Less-56/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql57', 'name' => 'SQL注入57', 'url' => 'source/Less-57/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql58', 'name' => 'SQL注入58', 'url' => 'source/Less-58/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql59', 'name' => 'SQL注入59', 'url' => 'source/Less-59/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql60', 'name' => 'SQL注入60', 'url' => 'source/Less-60/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql61', 'name' => 'SQL注入61', 'url' => 'source/Less-61/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql62', 'name' => 'SQL注入62', 'url' => 'source/Less-62/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql63', 'name' => 'SQL注入63', 'url' => 'source/Less-63/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql64', 'name' => 'SQL注入64', 'url' => 'source/Less-64/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sql65', 'name' => 'SQL注入65', 'url' => 'source/Less-65/' );
	}

	$menuBlocks[ 'meta' ] = array();
	if( dvwaIsLoggedIn() ) {
//		$menuBlocks[ 'meta' ][] = array( 'id' => 'database', 'name' => '数据库设置', 'url' => 'source/sql-connections/setup-db.php' );
//		$menuBlocks[ 'meta' ][] = array( 'id' => 'security', 'name' => '安全等级', 'url' => 'security.php' );
		$menuBlocks[ 'meta' ][] = array( 'id' => 'phpinfo', 'name' => 'PHP信息', 'url' => '../phpinfo.php' );
	}
//	$menuBlocks[ 'meta' ][] = array( 'id' => 'about', 'name' => 'About', 'url' => 'about.php' );

	if( dvwaIsLoggedIn() ) {
		$menuBlocks[ 'logout' ] = array();
		$menuBlocks[ 'logout' ][] = array( 'id' => 'back', 'name' => '返回', 'url' => '../index.php' );
		$menuBlocks[ 'logout' ][] = array( 'id' => 'logout', 'name' => '注销', 'url' => '../logout.php' );
	}

	$menuHtml = '';

	foreach( $menuBlocks as $menuBlock ) {
		$menuBlockHtml = '';
		foreach( $menuBlock as $menuItem ) {
			$selectedClass = ( $menuItem[ 'id' ] == $pPage[ 'page_id' ] ) ? 'selected' : '';
			$fixedUrl = DVWA_WEB_PAGE_TO_ROOT.$menuItem[ 'url' ];
			$menuBlockHtml .= "<li class=\"{$selectedClass}\"><a href=\"{$fixedUrl}\">{$menuItem[ 'name' ]}</a></li>\n";
		}
		$menuHtml .= "<ul class=\"menuBlocks\">{$menuBlockHtml}</ul>";
	}

	// Get security cookie --
	$securityLevelHtml = '';
	switch( dvwaSecurityLevelGet() ) {
		case 'low':
			$securityLevelHtml = 'low';
			break;
		case 'medium':
			$securityLevelHtml = 'medium';
			break;
		case 'high':
			$securityLevelHtml = 'high';
			break;
		default:
			$securityLevelHtml = 'impossible';
			break;
	}
	// -- END (security cookie)

	$phpIdsHtml   = '<em>PHPIDS:</em> ' . ( dvwaPhpIdsIsEnabled() ? 'enabled' : 'disabled' );
	$userInfoHtml = '<em> 用户：</em> ' . ( dvwaCurrentUser() );

	$messagesHtml = messagesPopAllToHtml();
	if( $messagesHtml ) {
		$messagesHtml = "<div class=\"body_padded\">{$messagesHtml}</div>";
	}

	$systemInfoHtml = "";
	if( dvwaIsLoggedIn() )
		$systemInfoHtml = "<div align=\"left\">{$userInfoHtml}<br /><em> 安全等级：</em> {$securityLevelHtml}<br /></div>";
	if( $pPage[ 'source_button' ] ) {
		$systemInfoHtml = dvwaButtonSourceHtmlGet( $pPage[ 'source_button' ] ) . " $systemInfoHtml";
	}
	if( $pPage[ 'help_button' ] ) {
		$systemInfoHtml = dvwaButtonHelpHtmlGet( $pPage[ 'help_button' ] ) . " $systemInfoHtml";
	}

	// Send Headers + main HTML code
	Header( 'Cache-Control: no-cache, must-revalidate');   // HTTP/1.1
	Header( 'Content-Type: text/html;charset=utf-8' );     // TODO- proper XHTML headers...
	Header( 'Expires: Tue, 23 Jun 2009 12:00:00 GMT' );    // Date in the past

	echo "
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">

<html xmlns=\"http://www.w3.org/1999/xhtml\">

	<head>
		<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />

		<title>{$pPage[ 'title' ]}</title>

		<link rel=\"stylesheet\" type=\"text/css\" href=\"" . DVWA_WEB_PAGE_TO_ROOT . "../dvwa/css/main.css\" />

		<link rel=\"icon\" type=\"\image/ico\" href=\"" . DVWA_WEB_PAGE_TO_ROOT . "../favicon.ico\" />

		<script type=\"text/javascript\" src=\"" . DVWA_WEB_PAGE_TO_ROOT . "../dvwa/js/dvwaPage.js\"></script>

	</head>

	<body class=\"home\">
		<div id=\"container\">

			<div id=\"header\">

<!--			<img src=\"" . DVWA_WEB_PAGE_TO_ROOT . "dvwa/images/logo.png\" alt=\"Damn Vulnerable Web Application\" />  -->

			</div>

			<div id=\"main_menu\">

				<div id=\"main_menu_padded\">
				{$menuHtml}
				</div>

			</div>

			<div id=\"main_body\">

				{$pPage[ 'body' ]}
				<br /><br />
				{$messagesHtml}

			</div>

			<div class=\"clear\">
			</div>

			<div id=\"system_info\">
				{$systemInfoHtml}
			</div>

			<div id=\"footer\">

				<p>Web安全漏洞靶场</p>
				<script src='" . DVWA_WEB_PAGE_TO_ROOT . "..//dvwa/js/add_event_listeners.js'></script>

			</div>

		</div>

	</body>

</html>";
}


function dvwaHelpHtmlEcho( $pPage ) {
	// Send Headers
	Header( 'Cache-Control: no-cache, must-revalidate');   // HTTP/1.1
	Header( 'Content-Type: text/html;charset=utf-8' );     // TODO- proper XHTML headers...
	Header( 'Expires: Tue, 23 Jun 2009 12:00:00 GMT' );    // Date in the past

	echo "
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">

<html xmlns=\"http://www.w3.org/1999/xhtml\">

	<head>

		<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />

		<title>{$pPage[ 'title' ]}</title>

		<link rel=\"stylesheet\" type=\"text/css\" href=\"" . DVWA_WEB_PAGE_TO_ROOT . "../dvwa/css/help.css\" />

		<link rel=\"icon\" type=\"\image/ico\" href=\"" . DVWA_WEB_PAGE_TO_ROOT . "../favicon.ico\" />

	</head>

	<body>

	<div id=\"container\">

			{$pPage[ 'body' ]}

		</div>

	</body>

</html>";
}


function dvwaSourceHtmlEcho( $pPage ) {
	// Send Headers
	Header( 'Cache-Control: no-cache, must-revalidate');   // HTTP/1.1
	Header( 'Content-Type: text/html;charset=utf-8' );     // TODO- proper XHTML headers...
	Header( 'Expires: Tue, 23 Jun 2009 12:00:00 GMT' );    // Date in the past

	echo "
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">

<html xmlns=\"http://www.w3.org/1999/xhtml\">

	<head>

		<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />

		<title>{$pPage[ 'title' ]}</title>

		<link rel=\"stylesheet\" type=\"text/css\" href=\"" . DVWA_WEB_PAGE_TO_ROOT . "../dvwa/css/source.css\" />

		<link rel=\"icon\" type=\"\image/ico\" href=\"" . DVWA_WEB_PAGE_TO_ROOT . "../favicon.ico\" />

	</head>

	<body>

		<div id=\"container\">

			{$pPage[ 'body' ]}

		</div>

	</body>

</html>";
}

// To be used on all external links --
function dvwaExternalLinkUrlGet( $pLink,$text=null ) {
	if(is_null( $text )) {
		return '<a href="' . $pLink . '" target="_blank">' . $pLink . '</a>';
	}
	else {
		return '<a href="' . $pLink . '" target="_blank">' . $text . '</a>';
	}
}
// -- END ( external links)

function dvwaButtonHelpHtmlGet( $pId ) {
	$security = dvwaSecurityLevelGet();
	return "<input type=\"button\" value=\"查看帮助\" class=\"popup_button\" id='help_button' data-help-url='" . DVWA_WEB_PAGE_TO_ROOT . "../vulnerabilities/view_help.php?id={$pId}&security={$security}' )\">";
}


function dvwaButtonSourceHtmlGet( $pId ) {
	$security = dvwaSecurityLevelGet();
	return "<input type=\"button\" value=\"查看源码\" class=\"popup_button\" id='source_button' data-source-url='" . DVWA_WEB_PAGE_TO_ROOT . "../vulnerabilities/view_source.php?id={$pId}&security={$security}' )\">";
}


// Database Management --

if( $DBMS == 'MySQL' ) {
	$DBMS = htmlspecialchars(strip_tags( $DBMS ));
	$DBMS_errorFunc = 'mysqli_error()';
}
elseif( $DBMS == 'PGSQL' ) {
	$DBMS = htmlspecialchars(strip_tags( $DBMS ));
	$DBMS_errorFunc = 'pg_last_error()';
}
else {
	$DBMS = "No DBMS selected.";
	$DBMS_errorFunc = '';
}

//$DBMS_connError = '
//	<div align="center">
//		<img src="' . DVWA_WEB_PAGE_TO_ROOT . 'dvwa/images/logo.png" />
//		<pre>Unable to connect to the database.<br />' . $DBMS_errorFunc . '<br /><br /></pre>
//		Click <a href="' . DVWA_WEB_PAGE_TO_ROOT . 'setup.php">here</a> to setup the database.
//	</div>';

function dvwaDatabaseConnect() {
	global $_DVWA;
	global $DBMS;
	//global $DBMS_connError;
	global $db;

	if( $DBMS == 'MySQL' ) {
		if( !@($GLOBALS["___mysqli_ston"] = mysqli_connect( $_DVWA[ 'db_server' ],  $_DVWA[ 'db_user' ],  $_DVWA[ 'db_password' ] ))
		|| !@((bool)mysqli_query($GLOBALS["___mysqli_ston"], "USE " . $_DVWA[ 'db_database' ])) ) {
			//die( $DBMS_connError );
			dvwaLogout();
			dvwaMessagePush( 'Unable to connect to the database.<br />' . $DBMS_errorFunc );
			dvwaRedirect( DVWA_WEB_PAGE_TO_ROOT . 'setup.php' );
		}
		// MySQL PDO Prepared Statements (for impossible levels)
		$db = new PDO('mysql:host=' . $_DVWA[ 'db_server' ].';dbname=' . $_DVWA[ 'db_database' ].';charset=utf8', $_DVWA[ 'db_user' ], $_DVWA[ 'db_password' ]);
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	}
	elseif( $DBMS == 'PGSQL' ) {
		//$dbconn = pg_connect("host={$_DVWA[ 'db_server' ]} dbname={$_DVWA[ 'db_database' ]} user={$_DVWA[ 'db_user' ]} password={$_DVWA[ 'db_password' ])}"
		//or die( $DBMS_connError );
		dvwaMessagePush( 'PostgreSQL is not yet fully supported.' );
		dvwaPageReload();
	}
	else {
		die ( "Unknown {$DBMS} selected." );
	}
}

// -- END (Database Management)


function dvwaRedirect( $pLocation ) {
	session_commit();
	header( "Location: {$pLocation}" );
	exit;
}

// XSS Stored guestbook function --
function dvwaGuestbook() {
	$query  = "SELECT name, comment FROM guestbook";
	$result = mysqli_query($GLOBALS["___mysqli_ston"],  $query );

	$guestbook = '';

	while( $row = mysqli_fetch_row( $result ) ) {
		if( dvwaSecurityLevelGet() == 'impossible' ) {
			$name    = htmlspecialchars( $row[0] );
			$comment = htmlspecialchars( $row[1] );
		}
		else {
			$name    = $row[0];
			$comment = $row[1];
		}

		$guestbook .= "<div id=\"guestbook_comments\">Name: {$name}<br />" . "Message: {$comment}<br /></div>\n";
	}
	return $guestbook;
}
// -- END (XSS Stored guestbook)


// Token functions --
function checkToken( $user_token, $session_token, $returnURL ) {  # Validate the given (CSRF) token
	if( $user_token !== $session_token || !isset( $session_token ) ) {
		dvwaMessagePush( 'CSRF token is incorrect' );
		dvwaRedirect( $returnURL );
	}
}

function generateSessionToken() {  # Generate a brand new (CSRF) token
	if( isset( $_SESSION[ 'session_token' ] ) ) {
		destroySessionToken();
	}
	$_SESSION[ 'session_token' ] = md5( uniqid() );
}

function destroySessionToken() {  # Destroy any session with the name 'session_token'
	unset( $_SESSION[ 'session_token' ] );
}

function tokenField() {  # Return a field for the (CSRF) token
	return "<input type='hidden' name='user_token' value='{$_SESSION[ 'session_token' ]}' />";
}
// -- END (Token functions)


// Setup Functions --
$PHPUploadPath    = realpath( getcwd() . DIRECTORY_SEPARATOR . DVWA_WEB_PAGE_TO_ROOT . "hackable" . DIRECTORY_SEPARATOR . "uploads" ) . DIRECTORY_SEPARATOR;
//$PHPIDSPath       = realpath( getcwd() . DIRECTORY_SEPARATOR . DVWA_WEB_PAGE_TO_ROOT . "external" . DIRECTORY_SEPARATOR . "phpids" . DIRECTORY_SEPARATOR . dvwaPhpIdsVersionGet() . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . "IDS" . DIRECTORY_SEPARATOR . "tmp" . DIRECTORY_SEPARATOR . "phpids_log.txt" );
$PHPCONFIGPath       = realpath( getcwd() . DIRECTORY_SEPARATOR . DVWA_WEB_PAGE_TO_ROOT . "config");


$phpDisplayErrors = 'PHP function display_errors: <em>' . ( ini_get( 'display_errors' ) ? 'Enabled</em> <i>(Easy Mode!)</i>' : 'Disabled</em>' );                                                  // Verbose error messages (e.g. full path disclosure)
$phpSafeMode      = 'PHP function safe_mode: <span class="' . ( ini_get( 'safe_mode' ) ? 'failure">Enabled' : 'success">Disabled' ) . '</span>';                                                   // DEPRECATED as of PHP 5.3.0 and REMOVED as of PHP 5.4.0
$phpMagicQuotes   = 'PHP function magic_quotes_gpc: <span class="' . ( ini_get( 'magic_quotes_gpc' ) ? 'failure">Enabled' : 'success">Disabled' ) . '</span>';                                     // DEPRECATED as of PHP 5.3.0 and REMOVED as of PHP 5.4.0
$phpURLInclude    = 'PHP function allow_url_include: <span class="' . ( ini_get( 'allow_url_include' ) ? 'success">Enabled' : 'failure">Disabled' ) . '</span>';                                   // RFI
$phpURLFopen      = 'PHP function allow_url_fopen: <span class="' . ( ini_get( 'allow_url_fopen' ) ? 'success">Enabled' : 'failure">Disabled' ) . '</span>';                                       // RFI
$phpGD            = 'PHP module gd: <span class="' . ( ( extension_loaded( 'gd' ) && function_exists( 'gd_info' ) ) ? 'success">Installed' : 'failure">Missing - Only an issue if you want to play with captchas' ) . '</span>';                    // File Upload
$phpMySQL         = 'PHP module mysql: <span class="' . ( ( extension_loaded( 'mysqli' ) && function_exists( 'mysqli_query' ) ) ? 'success">Installed' : 'failure">Missing' ) . '</span>';                // Core DVWA
$phpPDO           = 'PHP module pdo_mysql: <span class="' . ( extension_loaded( 'pdo_mysql' ) ? 'success">Installed' : 'failure">Missing' ) . '</span>';                // SQLi
$DVWARecaptcha    = 'reCAPTCHA key: <span class="' . ( ( isset( $_DVWA[ 'recaptcha_public_key' ] ) && $_DVWA[ 'recaptcha_public_key' ] != '' ) ? 'success">' . $_DVWA[ 'recaptcha_public_key' ] : 'failure">Missing' ) . '</span>';

$DVWAUploadsWrite = '[User: ' . get_current_user() . '] Writable folder ' . $PHPUploadPath . ': <span class="' . ( is_writable( $PHPUploadPath ) ? 'success">Yes' : 'failure">No' ) . '</span>';                                     // File Upload
$bakWritable = '[User: ' . get_current_user() . '] Writable folder ' . $PHPCONFIGPath . ': <span class="' . ( is_writable( $PHPCONFIGPath ) ? 'success">Yes' : 'failure">No' ) . '</span>';   // config.php.bak check                                  // File Upload
$DVWAPHPWrite     = '[User: ' . get_current_user() . '] Writable file ' . $PHPIDSPath . ': <span class="' . ( is_writable( $PHPIDSPath ) ? 'success">Yes' : 'failure">No' ) . '</span>';                                              // PHPIDS

$DVWAOS           = 'Operating system: <em>' . ( strtoupper( substr (PHP_OS, 0, 3)) === 'WIN' ? 'Windows' : '*nix' ) . '</em>';
$SERVER_NAME      = 'Web Server SERVER_NAME: <em>' . $_SERVER[ 'SERVER_NAME' ] . '</em>';                                                                                                          // CSRF

$MYSQL_USER       = 'MySQL username: <em>' . $_DVWA[ 'db_user' ] . '</em>';
$MYSQL_PASS       = 'MySQL password: <em>' . ( ($_DVWA[ 'db_password' ] != "" ) ? '******' : '*blank*' ) . '</em>';
$MYSQL_DB         = 'MySQL database: <em>' . $_DVWA[ 'db_database' ] . '</em>';
$MYSQL_SERVER     = 'MySQL host: <em>' . $_DVWA[ 'db_server' ] . '</em>';
// -- END (Setup Functions)

?>
