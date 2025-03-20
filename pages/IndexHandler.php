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

        $index_tpl->setComponent("cardrow", new TemplateComponent("three_card_row", "Card Row Argument 1", "Card Row Argument 2", "Card Row Argument 3"));

        $index_tpl->setVariable("page", "Index");
        return $index_tpl->output();
    }

}