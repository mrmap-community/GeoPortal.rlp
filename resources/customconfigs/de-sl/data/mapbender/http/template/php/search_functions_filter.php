<?php
global $filterResources;
global $OrigURL;
$filterResources=array();
$OrigURL='';

function Filter($file) {
	global $out, $name, $startpage, $L, $page, $items_page, $AdrCat, $AdrCatCount;
	global $filterResources;
	global $OrigURL;
	global $LinkURL;

	$ready=0;
	$order='';
	$maxresults='';
	$url=$LinkURL.'?cat='.$_REQUEST['cat'].'&searchfilter=';
	if(file_exists($file) && filesize($file)>0) {
		$DATA=json_decode(file_get_contents($file));
		if($DATA) {
#			$out[$name].=print_r($DATA,true);
			foreach($DATA->searchFilter as $filtername=>$Filter) {
				if($filtername=='classes') {
					foreach($Filter as $Class) {
						$filterResources[$Class->name]=$Class->title;
					}
				} elseif($filtername=='origURL') {
					$OrigURL=$Filter;
				} elseif($filtername=='orderFilter') {
					if($_REQUEST['cat']=='dienste') {
						if(is_array($Filter->item)) {
#							<h3>
#								'.$Filter->title.'
#							</h3>

							$order.='
								<form action="" method="post" onsumbit="return false;">
								<div><fieldset><legend>'.$Filter->header.'</legend><select onchange="if(this.options[this.selectedIndex].value!=\'\'){window.location.href=document.getElementsByTagName(\'base\')[0].getAttribute(\'href\')+this.options[this.selectedIndex].value;}">
									<option value="">'.$Filter->title.'</option>';
								// Select-Boxen bauen
							foreach($Filter->item as $Order) {
								$order.='
								<option value="'.$url.urlencode($Order->url).'">'.$Order->title.'</option>';
#							<p>
#								'.(($Order->url!='')?' <a href="'.$url.urlencode($Order->url).'">'.$Order->title.'</a>':$Order->title).'
#							</p>';
	
							}
							$order.='
								</select>
								</fieldset>
								</div>
								</form>';
						}
					}
				} elseif($filtername=='maxResults') {
					if($_REQUEST['cat']=='dienste') {
						if(is_array($Filter->item)) {
#							<h3>
##								'.$Filter->title.'
#							</h3>

							$maxresults.='
								<form action="" method="post" onsumbit="return false;">
								<div><fieldset><legend>'.$Filter->header.'</legend><select onchange="if(this.options[this.selectedIndex].value!=\'\'){window.location.href=document.getElementsByTagName(\'base\')[0].getAttribute(\'href\')+this.options[this.selectedIndex].value;}">
									<option value="">'.$Filter->title.'</option>';
								// Select-Boxen bauen
							foreach($Filter->item as $Maxresults) {
								$maxresults.='
								<option value="'.$url.urlencode($Maxresults->url).'">'.$Maxresults->title.'</option>';
#							<p>
#								'.(($Order->url!='')?' <a href="'.$url.urlencode($Order->url).'">'.$Order->title.'</a>':$Order->title).'
#							</p>';

							}
							$maxresults.='
								</select>
								</fieldset>
								</div>
								</form>';
	
						}
					}
				} else {
					$out[$name].='
						<h3><a href="'.$url.urlencode($Filter->delLink).'" title="alle entfernen">'.$Filter->title.(($Filter->delLink!='')?'<span>&times;</span></a>':'').'</h3>';

					foreach($Filter->item as $SearchItem) {
						$out[$name].='
						<a href="'.$url.urlencode($SearchItem->delLink).'" title="entfernen">
							'.$SearchItem->title.(($SearchItem->delLink!='')?'':'').'
						</a>';
					}
				}
			}
			$out[$name].=$order;
			$out[$name].=$maxresults;
			$ready=1;
		}
	}

	return $ready;
}

function getSearchFilter($file) {
	global $filterResources;
	global $OrigURL;

	if(file_exists($file) && filesize($file)>0) {
		$DATA=json_decode(file_get_contents($file));
		if($DATA) {
			$OrigURL=$DATA->searchFilter->origURL;
			foreach($DATA->searchFilter->classes as $Class) {
				$filterResources[$Class->name]=$Class->title;
			}
		}
	}
}
?>
