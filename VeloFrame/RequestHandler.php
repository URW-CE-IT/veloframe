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

namespace VeloFrame;

interface RequestHandler
{    
    /**
     * Handle a GET Request and return the rendered HTML as a string
     *
     * @param  array<string> $params
     * @return string
     */
    public function handleGet(array $params): string;

    /**
     * Handle a GET Request and return the rendered HTML as a string
     *
     * @param  array<string> $params
     * @return string
     */
    public function handlePost(array $params): string;
}