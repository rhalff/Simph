<?php
/**
 *
 * Simph
 *
 * Features:
 *
 *  - Full SEO!
 *  - Add your own keywords to every page
 *  - Ability to give everypage a full description
 *
 */
require_once 'lib/sp.widget.class.php';

class Simph {

        private $pages = array();
        private $url = '';
        private $config = array(
                        'javascripts'=>array()
                        );

        function __construct($config = false)
        {
                if($config) $this->config = $config;
                $this->_load();
        }

        function run()
        {

                if(!isset($this->pages[$this->url])) {
                        throw new Exception("Page not found");
                }
                $doc = $this->pages[$this->url]['document'];

                $this->addJavascripts($doc, $this->config['javascripts']);
                $this->addStylesheets($doc, $this->config['stylesheets']);
                $this->addBase($doc);
                $this->addEncoding($doc);
                $this->addClassTo($doc, 'body', $this->urlify($this->url));


                // export desired system vars to the widgets
                $vars = array(
                                'url'=>$this->url,
                                'pages'=>$this->pages
                             );

                // insert the tag
                foreach($this->config['widgets'] as $location=>$widget) {
                        foreach($widget as $name=>$value) {
                                list($name, $options) = $this->readOption($name, $value);
                                if(!$this->excluded($options)) {
                                        $this->attachWidget($doc, $location, $name, $options);
                                }
                        }
                }

                $content = $doc->saveXML();

                preg_match_all("/{(\w+)}/", $content, $matches);
                $tags = !empty($matches) ? $matches[1] : array();
                $s = $r = array();

                // 2 ways, add with a tag {MENU}
                // or automatic addition based on what is specified in the config
                foreach($tags as $widget) {
                        $r[] = new SimphWidget($widget, $vars);
                        $s[] = '{'.strtoupper($widget).'}';
                }


                print(str_replace($s, $r, $doc->saveXML()));
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
                        $this->addSibling($doc, $doc->createTextNode('{'.strtoupper($name).'}'), $el);
                } else {
                        // append is the default
                        $el->appendChild($doc->createTextNode('{'.strtoupper($name).'}'));
                }
        }

        private function addBase($doc)
        {
                $base = $doc->createElement('base');
                $base->setAttribute('href', "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/');
                $this->insertAfterTitle($doc, $base);
        }

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

        private function addClassTo($doc, $id, $className) 
        {
                if(substr($id,0,1) == '#') {
                        $tag = $this->getElementById($doc, substr($id, 1));
                } else {
                        $tag =  $doc->getElementsByTagname($id)->item(0);
                }
                if($tag) {
                        $classes = explode(' ', $tag->getAttribute('class'));
                        if(!in_array($className, $classes)) $classes[] = $className;
                        $tag->setAttribute("class", implode($classes, ' '));
                } else {
                        throw new Exception("Tag not found");
                }
        }

        /**
         * @param DOMNode $newnode Node to insert next to $ref
         * @param DOMNode $ref Reference node
         * @requires $ref has a parent node
         * @return DOMNode the real node inserted
         */
        private function insertAfterTitle($doc, DOMNode $newnode)
        {
                $title = $doc->getElementsByTagName('title')->item(0);
                $this->addSibling($doc, $newnode, $title);
        } 

        private function addSibling($doc, $newnode, $node)
        {
                if ($node->nextSibling) {
                        return $node->parentNode->insertBefore($newnode, $node->nextSibling);
                } else {
                        return $node->parentNode->appendChild($newnode);
                }
        }

        private function addStyle($doc, $href, $media = 'screen')
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

        function urlify($title)
        {
                return preg_replace("/\W+/", "-", $title);
        }

        function getTagValue($d, $tagName)
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
        function addEncoding($d)
        {
                $encoding = isset($this->config['LOCALE']) ? $this->config['LOCALE'] : 'UTF-8';
                $meta = $d->createElement('meta');
                $meta->setAttribute('charset', $encoding);
                $this->insertAfterTitle($d, $meta);
        }

        function getMeta($d, $name)
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
        function getElementById($doc, $id)
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
