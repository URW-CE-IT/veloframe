<?php
/**
 * HTTPException.php
 * 
 * Custom Exception for HTTP error handling with status codes.
 * 
 * @author Patrick Matthias Garske <patrick@garske.link>
 * @since 0.5
 */

namespace VeloFrame;

class HTTPException extends \Exception {
    protected int $statusCode;
    protected bool $useErrorHook;

    /**
     * @param string $message
     * @param int $statusCode
     * @param bool $useErrorHook Should the error be handled by the registered error hook?
     */
    public function __construct(string $message = "", int $statusCode = 500, bool $useErrorHook = true) {
        parent::__construct($message, $statusCode);
        $this->statusCode = $statusCode;
        $this->useErrorHook = $useErrorHook;
    }

    public function getStatusCode(): int {
        return $this->statusCode;
    }

    public function shouldUseErrorHook(): bool {
        return $this->useErrorHook;
    }
}
