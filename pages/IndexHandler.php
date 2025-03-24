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

        $test1_val = Input::sanitize("test1", INPUT_TYPE_STRING, "Please set the GET Parameter test1 to see the value here.");
        $index_tpl->setVariable("get_test1", $test1_val);

        $test2_val = Input::sanitize("test2", INPUT_TYPE_FLOAT, "Please set the GET Parameter test2 to see the value here. test2 accepts only numeric inputs (sanitized).");
        $index_tpl->setVariable("get_test2", $test2_val);

        $index_tpl->setVariable("page", "Index");
        return $index_tpl->output();
    }

}