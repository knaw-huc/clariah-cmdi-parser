<?php
define('PROFILE_PATH', dirname(__FILE__) . '/data/profiles/');
define('RECORD_DIR',dirname(__FILE__) . '/data/records/inprogress/');
define('TWEAK_DIR', dirname(__FILE__) . '/data/tweaks/');
define('TWEAKER', dirname(__FILE__) . '/tweaker/mergeTweak.xsl');
define('BASE_URL', "Go for it.");

require(dirname(__FILE__) . '/classes/CcfParser.class.php');

if (isset($_GET["profile"])) {
    $file = PROFILE_PATH . $_GET["profile"] . '.xml';
    if (isset($_GET["record"])) {
        $record = RECORD_DIR . $_GET["record"];
        if (!file_exists($record)) {
            $record = null;
        }
    } else {
        $record = null;
    }
    if (file_exists($file)) {
       get_struc($file, $record);
    } else {
        throw_error("Profile does not exists!");
    }

} else {
    throw_error();
}

function get_struc($file, $record) {
    $parser = new Ccfparser();
    $tweakFile =  TWEAK_DIR . $_GET["profile"] . 'Tweak.xml';

    if (file_exists($tweakFile)) {
        $struc = $parser->parseTweak($file, $tweakFile, TWEAKER, $record);
    } else {
        $struc = $parser->parseTweak($file);
    }
    send_json($struc);
}


function send_json($struc)
{
    header('Content-Type: application/json; charset=UTF-8');
    header('Access-Control-Allow-Origin: *');
    echo $struc;
}

function throw_error($error = "Bad request")
{
    $response = json_encode(array("error" => $error));
    send_json($response);
}
