<?php
/**
 * RoutingHandler.php
 * 
 * Handles automatic selection of registered RequestHandlers.
 * 
 * @author Patrick Matthias Garske <patrick@garske.link>
 * @since 0.1
 */

namespace VeloFrame;

class RoutingHandler {

    /** @var array<RequestHandler> $handlers */
    private array $handlers;
    
    /**
     * Register a new RequestHandler with a specific URI
     *
     * @param  string $uri
     * @param  RequestHandler $handler
     * @return void
     */
    public function register(string $uri, RequestHandler $handler) {
        if(isset($this->handlers[$uri]))
            throw new \Exception("Handler already registered for this URI.");
        $this->handlers[$uri] = $handler;
    }
    
    /**
     * Automatically select the correct previously registered RequestHandler to process a request for a given URI and return the rendered HTML string
     *
     * @param  string $uri
     * @return string
     */
    public function handle(string $uri) {

        if(!isset($this->handlers[$uri])) {
            if(!isset($this->handlers["error"])) {
                return "404";
            }
            return $this->handlers["error"]->handleGet([
                "error" => 404,
                "message" => "Not Found",
                "exception" => new HTTPException("Not Found", 404, true, true)
            ]);
        }

        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                return $this->handlers[$uri]->handlePost($_POST);
            }
            return $this->handlers[$uri]->handleGet($_GET);
        } catch (HTTPException $e) {
            http_response_code($e->getStatusCode());
            if ($e->shouldUseErrorHook() && isset($this->handlers["error"])) {
                // Pass the exception to the error handler, include message and code
                $errorData = [
                    "error" => $e->getStatusCode(),
                    "message" => $e->getMessage(),
                    "exception" => $e
                ];
                $result = $this->handlers["error"]->handleGet($errorData);
                if ($e->isTerminal()) {
                    return $result;
                }
                // If not terminal, continue processing (could append or log, here just return result for now)
                return $result;
            } else {
                // No error hook or not using it, just return the message
                if ($e->isTerminal()) {
                    return $e->getMessage();
                }
                // If not terminal, continue processing (could append or log, here just return message for now)
                return $e->getMessage();
            }
        }
    }

}
