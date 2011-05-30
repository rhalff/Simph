<?php
/**
*
* Config for Simph
*
* Mainly for site-wide html adjustments
*
*/

$CONFIG = array();
$CONFIG['locale'] = 'UTF-8';

// Define site-wide javascripts (will appear on all pages)
$CONFIG['javascripts'] = array(

        // jquery & ui support : http://jquery.com/
        'http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js',
        'http://ajax.googleapis.com/ajax/libs/jqueryui/1/jquery-ui.min.js',

        // mootools support : http://mootools.net/
		# 'http://ajax.googleapis.com/ajax/libs/mootools/1/mootools-yui-compressed.js',

        // swfobject if you may : http://code.google.com/p/swfobject/
        # 'https://ajax.googleapis.com/ajax/libs/swfobject/2/swfobject.js',

        // Yahoo! User Interface Library (YUI) : http://developer.yahoo.com/yui/
        # 'https://ajax.googleapis.com/ajax/libs/yui/3/build/yui/yui-min.js',

        // Dojo : http://dojotoolkit.org
        # 'https://ajax.googleapis.com/ajax/libs/dojo/1/dojo/dojo.xd.js ',

        // WebFont Loader : http://code.google.com/apis/webfonts/docs/webfont_loader.html
        # 'https://ajax.googleapis.com/ajax/libs/webfont/1/webfont.js',

        // site wide javascript
        'js/site.js'
		);

// Define site-wide stylesheets (will appear on all pages)
$CONFIG['stylesheets'] = array(

        // jquery ui specific styles
        'http://ajax.googleapis.com/ajax/libs/jqueryui/1/themes/base/jquery-ui.css',

        // site wide styles
		'css/style.css'
		//'css/extravaganza.css'=> array('only'=>'photos/Photos')
		);

/**
*
* Here you can define the side-wide widgets.
* However you can exclude some pages, or only show them om some pages.
*
* Note however that it's also possible to load these from your html by
* Using the relevant tag. e.g. {MENU}
*
* The form is: $CONFIG['widgets'][<tagName or id>][<widget name>] = <option array>
*
*/
$CONFIG['widgets'] = array();
$CONFIG['widgets']['body'] = array('footer');
$CONFIG['widgets']['#content']['menu']   = array('pos'=>'prepend');
$CONFIG['widgets']['#content']['footer'] = array('only'=>array('test-page'));

?>
