<?php
/**
 * RequestHandler.php
 * 
 * Basic RequestHandler Interface to be implemented by DefaultPageController.
 * A new Page Controller / Page Handler should try to extend DefaultPageController instead of implementing this interface.
 * 
 * @author Patrick Matthias Garske <patrick@garske.link>
 * @since 0.1
 */

namespace WebFramework;

interface RequestHandler
{
    public function handleGet($params): string;
    public function handlePost($params): string;
}