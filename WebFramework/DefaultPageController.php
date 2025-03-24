<?php
/**
 * DefaultPageController.php
 * 
 * Default base implementation of a Page Controller. Fully implements RequestHandler. New Page Handlers should extend this class.
 * 
 * @author Patrick Matthias Garske <patrick@garske.link>
 * @since 0.1
 */

namespace WebFramework;

class DefaultPageController implements RequestHandler {

    public function handleGet($params): string {
        return "Unhandled";
    }

    public function handlePost($params): string {
        return "Unhandled";
    }

}