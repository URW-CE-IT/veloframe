<?php
namespace WebFramework;

class Server {

    private string $proj_dir;
    private RoutingHandler $rh;

    public function __construct(string $proj_dir = NULL) {
        if(is_null($proj_dir)) {
            $this->proj_dir = pathinfo($_SERVER['SCRIPT_FILENAME'], PATHINFO_DIRNAME);
        }
        $GLOBALS["WF_PROJ_DIR"] = $this->proj_dir;
    }
    
    /**
     * Attach a RoutingHandler to manage Pages - Will automatically search for pages in Project Root if AUTO_FIND_PAGES is TRUE.
     *
     * @param  RoutingHandler $rh
     * @return void
     */
    public function attachRoutingHandler(RoutingHandler $rh) {
        $this->rh = $rh;

        if(defined("AUTO_FIND_PAGES") && AUTO_FIND_PAGES) {
            if(!is_dir($this->proj_dir . "/pages")) {
                print_debug("AUTO_FIND_PAGES failed, as no 'pages' directory could be found in project root. Please try setting the project root manually and check your project structure.", 1);
                return;
            }

            $pages_dir = new \RecursiveDirectoryIterator($this->proj_dir . "/pages");
            $filter = new \RecursiveCallbackFilterIterator($pages_dir, function($current, $key, $iterator) {
                if($current->getFilename()[0] === ".") {
                    return FALSE;
                }
                if(!str_ends_with($current->getFilename(),".php")){
                    return FALSE;
                }
                return TRUE;
            });
            $iterator = new \RecursiveIteratorIterator($filter);
            foreach($iterator as $file) {
                $match_uris = array();
                $fres = fopen($file, "r");
                if(!$fres) {
                    print_debug("AUTO_FIND_PAGES failed, as read-access to $file has failed! Please check your filesystem permissions.");
                    return;
                }
                $esig = false;
                while(($line = fgets($fres)) !== false && !$esig) {

                    if(substr($line, 0, 8) == "# @route") {
                        #Found route annotation, append route to match_uris array
                        $uri = trim(substr($line, 9));
                        array_push($match_uris, $uri);
                        continue;
                    }
                    if(substr($line, 0, 5) == "class") {
                        #Found class declaration - check if extends DefaultPageController or implements RequestHandler and require + register URIs
                        $matches = array();
                        $pattern = '/class\s+(\w+)\s+(?:(?:extends\s+(?:[A-Za-z_]\w*\\\\)*DefaultPageController)|(?:implements\s+(?:[A-Za-z_]\w*\\\\)*RequestHandler))\s*\{/';
                        if(preg_match($pattern, $line, $matches)){
                            require_once($file);
                            $class_name = $matches[1];
                            $object = new $class_name;

                            foreach($match_uris as $uri) {
                                $this->rh->register($uri, $object);
                            }
                            
                            $esig = true;
                        }
                    }
                }
            }
        }
    }
    
    /**
     * Serve the Request
     *
     * @return void
     */
    public function serve() {
        $path = "index";

        if(php_sapi_name() == 'cli-server') {
            $path = parse_url($path, PHP_URL_PATH);
            if(is_file($this->proj_dir."/".$path)) {
                if(preg_match('/.(js|css|png|jpe?g|gif|svg|ico|webp|woff2?|ttf|otf|eot|mp4|mp3|wav|avi|mov|pdf|zip|rar|md)$/', $path)) {
                    echo file_get_contents($this->proj_dir."/".$path);
                    return;
                }
            }   
            if(strlen($path) == 0) $path = "index";
        }

        if(isset($_GET["rpath"])){
            $path = $_GET["rpath"];
        }
        
        echo $this->rh->handle($path);
    }

}