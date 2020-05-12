<?php
class SimpleXMLElementExtended extends SimpleXMLElement {
    public function getAttribute($name){
        foreach($this->attributes() as $key=>$val){
            if($key == $name){
                return (string)$val;
            }// end if
        }// end foreach
    }// end function getAttribute

    public function getAttributeNames(){
        $cnt = 0;
        $arrTemp = array();
        foreach($this->attributes() as $a => $b) {
            $arrTemp[$cnt] = (string)$a;
            $cnt++;
        }// end foreach
        return (array)$arrTemp;
    }// end function getAttributeNames

    public function getChildrenCount(){
        $cnt = 0;
        foreach($this->children() as $node){
            $cnt++;
        }// end foreach
        return (int)$cnt;
    }// end function getChildrenCount

    public function getAttributeCount(){
        $cnt = 0;
        foreach($this->attributes() as $key=>$val){
            $cnt++;
        }// end foreach
        return (int)$cnt;
    }// end function getAttributeCount

    public function getAttributesArray($names){
        $len = count($names);
        $arrTemp = array();
        for($i = 0; $i < $len; $i++){
            $arrTemp[$names[$i]] = $this->getAttribute((string)$names[$i]);
        }// end for
        return (array)$arrTemp;
    }// end function getAttributesArray

    public function isAttribute($name){
        foreach($this->attributes() as $key=>$val){
            if($key == $name){
                return true;
            }// end if
        }// end foreach
        return false;
    }// end function isAttribute

    public function setAttribute($name,$value){
    	if($value!="") {
    		if($this->isAttribute($name)) {
    			$this[$name]=$value;
    		} else {
    			$this->addAttribute($name,$value);
    		}
    	}
    }// end function setAttribute

    public function setValue($value){
    	if($value!="") {
   			$this[0]=$value;
   		}
    }// end function setAttribute

		public function addChildCData($nodename,$cdata_text) {
			$node = $this->addChild($nodename); //Added a nodename to create inside the function
			$node = dom_import_simplexml($node);
			$no = $node->ownerDocument;
			$node->appendChild($no->createCDATASection($cdata_text));
  	}
/*
		public function addSpecialChild($nodename,$cdata_text) {
			$element = new SimpleXMLElement('<'.$nodename.'/>');
			$element->addChild('//', $cdata_text);
			echo $element->asXML();    // <exclamation>Amazing!</exclamation>			
			$node = $this->addChild($nodename); //Added a nodename to create inside the function
			$node = dom_import_simplexml($node);
			$no = $node->ownerDocument;
			$node->appendChild($no->createCDATASection($cdata_text));
  	}
*/
}
?>