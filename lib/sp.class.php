<?php
/**
 *
 * Simph
 *
 */
require_once 'lib/sp.widget.class.php';

class Simph {

        private $pages = array();
        private $url = '';
        private $config = array(
                        'locale'=> 'UTF8'
                        );
        private $inject = array(
                        'javascripts'=>array(),
                        'stylesheets'=>array(),
                        'widgets'=> array()
                        );

        function __construct($config = false)
        {
                if($config) $this->config = $config;
                $this->_load();
        }

        public function inject($inject)
        {
                $this->inject = $inject;
        }

        public function run()
        {

                if(!isset($this->pages[$this->url])) throw new Exception("Page not found");

                $doc = $this->pages[$this->url]['document'];

                $this->addJavascripts($doc, $this->inject['javascripts']);
                $this->addStylesheets($doc, $this->inject['stylesheets']);
                $this->addBase($doc);
                $this->addEncoding($doc);
                $this->addAttribTo($doc, 'body', 'id', 'page-' . strtolower($this->urlify($this->url)));

                // export desired system vars to the widgets
                $vars = array(
                                'url'=>$this->url,
                                'pages'=>$this->pages
                             );

                $this->attachWidgets($doc, $vars);

                $content = $this->placeWidgets($doc->saveXML(), $vars);

                $this->serve($content);
        }

        private function serve($content)
        {
                print($content);
        }

        private function attachWidgets($doc, $vars)
        {
                // insert the tag
                foreach($this->inject['widgets'] as $location=>$widget) {
                        foreach($widget as $name=>$value) {
                                list($name, $options) = $this->readOption($name, $value);
                                if(!$this->excluded($options)) {
                                        $this->attachWidget($doc, $location, $name, $options);
                                }
                        }
                }
        }

        /**
         *
         * Attaches a widget to the html document depending on the position
         * specified in the config
         *
         */
        private function attachWidget($doc, $location, $name, $options)
        {
                if(substr($location,0,1) == '#') {
                        $el = $this->getElementById($doc, substr($location, 1));
                } else {
                        $el =  $doc->getElementsByTagname($location)->item(0);
                }

                if(isset($options['pos']) && $options['pos'] == 'prepend') {
                        $this->prependChild($doc, $doc->createTextNode('{'.strtoupper($name).'}'), $el);
                } else {
                        // append is the default
                        $el->appendChild($doc->createTextNode('{'.strtoupper($name).'}'));
                }
        }

        /**
         *
         * Replaces the {WIDGET} tags with the content of the widget.
         *
         */
        private function placeWidgets($content, $vars)
        {

                preg_match_all("/{(\w+)}/", $content, $matches);

                $tags = !empty($matches) ? $matches[1] : array();

                $s = $r = array();

                // 2 ways, add with a tag {MENU}
                // or automatic addition based on what is specified in the config
                foreach($tags as $widget) {
                        $r[] = new SimphWidget($widget, $vars);
                        $s[] = '{'.strtoupper($widget).'}';
                }

                return str_replace($s, $r, $content);

        }

        /**
         *
         * Adds the base element to the html document.
         *
         * NOTE: This is only necessary if Simph is installed in a subdir of the document root
         *
         */
        private function addBase($doc)
        {
                $base = $doc->createElement('base');
                $base->setAttribute('href', "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/');
                $this->insertAfterTitle($doc, $base);
        }

        /**
         *
         * Add javascripts specified in the config to the document.
         *
         * Will consider excluded and only options for the current url.
         *
         */
        private function addJavascripts($doc, $scripts)
        {
                $scripts = array_reverse($scripts);
                foreach($scripts as $key=>$script) {
                        list($url, $options) = $this->readOption($key, $script);
                        if(!$this->excluded($options)) {
                                $base = $doc->createElement('script');
                                $base->setAttribute('type', 'text/javascript');
                                $base->setAttribute('src', $url);
                                $base->appendChild($doc->createTextNode(''));
                                $this->insertAfterTitle($doc, $base);
                        }
                }
        }

        /**
         *
         * Add stylesheets specified in the config to the document.
         *
         * Will consider excluded and only options for the current url.
         *
         */
        private function addStylesheets($doc, $scripts)
        {
                $scripts = array_reverse($scripts);
                foreach($scripts as $key=>$script) {
                        list($url, $options) = $this->readOption($key, $script);
                        if(!$this->excluded($options)) {
                                $this->addStyle($doc, $url);
                        }
                }
        }

        /**
         *
         * Will globally add a className to an element. 
         *
         * If $id is precided with an '#' will be considered to be an id 
         * else it will be a normal element. (e.g. body)
         *
         */
        private function addAttribTo($doc, $tag, $attr, $value) 
        {
                if(substr($tag,0,1) == '#') {
                        $el = $this->getElementById($doc, substr($tag, 1));
                } else {
                        $el =  $doc->getElementsByTagname($tag)->item(0);
                }
                if($el) {
                        if($attr == 'class') {
                                $classes = array();
                                $classString = $el->getAttribute('class');
                                if($classString != "") $classes = explode(' ', $classString);
                                if(!in_array($className, $classes)) $classes[] = $className;
                                $el->setAttribute("class", implode(' ', $classes));
                        } else {
                                $el->setAttribute($attr, $value);
                        }

                } else {
                        throw new Exception("Element not found");
                }
        }

        /**
         * @param DOMDocument $doc The DomDocument
         * @param DOMNode $newnode The new node to append after the title
         * @return void
         */
        private function insertAfterTitle(DOMDocument $doc, DOMNode $newnode)
        {
                $title = $doc->getElementsByTagName('title')->item(0);
                $this->addSibling($doc, $newnode, $title);
        } 

        /**
         * @param DOMDocument $doc The DomDocument
         * @param DOMNode $newnode The new node
         * @param DOMNode $node The node to append to
         * @return void
         */
        private function addSibling(DOMDocument $doc, DOMNode $newnode, DOMNode $node)
        {
                if ($node->nextSibling) {
                        return $node->parentNode->insertBefore($newnode, $node->nextSibling);
                } else {
                        return $node->parentNode->appendChild($newnode);
                }
        }

        /**
         * @param DOMDocument $doc The DomDocument
         * @param DOMNode $newnode The new node
         * @param DOMNode $node The node to prepend after
         * @return void
         */
        private function prependChild(DOMDocument $doc, DOMNode $newnode, DOMNode $node)
        {
                if ($node->firstChild) {
                        return $node->insertBefore($newnode, $node->firstChild);
                } else {
                        return $node->appendChild($newnode);
                }
        }


        /**
         * @param DOMDocument $doc The DomDocument
         * @param String $href The url
         * @param String $media The media type
         * @return void
         */
        private function addStyle(DOMDocument $doc, $href, $media = 'screen')
        {
                $base = $doc->createElement('link');
                $base->setAttribute('rel', 'stylesheet');
                $base->setAttribute('type', 'text/css');
                $base->setAttribute('media', $media);
                $base->setAttribute('href', $href);
                $this->insertAfterTitle($doc, $base);
        }

        private function _load() {

                $dir_iterator = new RecursiveDirectoryIterator("pages");
                $pages = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);

                foreach($pages as $page) {
                        if($page->isFile() && substr($page->getfileName(), -5) == '.html') {
                                $d = new DOMDocument();
                                $d->validateOnParse = true;
                                $d->preserveWhiteSpace = false;
                                $d->load($page->getPathName());

                                $title       = $this->getTagValue($d, 'title');
                                $description = $this->getMeta($d, 'description');

                                if(isset($this->pages["$title"])) {
                                        throw new Exception("Duplicate document title");
                                }
                                $dir = dirname(substr($page->getPathName(), 6));
                                $dir = $dir == '.' ? '' : "$dir/";
                                $path =  $dir . $this->urlify($title);
                                $this->pages[$path] = array(
                                                'title' => $title,
                                                'description' => $description,
                                                'path' => $path,
                                                'document' => $d
                                                );

                        }
                }

                $k = array_keys($this->pages);

                $this->url = isset($_REQUEST['url']) ? $_REQUEST['url'] : $k[0];
        }

        private function urlify($title)
        {
                return preg_replace("/\W+/", "-", $title);
        }

        private function getTagValue($d, $tagName)
        {
                if($title = $d->getElementsByTagName($tagName)->item(0)->firstChild->wholeText) {
                        return $title; 
                } else {
                        throw new Exception("No $tagName title");
                }

        }

        /**
         *
         * Add charset the HTML5 way
         *
         */
        private function addEncoding($d)
        {
                $encoding = isset($this->config['LOCALE']) ? $this->config['LOCALE'] : 'UTF-8';
                $meta = $d->createElement('meta');
                $meta->setAttribute('charset', $encoding);
                $this->insertAfterTitle($d, $meta);
        }

        private function getMeta($d, $name)
        {
                $metas = $d->getElementsByTagName('meta');
                foreach($metas as $meta) {
                        if($meta->getAttribute('name') == $name && $meta->hasAttribute('content')) {
                                return $meta->getAttribute('content'); 
                        }
                }
                return false;
        }

        /**
         *
         * Weird domDocument->getElementById() doesn't seem to work
         *
         */
        private function getElementById($doc, $id)
        {
                $xpath = new DOMXPath($doc);
                return $xpath->query("//*[@id='$id']")->item(0);
        }

        /**
         *
         * Options kan be a string or array
         *
         * If they are an array the options has some config attached to it.
         *
         * This function will always return an array with two values
         *
         * Meant to be read with list($name, $values) = $this->readOption($option);
         *
         */
        private function readOption($key, $value)
        {
                if(is_numeric($key)) {
                        return array($value, array());
                } else {
                        return array($key, $value);
                }
        }

        /**
         *
         *  Check if an option array contains the current url as excluded.
         *
         */
        private function excluded($o)
        {
                if(isset($o['only'])) {
                       $only  = is_array($o['only']) ? $o['only'] : array($o['only']); 
                        return !in_array($this->url, $only);
                }

                if(isset($o['except'])) {
                       $ex = is_array($o['except']) ? $o['except'] : array($o['except']); 
                        return in_array($this->url, $ex);
                }

                return false;
        }

}

?>
