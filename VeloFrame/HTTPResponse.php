<?php
/**
 * HTTPResponse.php
 * 
 * Represents an HTTP response with status code, content, and headers.
 * 
 * @author Patrick Matthias Garske <patrick@garske.link>
 * @since 0.5
 */

namespace VeloFrame;

class HTTPResponse {
    public int $statusCode;
    public string $content;
    public array $headers;

    public function __construct(string $content, int $statusCode = 200, array $headers = []) {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }
}
