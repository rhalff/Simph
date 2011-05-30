<?php
/**
*
* Simph Widget Class
*
* It's actually a template class, but let's call it widgets :-)
*
* All it provides are some minimal values from the 'system'
* The rest of the magic inside the included (widget) php file is up to you.
*
*/
class SimphWidget{

        public $vars = array();
        private $ret = ""; 

        function __construct($widget, $vars)
        {
                foreach($vars as $var=>$value) { $this->$var = $value; }
                $file = strtolower("widgets/$widget.html.php");
                if(file_exists($file)) {
                        ob_start();
                        include $file;
                        $this->ret = ob_get_contents();
                        ob_end_clean();
                } else {
                        throw new Exception(sprintf("Widget file not found: %s", $file));
                }
        }

        function __set($name, $value)
        {
                $this->$name = $value;
        }

        function __get($name)
        {
                return $this->$name;
        }

        function __toString()
        {
                return $this->ret;
        }

}

?>
