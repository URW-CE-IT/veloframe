<?php
/**
 * DefaultPageController.php
 * 
 * Default base implementation of a Page Controller. Fully implements RequestHandler. New Page Handlers should extend this class.
 * 
 * @author Patrick Matthias Garske <patrick@garske.link>
 * @since 0.1
 */

namespace VeloFrame;

class DefaultPageController implements RequestHandler {
    
    /**
     * Handle a GET Request and return the rendered HTML as a string or HTTPResponse
     *
     * @param  array<string> $params
     * @return string|HTTPResponse
     */
    public function handleGet(array $params) {
        return "Unhandled";
    }

    /**
     * Handle a POST Request and return the rendered HTML as a string or HTTPResponse
     *
     * @param  array<string> $params
     * @return string|HTTPResponse
     */
    public function handlePost(array $params) {
        return "Unhandled";
    }

}