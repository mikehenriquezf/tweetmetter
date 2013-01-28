<?php
class xml2Array {
   
    var $arrOutput = array();
    var $resParser;
    var $strXmlData;
	var $error='';
	var $error_die=true;
   
    function parse($strInputXML) {
				$this->arrOutput = array();
				
            $this->resParser = xml_parser_create();
            xml_set_object($this->resParser,$this);
            xml_set_element_handler($this->resParser, "tagOpen", "tagClosed");
           
            xml_set_character_data_handler($this->resParser, "tagData");
       
            $this->strXmlData = xml_parse($this->resParser,$strInputXML);
            if(!$this->strXmlData) {
				$this->error=sprintf("XML error: %s en linea %d, columna %d (%s)",xml_error_string(xml_get_error_code($this->resParser)),
				xml_get_current_line_number($this->resParser), xml_get_current_byte_index($this->resParser), htmlentities(substr($strInputXML, xml_get_current_byte_index($this->resParser)-10, 20)));
				if ($this->error_die)
					die($this->error);
				else
					return false;
            }
                           
            xml_parser_free($this->resParser);
            return $this->arrOutput;
    }

    function tagOpen($parser, $name, $attrs) {
       $tag["name"]=$name;
	   if (count($attrs)) $tag["attr"]=$attrs;
       array_push($this->arrOutput,$tag);
    }
   
    function tagData($parser, $tagData) {
       if(trim($tagData)) {
            if(isset($this->arrOutput[count($this->arrOutput)-1]['tagData'])) {
                $this->arrOutput[count($this->arrOutput)-1]['tagData'] .= $tagData;
            }
            else {
                $this->arrOutput[count($this->arrOutput)-1]['tagData'] = $tagData;
            }
       }
    }
   
    function tagClosed($parser, $name) {
       $this->arrOutput[count($this->arrOutput)-2]['children'][] = $this->arrOutput[count($this->arrOutput)-1];
       array_pop($this->arrOutput);
    }
}

