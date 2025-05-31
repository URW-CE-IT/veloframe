<?php

/**
 * IndexHandler.php
 * 
 * Example Index Page Handler
 * 
 * @author Patrick Matthias Garske <patrick@garske.link>
 * @since 0.1
 */

use VeloFrame as WF;

# @route index
class IndexHandler extends WF\DefaultPageController {

    public function handleGet(array $params): string {
        try{
            $index_tpl = new WF\Template("index");
        } catch (Exception $e) {
            return "ERROR! File could not be loaded.";
        }
        $index_tpl->includeTemplate("head", new WF\Template("std_head"));
        $index_tpl->includeTemplate("js_deps", new WF\Template("js_deps"));

        $test1_val = WF\Input::sanitize("test1", INPUT_TYPE_STRING, "Please set the GET Parameter test1 to see the value here.");
        $index_tpl->setVariable("get_test1", $test1_val);

        $test2_val = WF\Input::sanitize("test2", INPUT_TYPE_FLOAT, "Please set the GET Parameter test2 to see the value here. test2 accepts only numeric inputs (sanitized).");
        $index_tpl->setVariable("get_test2", $test2_val);

        $index_tpl->setVariable("page", "Index");
        return $index_tpl->output();
    }

}