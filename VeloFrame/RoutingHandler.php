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
                http_response_code(404);
                return "404";
            }
            $errorResponse = $this->handlers["error"]->handleGet([
                "error" => 404,
                "message" => "Not Found",
                "exception" => new HTTPException("Not Found", 404, true)
            ]);
            if ($errorResponse instanceof HTTPResponse) {
                http_response_code($errorResponse->statusCode);
                foreach ($errorResponse->headers as $k => $v) {
                    header("$k: $v");
                }
                return $errorResponse->content;
            }
            http_response_code(404);
            return $errorResponse;
        }

        try {
            $handler = $this->handlers[$uri];
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $response = $handler->handlePost($_POST);
            } else {
                $response = $handler->handleGet($_GET);
            }
            if ($response instanceof HTTPResponse) {
                http_response_code($response->statusCode);
                foreach ($response->headers as $k => $v) {
                    header("$k: $v");
                }
                return $response->content;
            }
            // If string, treat as 200 OK
            http_response_code(200);
            return $response;
        } catch (HTTPException $e) {
            http_response_code($e->getStatusCode());
            if ($e->shouldUseErrorHook() && isset($this->handlers["error"])) {
                $errorData = [
                    "error" => $e->getStatusCode(),
                    "message" => $e->getMessage(),
                    "exception" => $e
                ];
                $result = $this->handlers["error"]->handleGet($errorData);
                if ($result instanceof HTTPResponse) {
                    http_response_code($result->statusCode);
                    foreach ($result->headers as $k => $v) {
                        header("$k: $v");
                    }
                    return $result->content;
                }
                return $result;
            } else {
                return $e->getMessage();
            }
        }
    }

}
