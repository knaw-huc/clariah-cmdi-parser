<?php
if (!defined('BASE_URL'))
    exit('No direct script access allowed');

class Ccfrecord {
    var $resources = array();
    
    function createComponents($inputComponents, $profileID, $cmdiRecord, $resourcePath, $uploadPath) {
        $date = date("Y-m-d");
        $headerItems = $cmdiRecord->getElementsByTagNameNS('http://www.clarin.eu/cmd/1', 'Header')->item(0);
        
        foreach ($headerItems->childNodes as $item){
            switch ($item->tagName){
                case "cmd:MdCreationDate":
                    $item->nodeValue = $date;
                    break;
                case "cmd:MdProfile":
                    $item->nodeValue = $profileID;
                    break;
            }
        }
        $nodes = $cmdiRecord->getElementsByTagNameNS('http://www.clarin.eu/cmd/1', 'Components');
        $node = $nodes->item(0);
        $this->processChildren($inputComponents, $node, $cmdiRecord);
        //$node->appendChild($newNodes);
        //if (count($this->resources)){
            $this->processResources($cmdiRecord, $resourcePath, $uploadPath);
        //}
        return $cmdiRecord;
    }
    
    private function processChildren($arrayParts, $node, $cmdi) {
        usort($arrayParts, array(__CLASS__, 'cmp'));
        foreach ($arrayParts as $part){
            if ($part["type"] == 'component'){
                $newNode = $cmdi->createElement('cmd:' . $part["name"], NULL);
                if (isset($part["file"])){
                    $nr = count($this->resources) + 1;
                    $this->resources[] = array("id" => "R$nr", "file" => $part["file"]);
                    $newNode->setAttribute("ref", "R$nr");
                }
                $this->processChildren($part["content"], $newNode, $cmdi);
                if ($newNode->hasChildNodes()){
                    $node->appendChild($newNode);
                }
                
            } else {
                if ($part["type"] == "element"){
                    $this->processElements($part["name"], $part["content"], $node, $cmdi);
                }
            }
        }
    }
    static function cmp($a, $b) {
         if ($a["sortOrder"] == $b["sortOrder"])
        {
            return 0;
        }else{
           return ($a["sortOrder"] < $b["sortOrder"]) ? -1 : 1;
        }
        //return strcmp($a["sortOrder"], $b["sortOrder"]);
    }
    
    
    
    private function processElements($name, $items, $node, $cmdi){
        foreach ($items as $item){
            $newNode = $cmdi->createElement('cmd:' . $name, $item["value"]);
            if (count($item["attributes"])){
                $attributes = $item["attributes"];
                foreach ($attributes as $key => $attribute){
                    if ($key == "lang"){
                        $newNode->setAttribute("xml:lang", $attribute);
                    }
                }
            }
            $node->appendChild($newNode);
        }
    }
    
    private function processResources($cmdi, $resourcePath, $uploadPath){
        $nodes = $cmdi->getElementsByTagNameNS('http://www.clarin.eu/cmd/1', 'Resources');
        $node = $nodes->item(0);
        $newNode = $cmdi->createElement('cmd:ResourceProxyList', NULL);
        for ($i = 0; $i < count($this->resources); $i++){
            $proxyNode = $cmdi->createElement('cmd:ResourceProxy', NULL);
            $proxyNode->setAttribute("id", $this->resources[$i]["id"]);
            $type = $cmdi->createElement('cmd:ResourceType', "Resource");
            $proxyNode->appendChild($type);
            $ref = $cmdi->createElement('cmd:ResourceRef', "../resources/" . $this->resources[$i]["file"]);
            $proxyNode->appendChild($ref);
            $newNode->appendChild($proxyNode);
            //error_log($resourcePath);
            //error_log($uploadPath);
            $this->moveResource($this->resources[$i]["file"], $resourcePath, $uploadPath);
        }
        $node->appendChild($newNode);
        $newNode = $cmdi->createElement('cmd:JournalFileProxyList', NULL);
        $node->appendChild($newNode);
        $newNode = $cmdi->createElement('cmd:ResourceRelationList', NULL);
        $node->appendChild($newNode);
        $newNode = $cmdi->createElement('cmd:IsPartOfList', NULL);
        $node->appendChild($newNode);
    }
    
    private function moveResource($file, $resourcePath, $uploadPath){
        if (file_exists($uploadPath . $file)){
                rename($uploadPath . $file, $resourcePath . $file);
        }
        
    }
}