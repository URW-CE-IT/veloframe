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

    public function handleGet(array $params) {
        session_start();
        $start_time = microtime(true);
        try{
            $index_tpl = new WF\Template("index");
        } catch (Exception $e) {
            return "ERROR! File could not be loaded.";
        }
        $index_tpl->includeTemplate("head", new WF\Template("std_head"));
        $index_tpl->includeTemplate("js_deps", new WF\Template("js_deps"));
        $test1_val = WF\Input::sanitize("test1", INPUT_TYPE_STRING, NULL);
        $index_tpl->setVariable("get_test1", $test1_val);

        $index_tpl->setVariable("show_image", true); // Image will only be shown if this variable is set to true

        $index_tpl->setVariable("template_time", $_SESSION["template_time"] / ($_SESSION["drawn_templates"] ?? 1));

        $test2_val = WF\Input::sanitize("test2", INPUT_TYPE_FLOAT, "Please set the GET Parameter test2 to see the value here. test2 accepts only numeric inputs (sanitized).");
        $index_tpl->setVariable("get_test2", $test2_val);

        $index_tpl->setVariable("page", "Index");
        $output = new WF\HTTPResponse($index_tpl->output(),200, [
            "Content-Type" => "text/html; charset=utf-8",
            "Cache-Control" => "no-cache, no-store, must-revalidate",
            "Pragma" => "no-cache",
            "Expires" => "0"
        ]);
        $end_time = microtime(true);
        //Calculate average template time
        $template_time = $end_time - $start_time;
        $_SESSION["drawn_templates"] = ($_SESSION["drawn_templates"] ?? 0) + 1;
        $_SESSION["template_time"] = $_SESSION["template_time"] + $template_time;


        return $output;
    }

}