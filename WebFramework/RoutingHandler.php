<?php
/**
 * RoutingHandler.php
 * 
 * Handles automatic selection of registered RequestHandlers.
 * 
 * @author Patrick Matthias Garske <patrick@garske.link>
 * @since 0.1
 */

namespace WebFramework;

class RoutingHandler {

    private array $handlers;

    public function register($uri, RequestHandler $handler) {
        if(isset($this->handlers[$uri]))
            throw new Exception("Handler already registered for this URI.");
        $this->handlers[$uri] = $handler;
    }

    public function handle($uri) {

        if(!isset($this->handlers[$uri])) {
            if(!isset($this->handlers["error"])) {
                return "404";
            }
            $this->handlers["error"]->handleGet(array("error" => 404));
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->handlers[$uri]->handlePost($_POST);
        }
        return $this->handlers[$uri]->handleGet($_GET);

    }

}