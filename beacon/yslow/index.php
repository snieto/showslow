<?php 
require_once('../../global.php');

function updateUrlAggregates($url_id, $w, $o, $r)
{
	# updating latest values for the URL
	$query = sprintf("UPDATE urls set w = '%d', o = '%d', r = '%d', last_update = now() WHERE id = '%d'",
		mysql_real_escape_string($w),
		mysql_real_escape_string($o),
		mysql_real_escape_string($r),
		mysql_real_escape_string($url_id)
	);
	$result = mysql_query($query);

	if (!$result) {
		error_log(mysql_error());
		exit;
	}

}

function getUrlId($url)
{
	global $limitURLs;

	if ($limitURLs !== false && is_array($limitURLs)) {
		$matched = false;

		foreach ($limitURLs as $prefix) {
			if (substr($url, 0, strlen($prefix)) == $prefix) {
				$matched = true;
				break;
			}
		}

		if (!$matched) {
			header('HTTP/1.0 400 Bad Request');

			?><html>
<head>
<title>Bad Request: YSlow beacon</title>
</head>
<body>
<h1>Bad Request: YSlow beacon</h1>
<p>URL doesn't match any of the prefixes.</p>
</body></html>
<?php 
			exit;
		}
	}

	# get URL id
	$query = sprintf("SELECT id FROM urls WHERE url = '%s'", mysql_real_escape_string($url));
	$result = mysql_query($query);

	if (!$result) {
		error_log(mysql_error());
		exit;
	}

	if (mysql_num_rows($result) == 1) {
		$row = mysql_fetch_assoc($result);
		return $row['id'];
	} else if (mysql_num_rows($result) == 0) {
		$query = sprintf("INSERT INTO urls (url) VALUES ('%s')", mysql_real_escape_string($url));
		$result = mysql_query($query);

		if (!$result) {
			error_log(mysql_error());
			exit;
		}

		return mysql_insert_id();
	} else {
		error_log('more then one entry found for the URL');
		exit;
	}

}

$post_data = file_get_contents("php://input");
$post = json_decode($post_data, true);

if (!is_null($post) && array_key_exists('g', $post)
	&& array_key_exists('i', $post) && in_array($post['i'], $YSlow2AllowedProfiles)
	&& array_key_exists('w', $post) && filter_var($post['w'], FILTER_VALIDATE_INT) !== false
	&& array_key_exists('o', $post) && filter_var($post['o'], FILTER_VALIDATE_INT) !== false
	&& array_key_exists('u', $post) && filter_var(urldecode($post['u']), FILTER_VALIDATE_URL) !== false
	&& array_key_exists('r', $post) && filter_var($post['r'], FILTER_VALIDATE_INT) !== false
	)
{
	$url_id = getUrlId(urldecode($post['u']));

	$grades = $post['g'];

	$ynumreq	= $grades['ynumreq']['score'];
	$ycdn		= $grades['ycdn']['score'];
	$yexpires	= $grades['yexpires']['score'];
	$ycompress	= $grades['ycompress']['score'];
	$ycsstop	= $grades['ycsstop']['score'];
	$yjsbottom	= $grades['yjsbottom']['score'];
	$yexpressions	= $grades['yexpressions']['score'];
	$yexternal	= $grades['yexternal']['score'];
	$ydns		= $grades['ydns']['score'];
	$yminify	= $grades['yminify']['score'];
	$yredirects	= $grades['yredirects']['score'];
	$ydupes		= $grades['ydupes']['score'];
	$yetags		= $grades['yetags']['score'];
	$yxhr		= $grades['yxhr']['score'];
	$yxhrmethod	= $grades['yxhrmethod']['score'];
	$ymindom	= $grades['ymindom']['score'];
	$yno404		= $grades['yno404']['score'];
	$ymincookie	= $grades['ymincookie']['score'];
	$ycookiefree	= $grades['ycookiefree']['score'];
	$ynofilter	= $grades['ynofilter']['score'];
	$yimgnoscale	= $grades['yimgnoscale']['score'];
	$yfavicon	= $grades['yfavicon']['score'];

	# adding new entry
	$query = sprintf("/* grades POST */ INSERT INTO yslow2 (
		`ip` , `user_agent` , `url_id` ,
		`w` , `o` , `r` , `i` , lt,
		`ynumreq`,	`ycdn`,		`yexpires`,	`ycompress`,	`ycsstop`,
		`yjsbottom`,	`yexpressions`,	`yexternal`,	`ydns`,		`yminify`,
		`yredirects`,	`ydupes`,	`yetags`,	`yxhr`,		`yxhrmethod`,
		`ymindom`,	`yno404`,	`ymincookie`,	`ycookiefree`,	`ynofilter`,
		`yimgnoscale`,	`yfavicon`, details
	)
	VALUES (inet_aton('%s'), '%s', '%d',
		'%d', '%d', '%d', '%s', '%d',
		'%d', '%d', '%d', '%d', '%d',
		'%d', '%d', '%d', '%d', '%d',
		'%d', '%d', '%d', '%d', '%d',
		'%d', '%d', '%d', '%d', '%d',
		'%d', '%d', '%s'
	)",
		mysql_real_escape_string($_SERVER['REMOTE_ADDR']),
		mysql_real_escape_string($_SERVER['HTTP_USER_AGENT']),
		mysql_real_escape_string($url_id),
		mysql_real_escape_string($post['w']),
		mysql_real_escape_string($post['o']),
		mysql_real_escape_string($post['r']),
		mysql_real_escape_string($post['i']),
		mysql_real_escape_string($post['lt']),
		mysql_real_escape_string($ynumreq),
		mysql_real_escape_string($ycdn),
		mysql_real_escape_string($yexpires),
		mysql_real_escape_string($ycompress),
		mysql_real_escape_string($ycsstop),
		mysql_real_escape_string($yjsbottom),
		mysql_real_escape_string($yexpressions),
		mysql_real_escape_string($yexternal),
		mysql_real_escape_string($ydns),
		mysql_real_escape_string($yminify),
		mysql_real_escape_string($yredirects),
		mysql_real_escape_string($ydupes),
		mysql_real_escape_string($yetags),
		mysql_real_escape_string($yxhr),
		mysql_real_escape_string($yxhrmethod),
		mysql_real_escape_string($ymindom),
		mysql_real_escape_string($yno404),
		mysql_real_escape_string($ymincookie),	
		mysql_real_escape_string($ycookiefree),	
		mysql_real_escape_string($ynofilter),
		mysql_real_escape_string($yimgnoscale),	
		mysql_real_escape_string($yfavicon),
		mysql_real_escape_string($post_data)
	);

	if (!mysql_query($query))
	{
		error_log(mysql_error());
		exit;
	}

	updateUrlAggregates($url_id, $post['w'], $post['o'], $post['r']);

} else if (array_key_exists('i', $_GET) && in_array($_GET['i'], $YSlow2AllowedProfiles)
	&& array_key_exists('w', $_GET) && filter_var($_GET['w'], FILTER_VALIDATE_INT) !== false
	&& array_key_exists('o', $_GET) && filter_var($_GET['o'], FILTER_VALIDATE_INT) !== false
	&& array_key_exists('u', $_GET) && filter_var($_GET['u'], FILTER_VALIDATE_URL) !== false
	&& array_key_exists('r', $_GET) && filter_var($_GET['r'], FILTER_VALIDATE_INT) !== false
	)
{
	$url_id = getUrlId($_GET['u']);

	# adding new entry
	$query = sprintf("/* basic GET */ INSERT INTO yslow2 (
		`ip` , `user_agent` , `url_id` ,
		`w` , `o` , `r` , `i`, lt,
		`ynumreq`,	`ycdn`,		`yexpires`,	`ycompress`,	`ycsstop`,
		`yjsbottom`,	`yexpressions`,	`yexternal`,	`ydns`,		`yminify`,
		`yredirects`,	`ydupes`,	`yetags`,	`yxhr`,		`yxhrmethod`,
		`ymindom`,	`yno404`,	`ymincookie`,	`ycookiefree`,	`ynofilter`,
		`yimgnoscale`,	`yfavicon`
	)
	VALUES (inet_aton('%s'), '%s', '%d',
		'%d', '%d', '%d', '%s', '%d',
		'%d', '%d', '%d', '%d', '%d',
		'%d', '%d', '%d', '%d', '%d',
		'%d', '%d', '%d', '%d', '%d',
		'%d', '%d', '%d', '%d', '%d',
		'%d', '%d'
	)",
		mysql_real_escape_string($_SERVER['REMOTE_ADDR']),
		mysql_real_escape_string($_SERVER['HTTP_USER_AGENT']),
		mysql_real_escape_string($url_id),
		mysql_real_escape_string($_GET['w']),
		mysql_real_escape_string($_GET['o']),
		mysql_real_escape_string($_GET['r']),
		mysql_real_escape_string($_GET['i']),
		mysql_real_escape_string($_GET['lt']),
		mysql_real_escape_string($_GET['ynumreq']),
		mysql_real_escape_string($_GET['ycdn']),
		mysql_real_escape_string($_GET['yexpires']),
		mysql_real_escape_string($_GET['ycompress']),
		mysql_real_escape_string($_GET['ycsstop']),
		mysql_real_escape_string($_GET['yjsbottom']),
		mysql_real_escape_string($_GET['yexpressions']),
		mysql_real_escape_string($_GET['yexternal']),
		mysql_real_escape_string($_GET['ydns']),
		mysql_real_escape_string($_GET['yminify']),
		mysql_real_escape_string($_GET['yredirects']),
		mysql_real_escape_string($_GET['ydupes']),
		mysql_real_escape_string($_GET['yetags']),
		mysql_real_escape_string($_GET['yxhr']),
		mysql_real_escape_string($_GET['yxhrmethod']),
		mysql_real_escape_string($_GET['ymindom']),
		mysql_real_escape_string($_GET['yno404']),
		mysql_real_escape_string($_GET['ymincookie']),
		mysql_real_escape_string($_GET['ycookiefree']),
		mysql_real_escape_string($_GET['ynofilter']),
		mysql_real_escape_string($_GET['yimgnoscale']),
		mysql_real_escape_string($_GET['yfavicon'])
	);

	if (!mysql_query($query))
	{
		error_log(mysql_error());
		exit;
	}

	updateUrlAggregates($url_id, $_GET['w'], $_GET['o'], $_GET['r']);
} else {
	header('HTTP/1.0 400 Bad Request');

	?><html>
<head>
<title>Bad Request: YSlow beacon</title>
</head>
<body>
<h1>Bad Request: YSlow beacon</h1>
<p>This is <a href="http://developer.yahoo.com/yslow/">YSlow</a> beacon entry point.</p>

<h1>Configure your YSlow</h1>
<p><b style="color: red">If you're OK with all your YSlow measurements to be recorded by this instance of ShowSlow and displayed at <a href="<?php echo $showslow_base?>"><?php echo $showslow_base?></a></b>, just set these two Firefox parameters on <b>about:config</b> page:</p>

</ul>
<h2>Yslow 2.x</h2>
<ul>
<li>extensions.yslow.beaconUrl = <b style="color: blue"><?php echo $showslow_base?>beacon/yslow/</b></li>
<li>extensions.yslow.beaconInfo = <b style="color: blue">grade</b></li>
<li>extensions.yslow.optinBeacon = <b style="color: blue">true</b></li>
</ul>

</body></html>
<?php 
}
