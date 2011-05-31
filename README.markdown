Simph
=====

Is intended for small sites where a CMS is overkill.

It provides ѕome functions to avoid redundancy inside the different html pages.

HTML Pages
----------

The basic pages of the site can be made in html. No php is allowed inside these pages.
You can however inject or place output from the widgets inside the page.

The goal is to keep it as simple as possible.

The power of the system lies in the external libraries you include. e.g. jquery, ZEND FrameWork etc.

This functionality can be shared in the form of widgets, which are not much more then
a normal php file included somewhere in the page, either by injecting them somewhere in the document or by using tag names.

What Simph provides:

 - A very simplistic widget system
 - A widget for Automatic structuring of your navigation, based on the pages available.
 - All url's are seo. They are based on the path and the document title.
 - Inject common content into each or some html page, e.g. stylesheets, javascripts, widgets
 - Use widget tags to place content into an html page
 

CONFIG:
-------

You can use 'except' or 'only' on any variable in the config to only target certain pages.

This goes for stylesheets, javascripts and widgets.
