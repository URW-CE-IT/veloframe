<?php

/**
 * IndexHandler.php
 * 
 * Example Index Page Handler
 * 
 * @author Patrick Matthias Garske <patrick@garske.link>
 * @since 0.1
 */

class IndexHandler extends DefaultPageController {

    public function handleGet($params): string {
        $index_tpl = new Template("index");
        $index_tpl->includeTemplate("head", new Template("std_head"));
        $index_tpl->includeTemplate("js_deps", new Template("js_deps"));
        $index_tpl->includeTemplate("navbar", new Template("navbar"));

        $index_tpl->setVariable("page", "Index");
        $index_tpl->setVariable("data", print_r($_SERVER, true));
        return $index_tpl->output();
    }

}