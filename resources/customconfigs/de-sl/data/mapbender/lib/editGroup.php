<?php 
require_once dirname(__file__)."/../conf/geoportal.conf";
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<?php
echo '<meta http-equiv="Content-Type" content="text/html; charset='.CHARSET.'">';	
?>
<title></title>
<?php
include '../include/dyn_css.php';
?>
<script type="text/javascript">

function validate(val){
   var ok = validateInput();
   if(ok == 'true'){
     var permission = false;
     if(val == 'save'){
        permission = confirm("Save changes?");
     }
     if(val == 'update'){
        permission = confirm("Save changes?");
     }
     if(val == 'delete'){
        permission = confirm("Delete Group?");
     }
     if(permission === true){
        document.forms[0].action.value = val;
        document.forms[0].submit();
     }
   }
}
function validateInput(){
  var str_alert = "Input incorrect !";
  if(document.forms[0].name.value === ''){
      alert(str_alert);
      document.forms[0].name.focus();
      return 'false';
  }
  return 'true';
}
</script>

</head>
<body>
<?php

#delete
if($action == 'delete'){
	$group = new Group(intval($selected_group));
	$group->remove();
	$selected_group = 'new';
}

#save
if($action == 'save'){
	$group = Group::byName($name);

	if(!is_null($group)){
		echo "<script type='text/javascript'>alert('groupname must be unique!');</script>";
	}
	else {
		$group = new Group(null);
		$changes = new stdClass();
		$changes->name = $name;
		$changes->owner = $owner_id;
		$changes->description = $description;
		$changes->title = $title;
		$changes->address = $address;
		$changes->postcode = $postcode;
		$changes->city = $city;
		$changes->stateorprovince = $stateorprovince;
		$changes->country = $country;
		$changes->voicetelephone = $voicetelephone;
		$changes->facsimiletelephone = $facsimiletelephone;
		$changes->email = $email;
		$changes->logo_path = $logo_path;
		$changes->spatial = $spatial;
		$group->change($changes);	
		
		$group->create();	

		$selected_group = $group->getId();
	}
}

#update
if ($action == 'update') {
	$group = Group::byName($name);
	if (!is_null($group) && intval($group->getId()) !== intval($selected_group)) {
		echo "<script type='text/javascript'>alert('Groupname must be unique!');</script>";
	}
	else {
		$group = new Group(intval($selected_group));
		$changes = new stdClass();
		$changes->name = $name;
		$changes->owner = $owner_id;
		$changes->description = $description;
		$changes->title = $title;
		$changes->address = $address;
		$changes->postcode = $postcode;
		$changes->city = $city;
		$changes->stateorprovince = $stateorprovince;
		$changes->country = $country;
		$changes->voicetelephone = $voicetelephone;
		$changes->facsimiletelephone = $facsimiletelephone;
		$changes->email = $email;
		$changes->logo_path = $logo_path;
		$changes->spatial = $spatial;
		$group->change($changes);		

		$group->commit();	
	}
}

if (!isset($name) || $selected_group == 'new'){
	$name = "";
	$owner_id = Mapbender::session()->get("mb_user_id");
	$owner = new User(intval($owner_id));
	$owner_name = $owner->name;
	$description = "";
	$title = "";
	$address = "";
	$postcode = "";
	$city = "";
	$stateorprovince = "";
	$country = "";
	$voicetelephone = "";
	$facsimiletelephone = "";
	$email = "";
	$logo_path = "";
	$spatial = array();
}

/*HTML*****************************************************************************************************/

echo "<form name='form1' action='" . $self ."' method='post'><div style=\"float:left;\">";
echo "<table border='0'>";
#User
echo "<tr>";
   echo "<td>";
      echo "Group: ";
   echo "</td>";
echo "<td>";
echo "<select name='selected_group' onchange='submit()'>";
	echo "<option value='new'>NEW...</option>";
	$filter = new stdClass();
	if (isset($myGroup) && $myGroup) {
		$filter->owner = Mapbender::session()->get("mb_user_id");
	}
	$groupArray = Group::getList($filter);
	foreach ($groupArray as $group) {
		echo "<option value='" . 
			htmlentities($group->getId(), ENT_QUOTES. "UTF-8") . "' ";

		if ($selected_group && intval($selected_group) == $group->getId()) {
			echo "selected";
		}
		echo ">" . 
			htmlentities($group->name, ENT_QUOTES, "UTF-8") . "</option>";
	}
	echo "</select>";
	echo "</td>";
echo "</tr>";


if(isset($selected_group) && $selected_group != 0){
	$group = new Group(intval($selected_group));
	$data = $group->getFields();

	if ($group->isValid()) {
		$name = $data["name"];
		$owner_id = $data["owner"];
		$description = $data["description"];
		$title = $data["title"]; 
		$address = $data["address"];
		$postcode = $data["postcode"];
		$city = $data["city"]; 
		$stateorprovince = $data["stateorprovince"]; 
		$country = $data["country"]; 
		$voicetelephone = $data["voicetelephone"];
		$facsimiletelephone = $data["facsimiletelephone"];
		$email = $data["email"];
		$logo_path = $data["logo_path"];
		$spatial = array_flip(explode(",",$data["spatial"]));
	}
	$owner = new User(intval($owner_id));
	if ($owner->isValid()) {
		$owner_name = $owner->name;
	}
}


$select_options = "";
$conn = pg_connect("host=".GEOMDB_HOST." port=".GEOMDB_PORT." dbname=".GEOMDB_NAME." user=".GEOMDB_USER." password=".GEOMDB_PASSWORD);
if($conn) {
    $result = pg_query($conn,"SELECT gemeinde, gem_schl FROM gis.verwaltungseinheit");
    
    while($tmp = pg_fetch_assoc($result)) {
        $select_options .= "\n<option value='".$tmp["gem_schl"]."' ".(isset($spatial[$tmp["gem_schl"]]) ? ' selected="selected" ' : "").">".$tmp["gemeinde"]."</option>";
    }
}



#name
echo "<tr>";
   echo "<td>"._mb("Name").":</td>";
   echo "<td>";
      echo "<input type='text' size='30' name='name' value='" . 
	  	htmlentities($name, ENT_QUOTES, "UTF-8") . "'>";
   echo "</td>";
echo "</tr>";

#title
echo "<tr>";
   echo "<td>"._mb("Title").":</td>";
   echo "<td>";
      echo "<input type='text' size='30' name='title' value='" . 
	  	htmlentities($title, ENT_QUOTES, "UTF-8") . "'>";
   echo "</td>";
echo "</tr>";


#owner
echo "<tr>";
   echo "<td>"._mb("Owner").": </td>";
   echo "<td>";
	echo "<input type='text' size='30' name='owner_name' value='" . 
	  	htmlentities($owner_name, ENT_QUOTES, "UTF-8") . "' readonly>";
      echo "<input type='hidden' size='30' name='owner_id' value='" . 
	  	htmlentities($owner_id, ENT_QUOTES, "UTF-8") . "' readonly>";
   echo "</td>";
echo "</tr>";

#description
echo "<tr>";
   echo "<td>"._mb("Description").": </td>";
   echo "<td>";
      echo "<input type='text' size='30' name='description' value='" . 
	  	htmlentities($description, ENT_QUOTES, "UTF-8") . "'>";
   echo "</td>";
echo "</tr>";

#address
echo "<tr>";
   echo "<td>"._mb("Address").": </td>";
   echo "<td>";
      echo "<input type='text' size='30' name='address' value='" . 
	  htmlentities($address, ENT_QUOTES, "UTF-8") . "'>";
   echo "</td>";
echo "</tr>";

#postcode
echo "<tr>";
   echo "<td>"._mb("Postcode").": </td>";
   echo "<td>";
      echo "<input type='text' size='30' name='postcode' value='" . 
	  	htmlentities($postcode, ENT_QUOTES, "UTF-8") . "'>";
   echo "</td>";
echo "</tr>";

#city
echo "<tr>";
   echo "<td>"._mb("City").": </td>";
   echo "<td>";
      echo "<input type='text' size='30' name='city' value='" . 
	  	htmlentities($city, ENT_QUOTES, "UTF-8") . "'>";
   echo "</td>";
echo "</tr>";

#provice
echo "<tr>";
   echo "<td>"._mb("Province").": </td>";
   echo "<td>";
      echo "<input type='text' size='30' name='stateorprovince' value='" . 
	  htmlentities($stateorprovince, ENT_QUOTES, "UTF-8") . "'>";
   echo "</td>";
echo "</tr>";

#country
echo "<tr>";
   echo "<td>"._mb("Country").": </td>";
   echo "<td>";
      echo "<input type='text' size='30' name='country' value='" . 
	  htmlentities($country, ENT_QUOTES, "UTF-8") . "'>";
   echo "</td>";
echo "</tr>";

#voicetelephone
echo "<tr>";
   echo "<td>"._mb("Voicetelephone").": </td>";
   echo "<td>";
      echo "<input type='text' size='30' name='voicetelephone' value='" . 
	  htmlentities($voicetelephone, ENT_QUOTES, "UTF-8") . "'>";
   echo "</td>";
echo "</tr>";

#facsimiletelephone
echo "<tr>";
   echo "<td>"._mb("Facsimiletelephone").": </td>";
   echo "<td>";
      echo "<input type='text' size='30' name='facsimiletelephone' value='" . 
	  htmlentities($facsimiletelephone, ENT_QUOTES, "UTF-8") . "'>";
   echo "</td>";
echo "</tr>";

#email
echo "<tr>";
   echo "<td>Email: </td>";
   echo "<td>";
      echo "<input type='text' size='30' name='email' value='" . 
	  htmlentities($email, ENT_QUOTES, "UTF-8") . "'>";
   echo "</td>";
echo "</tr>";

#logo
echo "<tr>";
   echo "<td>Logo: </td>";
   echo "<td>";
      echo "<input type='text' size='30' name='logo_path' value='" . 
	  htmlentities($logo_path, ENT_QUOTES, "UTF-8") . "'>";
   echo "</td>";
echo "</tr>";

# blank row
echo "<tr>";
   echo "<td colspan='2'>&nbsp;</td>";
echo "</tr>";

# send form
echo "<tr>";
   echo "<td>&nbsp;</td>";
   echo "<td>";
	if($selected_group == 'new' || !isset($selected_group)){
		echo "<input type='button' value='save'  onclick='validate(\"save\")'>";
	}
	if(Mapbender::session()->get("mb_user_id") == $owner_id && $selected_group != 'new' && $selected_group != '' ){
		echo "<input type='button' value='save'  onclick='validate(\"update\")'>";
		echo "<input type='button' value='delete'  onclick='validate(\"delete\")'>";
	}
   echo "</td>";
echo "</tr>";

echo"</table>";

?>
<input type='hidden' name='action' value=''>
</div>
<div style="float:right;width:250px;height:400px;">
    Verwaltungseinheiten für räuml. Absicherung<br/>
    <select name="spatial[]" multiple style="width:250px;height:400px;background-color:white;">
        <?php echo $select_options; ?>
    </select>
</div>
</form>
</body>
</html>