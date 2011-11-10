<?php defined('BASEPATH') OR exit; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US">
<head> 
	<title>Emitter</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta http-equiv="Content-Style-Type" content="text/css" />
	<meta name="Author" content="Canada Open Data Community" />
	<meta name="robots" content="index, follow" />
    <link rel="stylesheet" type="text/css" media="all" href="<?php print $base_url; ?>assets/stylesheets/stylesheet.css" />
    <!--[if gte IE 7]>
    <link rel="stylesheet" type="text/css" media="all" href="<?php print $base_url; ?>assets/stylesheets/stylesheet_ie.css" />
    <style type="text/css">.browser-incompat { display: none; }</style>
    <![endif]-->
    <!--[if lte IE 7]>
    <link rel="stylesheet" type="text/css" media="all" href="<?php print $base_url; ?>assets/stylesheets/stylesheet_ie_lte_7.css" />
    <![endif]-->
    
    <script type="text/javascript" src="<?php print $base_url; ?>assets/javascript/jquery/jquery.min.js"></script>
    
    <?php if( $load_webapp_js ) : ?>
    <link rel="stylesheet" type="text/css" media="all" href="<?php print $base_url; ?>assets/stylesheets/jquery/plugins/dataTables/demo_table.css" />
    <link rel="stylesheet" type="text/css" media="all" href="<?php print $base_url; ?>assets/stylesheets/jquery/plugins/dataTables/demo_table_jui.css" />
    
    <script type="text/javascript" src="<?php print $base_url; ?>assets/javascript/jquery/plugins/jqmodal.js"></script>
    <script type="text/javascript" src="<?php print $base_url; ?>assets/javascript/jquery/plugins/dataTables.js"></script>
    <script type="text/javascript" src="<?php print $base_url; ?>assets/javascript/dataTableManager.js"></script>
    <script type="text/javascript" src="http://dev.virtualearth.net/mapcontrol/mapcontrol.ashx?v=6.2"></script>
    <script type="text/javascript">
    var AppConfig = { base_url : '<?php print $base_url; ?>index.php/', assets_url : '<?php print $base_url; ?>assets/' };
    </script>
    <script type="text/javascript" src="<?php print $base_url; ?>assets/javascript/webapp.js"></script>
    <?php endif; ?>
    <script type="text/javascript">
    $(document).ready(function() {
		$("#browser-incompat").addClass('display-none');
    });
    </script>
</head> 
<body id="top">
<?php if( $load_webapp_js ) : ?>
<div class="loading-screen" id="loading-screen-modal">
    <div class="loading-dialog">
        <h1>Loading...</h1>
        <p>Please wait while we query the database for results based on the specified search criteria.</p>
    </div>
</div>
<?php endif; ?>
<div class="browser-incompat" id="browser-incompat">
	<div>
		<p><strong>Warning!</strong> It appears that your browser may be incompatible with this application. Please assure that you are using an up to date version of your browser with JavaScript enabled.</p>
	</div>
</div>
<div id="wrapper" class="wrapper">
    <div class="header">
		<div style="float: right;">
			<div style="text-align: right"><a href="http://twitter.com/share" class="twitter-share-button" data-url="http://www.emitter.ca/" data-text="EMITTER.CA helped me track industrial polluters in my neighbourhood! #opendata #emitter @emitterca" data-count="horizontal">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script></div>
		
        <ul class="navigation">
            <li class="first"><?php print anchor('about', 'About'); ?></li>
            <li><?php print anchor('methodology', 'Methodology'); ?></li>
            <li class="last"><?php print anchor('developer', 'Developer'); ?></li>
        </ul>
        </div>
        <a href="<?php print $base_url; ?>" title="Emitter"><img src="<?php print $base_url; ?>assets/images/emitter-logo-beta.jpg" title="Emitter" alt="Emitter" width="183px" height="43px" /></a>
        <h2 class="tagline">Tracking pollution in your neighbourhood</h2>
        <div class="clear-fix">&nbsp;</div>
    </div>
    <div class="body">