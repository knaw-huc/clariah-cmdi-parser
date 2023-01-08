<?php

if (!defined('BASE_URL'))
    exit('No direct script access allowed');

class Ccfparser {

    function cmdi2json($cmdifile, $typeFile = 1) {

        switch ($typeFile) {
            case PARSER_PROFILE:
                $xml = simplexml_load_string($cmdifile);
                break;
            case PARSER_METADATA:
                $xml = simplexml_load_string($cmdifile, "SimpleXMLElement", '0', 'cmd', true);
                break;
            default:
                $xml = array();
        }
        return json_encode($xml);
    }

    function xml2jason($cmdifile) {
        $xml = simplexml_load_string($cmdifile);
    }

    function parseTweak($cmdifile, $tweakfile = null, $tweaker = null, $record = null) {
        $jsonArray = array();

        if (is_null($tweakfile)) {
            $xml = new DOMDocument();
            $xml->preserveWhiteSpace = false;
            $xml->load($cmdifile);
        } else {
            $xml = $this->_tweak($cmdifile, $tweakfile, $tweaker);
        }


        $id = $xml->documentElement->getElementsByTagName('ID')->item(0);
        $jsonArray["id"] = $id->nodeValue;
        $jsonArray["content"] = $this->_createFormStruc($xml->documentElement);
        if (!is_null($record)) {
            $recArray = $this->_createRecordArray($record);
            $jsonArray["record"] = $recArray;
        }
        return json_encode($jsonArray);
    }

    /* Private functions */

    private function _tweak($cmdifile, $tweakfile, $tweaker) {
        $error_level = error_reporting();
        error_reporting(0);
        $profile = new DOMDocument();
        $profile->preserveWhiteSpace = false;
        $profile->load($cmdifile);

        $xsl = new DOMDocument;
        $xsl->preserveWhiteSpace = false;
        $xsl->load($tweaker);

        $proc = new XSLTProcessor;
        $proc->importStyleSheet($xsl);

        $params = array('tweakFile' => $tweakfile);
        $proc->setParameter('', $params);
        $retXML = $proc->transformToDoc($profile);

        error_reporting($error_level);
        return $retXML;
    }

    private function _createFormStruc($xml, $level = 0) {
        $i = 0;
        $retArray = array();
        $level++;
        $valueArray = array();
        foreach ($xml->childNodes as $node) {
            $nodeArray = array();

            switch ($node->nodeName) {
                case 'Component':
                    $i++;
                    $nodeArray["type"] = "Component";
                    $nodeArray["level"] = $level;
                    $nodeArray["ID"] = uniqid();
                    $nodeArray["attributes"] = $this->_processComponent($node, $level, $i);
                    $nodeArray["content"] = $this->_displaySort($this->_createFormStruc($node, $level));
                    $retArray[] = $nodeArray;
                    break;
                case 'Element':
                    $i++;
                    $nodeArray["type"] = "Element";
                    $nodeArray["level"] = $level;
                    $nodeArray["ID"] = uniqid();
                    $nodeArray["attributes"] = $this->_processElement($node, $level, $i);
                    $retArray[] = $nodeArray;
                    break;
            }
        }
        return $retArray;
    }

    private function _createRecordArray($record) {
        $resources = array();
        $cmdiRec = new DOMDocument();
        $cmdiRec->load($record);
        $xpath = new DOMXPath($cmdiRec);
        $nodes = $xpath->query("/cmd:CMD/cmd:Resources/cmd:ResourceProxyList/cmd:ResourceProxy");
        foreach ($nodes as $node) {
            
            $resources[] = array("ref" => $node->getAttribute('id'), "file" => basename($node->childNodes->item(1)->nodeValue));
        }
        $xml = $cmdiRec->documentElement;

        $retArray = array();
        $retArray = $this->_parseRecord($xml, $resources);
        return $retArray;
    }

    private function _hasChild($p) {
        if ($p->hasChildNodes()) {
          foreach ($p->childNodes as $c) {
           if ($c->nodeType == XML_ELEMENT_NODE)
            return true;
          }
        }
        return false;
    }

    private function _parseRecord($xml, $resources) {
        $retArray = array();
        foreach ($xml->childNodes as $node) {
            $name = $node->localName;
            if ($node->hasChildNodes()) {
                if (!$this->_hasChild($node)) {
                    $attributes = $this->_getValueAttributes($node, $resources);
                    if ($attributes) {
                        $retArray[] = array("name" => $name, "type" => "element", "value" => $node->nodeValue, "attributes" => $attributes);
                    } else {
                        $retArray[] = array("name" => $name, "type" => "element", "value" => $node->nodeValue);
                    }
                } else {
                    $attributes = $this->_getValueAttributes($node, $resources);
                    if ($attributes) {
                        $retArray[] = array("name" => $name, "type" => "component",  "attributes" => $attributes, "value" => $this->_parseRecord($node, $resources));
                    } else {
                        $retArray[] = array("name" => $name, "type" => "component", "value" => $this->_parseRecord($node, $resources));
                    }
                }
            }
        }
        $retArray[] = $resources;
        return $retArray;
    }

    
    private function _createPath($path, $name, $index) {
        $retArray = array();
        if ($index == 0) {
            $retArray = array($name);
        } else {
            for ($i = 0; $i < $index; $i++) {
                $retArray[$i] = $path[$i];
            }
            $retArray[$index] = $name;
        }
        return $retArray;
    }

    private function _processComponent($node, $level, $i) {
        $retArray = array();
        $retArray = $this->_getNodeAttributes($node, $i);
        return $retArray;
    }

    private function _processElement($node, $level, $i) {
        $retArray = array();
        $retArray = $this->_getNodeAttributes($node, $i);
        return $retArray;
    }

    function _getValues($record, $path, &$valueArray) {
        $retArray = $valueArray;
        $xpathQuery = '//cmd:Components/cmd:' . implode("/cmd:", $path);
        $cmdiRec = new DOMDocument();
        $cmdiRec->load($record);
        $xpath = new DOMXpath($cmdiRec);
        $values = $xpath->query($xpathQuery);
        foreach ($values as $value) {
            $valArr = array();
            $valArr["value"] = $value->nodeValue;
            $valArr["path"] = $value->getNodepath();
            $attributes = $this->_getValueAttributes($value);
            if ($attributes) {
                $valArr["attributes"] = $attributes;
            }
            $retArray[] = $valArr;
            //print_r($valArr);
        }
        return $retArray;
    }

    private function _getValueAttributes($valueNode, $res) {
        $retArray = array();
        foreach ($valueNode->attributes as $attribute) {
            $retArray[$attribute->nodeName] = $attribute->nodeValue;
        }
        if (count($retArray)) {
            return $retArray;
        } else {
            return null;
        }
    }

    private function _displaySort($arr) {
        $retArr = array();
        $tempArr = array();
        foreach ($arr as $element) {
            if ($element["type"] == "Component") {
                $tempArr[] = $element;
            } else {
                if (isset($element["attributes"]["displayOrder"])) {
                    $retArr[] = $element;
                } else {
                    $tempArr[] = $element;
                }
            }
        }
        usort($retArr, array(__CLASS__, 'cmp'));
        foreach ($tempArr as $element) {
            $retArr[] = $element;
        }
        return $retArr;
    }

    static function cmp($a, $b) {
        if ($a["attributes"]["displayOrder"] == $b["attributes"]["displayOrder"]) {
            return 0;
        } else {
            return ($a["attributes"]["displayOrder"] < $b["attributes"]["displayOrder"]) ? -1 : 1;
        }
    }

    private function _getNodeAttributes($node, $i) {
        $retArray = array();
        foreach ($node->attributes as $attribute) {
            switch ($attribute->nodeName) {
                case 'ValueScheme':
                case 'CardinalityMin':
                    $retArray[$attribute->nodeName] = $attribute->nodeValue;
                    break;
                case 'CardinalityMax':
                    $retArray[$attribute->nodeName] = $attribute->nodeValue;
                    if ($attribute->nodeValue === "unbounded") {
                        $retArray["duplicate"] = 'yes';
                    }
                    break;
                case 'Multilingual':
                    $retArray[$attribute->nodeName] = $attribute->nodeValue;
                    if ($attribute->nodeValue === "true") {
                        $retArray["duplicate"] = 'yes';
                    }
                    break;
                case 'name':
                    $retArray["name"] = $attribute->nodeValue;
                    $retArray["label"] = $attribute->nodeValue;
                    break;
                case 'cue:displayOrder':
                    $retArray["displayOrder"] = $attribute->nodeValue;
                    break;
                case 'cue:inputHeight':
                    $retArray['height'] = $attribute->nodeValue;
                    break;
                case 'cue:inputWidth':
                    $retArray['width'] = $attribute->nodeValue;
                    break;
                case 'cue:hide':
                    $retArray["hide"] = $attribute->nodeValue;
                    break;
                case 'cue:readonly' :
                    $retArray["readonly"] = $attribute->nodeValue;
                    break;
            }
        }

        foreach ($node->childNodes as $child) {
            switch ($child->nodeName) {
                case 'clariah:label':
                    $retArray["label"] = $child->nodeValue;
                    break;
                case 'AutoValue':
                    $retArray["autoValue"] = $this->_getAutoValue($child->nodeValue);
                    break;
                case 'clariah:display':
                    $retArray["display"] = $child->nodeValue;
                    break;
                case 'clariah:isTab':
                    $retArray["isTab"] = $child->nodeValue;
                    break;
//                case 'clariah:inputField':
//                    $this->_inputFieldDimensions($child, $retArray);
//                    break;
                case 'clariah:resource':
                    $retArray["resource"] = $child->nodeValue;
                    break;
                case 'AttributeList':
                    $retArray["attributeList"] = $this->_createAttributeList($child);
                    break;
                case 'ValueScheme':
                    $retArray["ValueScheme"] = $this->_implementValueScheme($child);
                    if (array_key_exists("pattern", $retArray["ValueScheme"])) {
                        $retArray["pattern"] = $retArray["ValueScheme"]["pattern"];
                        $retArray["ValueScheme"] = "string";
                    }
                    break;
//                case 'clariah:displayOrder':
//                    $retArray["displayOrder"] = $child->nodeValue;
//                    break;
                case 'clariah:autoCompleteURI':
                    $retArray["autoCompleteURI"] = $child->nodeValue;
                    break;
            }
        }
        $retArray["initialOrder"] = $i;
        if (isset($retArray["height"])) {
            $retArray["inputField"] = 'multiple';
        } else {
            if (isset($retArray["width"])) {
                $retArray["inputField"] = 'single';
            }
        }
        return $retArray;
    }
    
    private function _createAttributeList($node){
        $retArray = array();
        foreach ($node->childNodes as $child){
           $retArray[] = $this->_addAttributesForList($child);
        }
        return $retArray;
    }
    
    private function _addAttributesForList($node) {
        $retArray = array();
        foreach($node->attributes as $attribute) {
            $retArray[$attribute->nodeName] = $attribute->nodeValue;
        }
        return $retArray;
    }

    private function _getAutoValue($value) {
        switch ($value) {
            case 'now':
                return array("type" => "function", "value" => date("Y-m-d"));
                break;
            default:
                return $this->_getOtherAutoValues($value);
                break;
        }
    }

    private function _getOtherAutoValues($value) {
        $parts = explode(":", $value);
        if (count($parts) == 2) {
            return array("type" => $parts[0], "value" => $parts[1]);
        } else {
            return "";
        }
    }

    private function _inputFieldDimensions($node, &$retArray) {
        if ($node->nodeValue == '') {
            $retArray["inputField"] = 'single';
        } else {
            $retArray["inputField"] = $node->nodeValue;
        }
        foreach ($node->attributes as $attribute) {
            $retArray[$attribute->nodeName] = $attribute->nodeValue;
        }
    }

    private function _implementValueScheme($xml) {
        $retArray = array();
        foreach ($xml->childNodes as $node) {
            switch ($node->nodeName) {
                case 'pattern':
                    //return "string";
                    return array($node->nodeName => $node->nodeValue);
                    break;
                case 'Vocabulary':
                    $retArray[] = $this->_enumerateVocabulary($node);
                    break;
            }
        }
        return $retArray;
    }

    private function _enumerateVocabulary($xml) {
        $retArray = array();
        foreach ($xml->firstChild->childNodes as $entry) {
            if ($entry->firstChild->nodeType != XML_TEXT_NODE) {
                $retArray[] = $this->_processVocabularyItem($entry);
            } else {
                $retArray[] = array("value" => $entry->nodeValue, "label" => $entry->nodeValue);
            }
        }
        return $retArray;
    }

    private function _processVocabularyItem($node) {
        $retArray = array();
        foreach ($node->childNodes as $child) {
            switch ($child->nodeName) {
                case 'clariah:label':
                    $retArray["label"] = $child->nodeValue;
                    break;
                case 'clariah:value':
                    $retArray["value"] = $child->nodeValue;
                    break;
                default:
                    break;
            }
        }
        return $retArray;
    }

}
