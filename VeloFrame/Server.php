<?php
/**
 * Server.php
 * 
 * Handles routing, serves registered routes and optionally auto-discoveres handlers
 * 
 * @author Patrick Matthias Garske <patrick@garske.link>
 * @since 0.1
 */

namespace VeloFrame;

class Server {

    private string $proj_dir;
    private RoutingHandler $rh;
    private bool $discovery_ran = false;

    public function __construct(string $proj_dir = NULL) {
        if(is_null($proj_dir)) {
            $this->proj_dir = pathinfo(__DIR__, PATHINFO_DIRNAME);
        }
        $GLOBALS["WF_PROJ_DIR"] = $this->proj_dir;
    }
    
    /**
     * Automatically discover and register new Page Controllers
     *
     * @param  RoutingHandler $rh
     * @return void
     */
    public function discoverPages() {
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
     * Get the Routing Handler attached to the Server. Will return NULL if no RoutingHandler has been attached yet.
     *
     * @return RoutingHandler
     */
    public function getRoutingHandler(): RoutingHandler {
        return $this->rh;
    }
    
    /**
     * Attach a new RoutingHandler to the Server. Will run discoverPages if it hasnt run before.
     *
     * @param  RoutingHandler $rh
     * @return void
     */
    public function setRoutingHandler(RoutingHandler $rh) {
        $this->rh = $rh;
        if(!$this->discovery_ran) {
            $this->discovery_ran = true;
            $this->discoverPages();
        }
    }
    
    /**
     * Serve the Request
     *
     * @return void
     */
    public function serve() {
        $path = "index";

        if(defined("SERVICE_ATTRIB") && SERVICE_ATTRIB)
            header('X-Powered-By: URW-CE-IT/VeloFrame');

        if(php_sapi_name() == 'cli-server') {
            $path = parse_url(substr($_SERVER['REQUEST_URI'], 1), PHP_URL_PATH);
            if(strlen($path) == 0) $path = "index";
        }

        if(isset($_GET["rpath"])){
            $path = $_GET["rpath"];
        } 
        
        // Check if an image is requested and serve using Optimizer
        if(preg_match('/.(png|jpe?g|gif|svg|ico|webp)$/', $path)) {
            echo Optimizer::serveOptimizedImage($this->proj_dir."/".$path);
            return;
        }
        // Check if a JS file is requested and serve it using Optimizer
        if(preg_match('/.(js)$/', $path)) {
            echo Optimizer::serveOptimizedJS($this->proj_dir."/".$path);
            return;
        }
        // Check if a CSS file is requested and serve it using Optimizer
        if(preg_match('/.(css)$/', $path)) {
            echo Optimizer::serveOptimizedCSS($this->proj_dir."/".$path);
            return;
        }

        echo $this->rh->handle($path);
    }

}