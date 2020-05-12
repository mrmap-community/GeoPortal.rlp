<?php
class mbPermanentImgDecorator extends mbTemplatePdfDecorator {

	protected $pageElementType = "permanentImage";
	protected $elementId;
	
	public function __construct($pdfObj, $elementId, $mapConf, $controls) {
		parent::__construct($pdfObj, $mapConf, $controls);
		$this->elementId = $elementId;
		$this->override();		
		$this->decorate();
	}
	
	public function override() {}
	
	public function decorate() {
		global $mapOffset_left, $mapOffset_bottom, $map_height, $map_width, $coord;
		global $yAxisOrientation;
		$yAxisOrientation = 1;
        $coord = mb_split(",",$this->pdf->getMapExtent());
		$mapInfo = $this->pdf->getMapInfo();
        $mapOffset_left = $mapInfo["x_ul"];
		$mapOffset_bottom = $mapInfo["y_ul"];
		$map_height = $mapInfo["height"];
		$map_width = $mapInfo["width"];

		
		if (isset($_REQUEST["mypermanentImage"]) && $_REQUEST["mypermanentImage"] != "") {
			$permanentImage = $_REQUEST["mypermanentImage"];
		}
		else {
			return "No mypermanentImage values found.";
		}

        $markers = json_decode($permanentImage);

        // If images are printed use the $_REQUEST['dpi'] setting they get too small at hight dpi values
        $imagedpi = 72;

        foreach( $markers as $marker){


            // left edge of the map on pdf 
            $pdf_map_x1 = $mapInfo["x_ul"]; // unit: mm

            // width of the map on the pdf
            $pdf_map_dx =$mapInfo["width"]; //unit mm
            

            // left edge of the map on the map
            $map_x1 = $coord[0]; // unit: coord
            // right edge of the map on the map
            $map_x2 = $coord[2]; // unit: coord
            
            // width of map in on map
            $map_dx = $map_x2 - $map_x1; //unit: coord
            


            // left edge of the image on map
            $img_x1 = $marker->position[0]; //unit: coord
            
            // the distance between the left edge of the map and the image on the map
            $img_off_x =  $img_x1 - $map_x1; //unit: coord 


            // add the marker offset to the left 
            $pdf_marker_off_x = ($marker->offset_x * 25.4)/$imagedpi; //unit: mm
            
            // left edge of the image on the pdf
            $pdf_img_off_x = ($pdf_map_x1 +  $img_off_x * ($pdf_map_dx/$map_dx)) - $pdf_marker_off_x ;  //unit: mm

            // width of the image on the pdf
            $pdf_img_dx = ($marker->width * 25.4)/$imagedpi; //unit: mm
            

            // top edge of the map on pdf 
            $pdf_map_y1 = $mapInfo["y_ul"]; // unit: mm

            // height of the map on the pdf
            $pdf_map_dy =$mapInfo["height"]; //unit mm
            

            // top edge of the map on the map
            $map_y1 = $coord[1]; // unit: coord
            // bottom edge of the map on the map
            $map_y2 = $coord[3]; // unit: coord
            
            // height of map in on map
            $map_dy = $map_y2 - $map_y1; //unit: coord
            

            // top edge of the image on map
            $img_y1 = $marker->position[1]; //unit: coord
            
            // the distance between the top edge of the map and the image on the map
            $img_off_y =  $img_y1 - $map_y1; //unit: coord 


            // add the marker offset to the top 
            $pdf_marker_off_y = ($marker->offset_y * 25.4)/$imagedpi; //unit: mm
            
            // top edge of the image on the pdf
            $pdf_img_off_y = ($pdf_map_dy + $pdf_map_y1)  - (  $img_off_y * ($pdf_map_dy/$map_dy)) - $pdf_marker_off_y;  //unit: mm

            // width of the image on the pdf
            $pdf_img_dy = ($marker->width * 25.4)/$imagedpi; //unit: mm

            
            $left = $pdf_img_off_x;
            $top = $pdf_img_off_y;
            $width = $pdf_img_dx;
            $height = $pdf_img_dy;
            $path = dirname(__FILE__)."/../../frames/".$marker->path;

            $e = new mb_notice("mbPermanentImgDecorator: img left: ".$left);
            $e = new mb_notice("mbPermanentImgDecorator: img top: ".$top);
            $e = new mb_notice("mbPermanentImgDecorator: img width: ".$width);
            $e = new mb_notice("mbPermanentImgDecorator: img height: ".$height);
            $e = new mb_notice("mbPermanentImgDecorator: img path: ".$path);

            $this->pdf->objPdf->Image($path, $left, $top, $width, $height, 'png');


        }
		
    }
	

}

?>
