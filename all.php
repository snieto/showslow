<?
require_once('global.php');
?><html>
<head>
<title>Show Slow</title>
<style type="text/css">
/*margin and padding on body element
  can introduce errors in determining
  element position and are not recommended;
  we turn them off as a foundation for YUI
  CSS treatments. */
body {
	margin:0;
	padding:0;
}
</style>

<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.7.0/build/fonts/fonts-min.css" />
<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.7.0/build/tabview/assets/skins/sam/tabview.css" />
<script type="text/javascript" src="http://yui.yahooapis.com/2.7.0/build/yahoo-dom-event/yahoo-dom-event.js"></script>

<script type="text/javascript" src="http://yui.yahooapis.com/2.7.0/build/element/element-min.js"></script>
<script type="text/javascript" src="http://yui.yahooapis.com/2.7.0/build/tabview/tabview-min.js"></script>
<? if ($showFeedbackButton) {?>
<script type="text/javascript">
  var uservoiceJsHost = ("https:" == document.location.protocol) ? "https://uservoice.com" : "http://cdn.uservoice.com";
  document.write(unescape("%3Cscript src='" + uservoiceJsHost + "/javascripts/widgets/tab.js' type='text/javascript'%3E%3C/script%3E"))
</script>
<script type="text/javascript">
UserVoice.Tab.show({ 
  key: 'showslow',
  host: 'showslow.uservoice.com', 
  forum: 'general', 
  alignment: 'right',
  background_color:'#f00', 
  text_color: 'white',
  hover_color: '#06C',
  lang: 'en'
})
</script>
<? } ?>
</head>
<body class="yui-skin-sam">
<a href="http://code.google.com/p/showslow/"><img src="showslow_icon.png" style="float: right; margin-left: 1em; border: 0"/></a>
<div style="float: right">powered by <a href="http://code.google.com/p/showslow/">showslow</a></div>
<h1>Show Slow</h1>
<?=$ShowSlowIntro?>
<div id="showslowlists" class="yui-navset">
    <ul class="yui-nav">
        <li><a href="#last100"><em>Last 100 measurements</em></a></li>
        <li class="selected"><a href="#urls"><em>URLs measured</em></a></li>
        <li><a href="#configure"><em>Configuring YSlow / PageSpeed</em></a></li>
    </ul> 
    <div class="yui-content">
        <div id="last100">
		<table>
		<tr><th>Timestamp</th><th colspan="2">YSlow grade</th><th colspan="2">PageSpeed grade</th><th style="padding-left:10px; text-align: left">URL</th></tr>
		<?
		$query = sprintf("SELECT url, o, ps_o, last_update FROM urls ORDER BY last_update DESC LIMIT 100");
		$result = mysql_query($query);

		if (!$result) {
			error_log(mysql_error());
		}

		while ($row = mysql_fetch_assoc($result)) {
		?><tr>
			<td><?=htmlentities($row['last_update'])?></td>

		<? if (is_null($row['o'])) {?>
			<td></td><td></td>
		<?}else{?>
			<td style="text-align: right; padding:0 10px 0 10px"><?=yslowPrettyScore($row['o'])?> (<?=$row['o']?>)</td>
			<td><div style="background-color: silver; width: 101px" title="Current YSlow grade: <?=yslowPrettyScore($row['o'])?> (<?=$row['o']?>)"><div style="width: <?=$row['o']+1?>px; height: 0.7em; background-color: <?=scoreColor($row['o'])?>"/></div></td>
		<?}?>

		<? if (is_null($row['ps_o'])) {?>
			<td></td><td></td>
		<?}else{?>
			<td style="text-align: right; padding:0 10px 0 10px"><?=yslowPrettyScore($row['ps_o'])?> (<?=$row['ps_o']?>)</td>
			<td><div style="background-color: silver; width: 101px" title="Current YSlow grade: <?=yslowPrettyScore($row['ps_o'])?> (<?=$row['ps_o']?>)"><div style="width: <?=$row['ps_o']+1?>px; height: 0.7em; background-color: <?=scoreColor($row['ps_o'])?>"/></div></td>
		<?}?>
			<td style="padding-left:10px"><a href="details/?url=<?=urlencode($row['url'])?>"><?=htmlentities(substr($row['url'], 0, 100))?><? if (strlen($row['url']) > 100) { ?>...<? } ?></a></td>
		</tr><?
		}

		mysql_free_result($result);
		?>
		</table>
	</div>
        <div id="urls">
		<table>
		<tr><th colspan="2">YSlow grade</th><th colspan="2">PageSpeed grade</th><th style="padding-left:10px; text-align: left">URL</th></tr>
		<?
		$query = sprintf("SELECT DISTINCT url, o, ps_o FROM urls");
		$result = mysql_query($query);

		if (!$result) {
			error_log(mysql_error());
		}

		while ($row = mysql_fetch_assoc($result)) {
		?><tr>

		<? if (is_null($row['o'])) {?>
			<td></td><td></td>
		<?}else{?>
			<td style="text-align: right; padding:0 10px 0 10px"><?=yslowPrettyScore($row['o'])?> (<?=$row['o']?>)</td>
			<td><div style="background-color: silver; width: 101px" title="Current YSlow grade: <?=yslowPrettyScore($row['o'])?> (<?=$row['o']?>)"><div style="width: <?=$row['o']+1?>px; height: 0.7em; background-color: <?=scoreColor($row['o'])?>"/></div></td>
		<?}?>

		<? if (is_null($row['ps_o'])) {?>
			<td></td><td></td>
		<?}else{?>
			<td style="text-align: right; padding:0 10px 0 10px"><?=yslowPrettyScore($row['ps_o'])?> (<?=$row['ps_o']?>)</td>
			<td><div style="background-color: silver; width: 101px" title="Current YSlow grade: <?=yslowPrettyScore($row['ps_o'])?> (<?=$row['ps_o']?>)"><div style="width: <?=$row['ps_o']+1?>px; height: 0.7em; background-color: <?=scoreColor($row['ps_o'])?>"/></div></td>
		<?}?>
			<td style="padding-left:10px"><a href="details/?url=<?=urlencode($row['url'])?>"><?=htmlentities(substr($row['url'], 0, 100))?><? if (strlen($row['url']) > 100) { ?>...<? } ?></a></td>
		</tr><?
		}

		mysql_free_result($result);
		?>
		</table>
	</div>
	<div id="configure">
		<p>
		<b style="color: red">If you're OK with all your measurements to be recorded by this instance of ShowSlow and displayed at <a href="<?=$showslow_base?>"><?=$showslow_base?></a></b>, just set these two Firefox parameters on <b>about:config</b> page:
		</p>
		<h2>Yslow 2.x</h2>
		<ul>
		<li>extensions.yslow.beaconUrl = <b style="color: blue"><?=$showslow_base?>beacon/yslow/</b></li>
		<li>extensions.yslow.optinBeacon = <b style="color: blue">true</b></li>
		</ul>
		<h2>Yslow 1.x</h2>
		<ul>
		<li>extensions.firebug.yslow.beaconUrl = <b style="color: blue"><?=$showslow_base?>beacon/yslow/</b></li>
		<li>extensions.firebug.yslow.optinBeacon = <b style="color: blue">true</b></li>
		</ul>
		<h2>PageSpeed</h2>
		<ul>
		<li>extensions.PageSpeed.beacon.minimal.url = <b style="color: blue"><?=$showslow_base?>beacon/pagespeed/</b></li>
		<li>extensions.PageSpeed.beacon.minimal.enabled = <b style="color: blue">true</b></li>
		<li>extensions.PageSpeed.beacon.minimal.autorun = <b style="color: blue">true</b></li>
		</ul>
	</div>
    </div>
</div>

<script type="text/javascript">
    var tabView = new YAHOO.widget.TabView('showslowlists');
</script>
<? if ($googleAnalyticsProfile) {?>
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker('<?=$googleAnalyticsProfile?>');
pageTracker._trackPageview();
} catch(err) {}</script>
<?}?>
</body></html>
