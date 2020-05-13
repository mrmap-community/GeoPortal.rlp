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
$myPW = "**********";
echo "<script language='JavaScript'>var myPW = '".$myPW."';</script>";
?>
<script type="text/javascript">
<?php 
	include '../include/dyn_js.php';
	include '../include/dyn_php.php';
	
	$myPW = "**********";
	echo "var myPW = '".$myPW."';";
	if(!$withPasswordInsertion) {
		$withPasswordInsertion = "true";
	}
	echo "var withPasswordInsertion = '" . $withPasswordInsertion . "';";
?>
</script>
<script type='text/javascript' src="../extensions/jquery.js"></script>
<script type='text/javascript' src="../javascripts/user.js"></script>
<script type='text/javascript'>

function sendRegisterData() {
	if (document.form1.email.value == '') {
		alert("Data could not be sent. No mail address given for this user.");
		return false;
	}
	var parameters = {
		command : "sendMailToUser",
		userId : document.form1.selected_user.options[document.form1.selected_user.selectedIndex].value
	};
	$.post("../php/mod_sendUserMail.php", parameters, function (json, status) {
		if(status == 'success') {
			alert(json);
		}
	});
}

function callPick(obj){
	dTarget = obj;
	var dp = window.open('../tools/datepicker/datepicker.php?m=Jan_Feb_März_April_Mai_Juni_Juli_Aug_Sept_Okt_Nov_Dez&d=Mo_Di_Mi_Do_Fr_Sa_So&t=heute','dp','left=200,top=200,width=230,height=210,toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=0');
	dp.focus();
	return false;
}

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
        permission = confirm("Delete User?");
     }
     if(val == 'new_pw_ticket'){
     	permission = confirm("Set new password ticket for this user?");
     }
     if(permission === true){
      	if(withPasswordInsertion == 'true'){
         	if(document.forms[0].password.value == myPW){
             	document.forms[0].password.value = '';
         	}
         }
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
  if(withPasswordInsertion == 'true') {
	  if(document.forms[0].password.value === '') {
	      alert(str_alert);
	      document.forms[0].password.focus();
	      return 'false';
	  }
      if((document.forms[0].password.value != myPW || document.forms[0].v_password.value !== '' )&& document.forms[0].password.value != document.forms[0].v_password.value){
	      alert("Password verification failed. You have to enter the same password twice!");
	      document.forms[0].password.value = myPW;
	      document.forms[0].password.focus();
	      return 'false';
	  }
  }
  if(document.forms[0].resolution.value === '') {
      document.forms[0].resolution.value = 72;
      return 'true';
  }
  if(document.forms[0].login_count.value === '') {
      document.forms[0].login_count.value = 0;
      return 'true';
  }
  return 'true';
}
/**
 * filter the Userlist by str
 */
function filterUser(list, all, str){
	str=str.toLowerCase();
	var selection=[];
	var i,j,selected;
	for(i=0;i<list.options.length;i++){
		if (list.options[i].selected) {
			selection[selection.length] = list.options[i].value;
		}
	}
	
	list.options.length = 1;
	for(i=0; i<all.length; i++){
		if (all[i].name.toLowerCase().indexOf(str) == -1) {
			continue;
		}
		selected=false;
		for(j=0;j<selection.length;j++){
			if(selection[j]==all[i].id){
				selected=true;
				break;
			}
		}
		var newOption = new Option(all[i].name, all[i].id,false,selected);
		newOption.setAttribute("title", all[i].email);
		list.options[list.options.length] = newOption;
	}	
}
</script>
</head>
<body>
<?php
#delete
if ($action == 'delete' && (!isset($editSelf) || !$editSelf)) {
	$user = new User(intval($selected_user));
	$user->remove();
	$selected_user = 'new';
}

#save
if($action == 'save'){
	$user = User::byName($name);
	
	if (!is_null($user)) {
		echo "<script language='JavaScript'>alert('"._mb("Username must be unique!")."');</script>";
	}
	else {
		$user = new User(null);
		$user->name = $name;
		$user->owner = $owner_id;
		$user->description = $description;
		$user->email = $email;
		$user->phone = $phone;
		$user->organization = $organization;
		$user->position = $position;
		$user->department = $department;
		$user->resolution = $resolution;
		$user->firstName = $firstname;
		$user->lastName = $lastname;
		$user->academicTitle = $academic_title;
		$user->fax = $facsimile;
		$user->street = $street;
		$user->houseNumber = $housenumber;
		$user->deliveryPoint = $delivery_point;
		$user->postalCode = $postal_code;
		$user->city = $city;
		$user->country = $country;
		$user->spatial = $spatial;
		$user->new_password = $new_password;
		
		$user->create();
		$user->setNewUserPasswordTicket();
		
		if($withPasswordInsertion == 'true' && $password !== '' && $user->validUserPasswordTicket($user->passwordTicket)) {
			$user->setPassword($password, $user->passwordTicket);
		}
		
// TODO: uuid() ????
	}
}

#update
if ($action == 'update') {
	//check sercurity:
	// has the user all permissions to do that
	$user = User::byName($name);
	
	if (!is_null($user) && intval($user->id) !== intval($selected_user)) {
		echo "<script language='JavaScript'>alert('"._mb("Username must be unique!")."');</script>";
	}
	else{
		$user = new User(intval($selected_user));
		$user->name = $name;
		$user->owner = $owner_id;
		$user->description = $description;
		$user->email = $email;
		$user->phone = $phone;
		$user->department = $department;
		$user->organization = $organization;
		$user->position = $position;
		$user->resolution = $resolution;
		$user->firstName = $firstname;
		$user->lastName = $lastname;
		$user->academicTitle = $academic_title;
		$user->fax = $facsimile;
		$user->street = $street;
		$user->houseNumber = $housenumber;
		$user->deliveryPoint = $delivery_point;
		$user->postalCode = $postal_code;
		$user->city = $city;
		$user->country = $country;
		$user->loginCount = $login_count;		
		$user->spatial = $spatial;	
		$user->new_password = $new_password;
		
		$user->commit();
		
		// TODO: uuid ???

		$user->setNewUserPasswordTicket();
		
		if($withPasswordInsertion == 'true' && $password !== '' && $user->validUserPasswordTicket($user->passwordTicket)) {
			$user->setPassword($password, $user->passwordTicket);
			echo "<script language='JavaScript'>alert('Password has been updated successfully!');</script>";
		}
	}
}


if($action == 'new_pw_ticket'){
	$user = new user(intval($selected_user));
	$user->setNewUserPasswordTicket();
}

if (!isset($name) || $selected_user == 'new'){
  $name = "";
  $password = "";
  $owner_id = Mapbender::session()->get("mb_user_id");
  $owner_name = Mapbender::session()->get("mb_user_name");
  $description = "";
  $login_count = 0;
  $email = "";
  $phone = "";
  $department = "";
  $organization = "";
  $position = "";
  $resolution = 72;
  $firstname = "";
  $lastname = "";
  $academic_title = "";
  $facsimile = "";
  $street = "";
  $housenumber = "";
  $delivery_point = "";
  $postal_code = "";
  $city = "";
  $country = "";
  $spatial = array();
  $new_password = "FALSE";
}


/*HTML*****************************************************************************************************/
echo "<form name='form1' action='" . $self ."' method='post'><div style=\"float:left;\">";
echo "<table border='0'>";
#User
if ((!isset($editSelf) || !$editSelf)) {
	echo "<tr>";
	   echo "<td>";
	      echo _mb("User").": ";
	   echo "</td>";
	echo "<td>";
	   echo "<input type='text' value='' id='find_user' data-target='selecteduser' owner-check='off' data-target-type='select' data-target-new='true' />";
//	   echo "<input type='text' value='' onkeyup='filterUser(document.getElementById(\"selecteduser\"),user,this.value);'/>";
	   echo "<br /><select id='selecteduser' name='selected_user' onchange='submit()'>";
	   echo "<option value='new'>"._mb("NEW")."...</option>";
	
		$filter = new stdClass();
		if (isset($myUser) && $myUser) {
			$filter->owner = Mapbender::session()->get("mb_user_id");
		}
		/*
		$userArray = User::getList($filter);
		foreach ($userArray as $user) {
			echo "<option value='".htmlentities($user->id, ENT_QUOTES, "UTF-8") . 
				"' title='".htmlentities($user->email, ENT_QUOTES, "UTF-8") . 
				"'";
			if ($selected_user && intval($selected_user) === $user->id) {
				echo "selected";
			}
			echo ">" . htmlentities($user->name, ENT_QUOTES, "UTF-8") . "</option>";
		}
		*/
		$user = new User(intval($selected_user));
		$data = $user->getFields();
		
		if ($selected_user && intval($selected_user) === $user->id) {
			echo '<option value="'.htmlentities($user->id, ENT_QUOTES, 'UTF-8').'" title="'.htmlentities($user->email, ENT_QUOTES, 'UTF-8').'" selected="selected">'.htmlentities($user->name, ENT_QUOTES, "UTF-8").'</option>';
		}
		
		$cnt_user = count($userArray);
		echo "</select>";
		echo "</td>";
	echo "</tr>";
}

if(isset($selected_user) && $selected_user != 0){
	$user = new User(intval($selected_user));
	$data = $user->getFields();

	if ($user->isValid()) {
		$name = $data["name"];
		$password = $data["password"];
		$owner_id = $data["owner"];
		$description = $data["description"];
		$login_count = $data["loginCount"];
		$email = $data["email"];
		$phone = $data["phone"];
		$department = $data["department"];
		$organization = $data["organization"];
		$position = $data["position"];
		$resolution = $data["resolution"];
//		$uuid = $data["uuid"];
		$firstname = $data["firstName"];
		$lastname = $data["lastName"];
		$academic_title = $data["academicTitle"];
		$facsimile = $data["fax"];
		$street = $data["street"];
		$housenumber = $data["houseNumber"];
		$delivery_point = $data["deliveryPoint"];
		$postal_code = $data["postalCode"];
		$city = $data["city"];
		$country = $data["country"];
		$spatial = array_flip(explode(",",$data["spatial"]));
		
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

//wieder Wechsel der Connection auf normale Mapbender-DB
$con = db_connect(DBSERVER,OWNER,PW);
db_select_db(DB,$con);

$owner = new User(intval($owner_id));
if ($owner->isValid()) {
	$owner_name = $owner->name;
}


# blank row
echo "<tr>";
   echo "<td colspan='2'>&nbsp;</td>";
echo "</tr>";

#username
echo "<tr>";
   echo "<td>"._mb("Username").":</td>";
   echo "<td>";
      echo "<input type='text' size='30' name='name' value='".htmlentities($name, ENT_QUOTES, "UTF-8")."'>";
   echo "</td>";
echo "</tr>";

echo "<tr>";
   echo "<td>"._mb("Firstname").":</td>";
   echo "<td>";
      echo "<input type='text' size='30' name='firstname' value='".htmlentities($firstname, ENT_QUOTES, "UTF-8")."'>";
   echo "</td>";
echo "</tr>";


echo "<tr>";
   echo "<td>"._mb("Lastname").":</td>";
   echo "<td>";
      echo "<input type='text' size='30' name='lastname' value='".htmlentities($lastname, ENT_QUOTES, "UTF-8")."'>";
   echo "</td>";
echo "</tr>";

echo "<tr>";
   echo "<td>"._mb("Academic title").":</td>";
   echo "<td>";
      echo "<input type='text' size='30' name='academic_title' value='".htmlentities($academic_title, ENT_QUOTES, "UTF-8")."'>";
   echo "</td>";
echo "</tr>";

# blank row
echo "<tr>";
   echo "<td colspan='2'>&nbsp;</td>";
echo "</tr>";

if($withPasswordInsertion == 'true') {
	#password
	echo "<tr>";
	   echo "<td>"._mb("Password").": </td>";
	   echo "<td>";
	      echo "<input type='password' size='30' name='password' value='";
	      if(isset($selected_user) && $selected_user != 'new'){
	         echo $myPW;
	      }
	      echo "' >";
	      echo "<input type='hidden' name='password_plain' value='".htmlentities($password, ENT_QUOTES, "UTF-8")."'>";
	   echo "</td>";
	echo "</tr>";
	
	#confirm password
	echo "<tr>";
   echo "<td>"._mb("Confirm password").": </td>";
	   echo "<td>";
	      echo "<input type='password' size='30' name='v_password' value='";
	      echo "'>";
	   echo "</td>";
	echo "</tr>";
}

# blank row
echo "<tr>";
   echo "<td colspan='2'>&nbsp;</td>";
echo "</tr>";

#description
echo "<tr>";
   echo "<td>"._mb("Description").": </td>";
   echo "<td>";
      echo "<input type='text' size='30' name='description' value='".htmlentities($description, ENT_QUOTES, "UTF-8")."'>";
   echo "</td>";
echo "</tr>";

#email
echo "<tr>";
   echo "<td>"._mb("Email").": </td>";
   echo "<td>";
      echo "<input type='text' size='30' name='email' value='".htmlentities($email, ENT_QUOTES, "UTF-8")."'>";
   echo "</td>";
echo "</tr>";

#phone
echo "<tr>";
   echo "<td>"._mb("Phone").": </td>";
   echo "<td>";
      echo "<input type='text' size='30' name='phone' value='".htmlentities($phone, ENT_QUOTES, "UTF-8")."'>";
   echo "</td>";
echo "</tr>";

#fax
echo "<tr>";
   echo "<td>"._mb("Facsimile").": </td>";
   echo "<td>";
      echo "<input type='text' size='30' name='facsimile' value='".htmlentities($facsimile, ENT_QUOTES, "UTF-8")."'>";
   echo "</td>";
echo "</tr>";


# blank row
echo "<tr>";
   echo "<td colspan='2'>&nbsp;</td>";
echo "</tr>";

#street
echo "<tr>";
   echo "<td>"._mb("Street").": </td>";
   echo "<td>";
      echo "<input type='text' size='30' name='street' value='".htmlentities($street, ENT_QUOTES, "UTF-8")."'>";
   echo "</td>";
echo "</tr>";

#houseno.
echo "<tr>";
   echo "<td>"._mb("Housenumber").": </td>";
   echo "<td>";
      echo "<input type='text' size='30' name='housenumber' value='".htmlentities($housenumber, ENT_QUOTES, "UTF-8")."'>";
   echo "</td>";
echo "</tr>";

#delivery_point
echo "<tr>";
   echo "<td>"._mb("Delivery Point").": </td>";
   echo "<td>";
      echo "<input type='text' size='30' name='delivery_point' value='".htmlentities($delivery_point, ENT_QUOTES, "UTF-8")."'>";
   echo "</td>";
echo "</tr>";

#postal_code
echo "<tr>";
   echo "<td>"._mb("Postal Code").": </td>";
   echo "<td>";
      echo "<input type='text' size='30' name='postal_code' value='".htmlentities($postal_code, ENT_QUOTES, "UTF-8")."'>";
   echo "</td>";
echo "</tr>";

#city
echo "<tr>";
   echo "<td>"._mb("City").": </td>";
   echo "<td>";
      echo "<input type='text' size='30' name='city' value='".htmlentities($city, ENT_QUOTES, "UTF-8")."'>";
   echo "</td>";
echo "</tr>";

#organization
echo "<tr>";
   echo "<td>"._mb("Organization").": </td>";
   echo "<td>";
      echo "<input type='text' size='30' name='organization' value='".htmlentities($organization, ENT_QUOTES, "UTF-8")."'>";
   echo "</td>";
echo "</tr>";

#department
echo "<tr>";
   echo "<td>"._mb("Department").": </td>";
   echo "<td>";
      echo "<input type='text' size='30' name='department' value='".htmlentities($department, ENT_QUOTES, "UTF-8")."'>";
   echo "</td>";
echo "</tr>";

#position
echo "<tr>";
   echo "<td>"._mb("Position").": </td>";
   echo "<td>";
      echo "<input type='text' size='30' name='position' value='".htmlentities($position, ENT_QUOTES, "UTF-8")."'>";
   echo "</td>";
echo "</tr>";

#country
echo "<tr>";
   echo "<td>"._mb("Country").": </td>";
   echo "<td>";
      echo "<input type='text' size='30' name='country' value='".htmlentities($country, ENT_QUOTES, "UTF-8")."'>";
   echo "</td>";
echo "</tr>";


# blank row
echo "<tr>";
   echo "<td colspan='2'>&nbsp;</td>";
echo "</tr>";

#owner
echo "<tr>";
   echo "<td>"._mb("Owner").": </td>";
   echo "<td>";
      echo "<input type='text' size='30' name='owner_name' value='".htmlentities($owner_name, ENT_QUOTES, "UTF-8")."' readonly>";
      echo "<input type='hidden' size='30' name='owner_id' value='".htmlentities($owner_id, ENT_QUOTES, "UTF-8")."' readonly>";
   echo "</td>";
echo "</tr>";

/*
#uuid
echo "<tr>";
   echo "<td>UUID: </td>";
   echo "<td>";
      echo "<input type='text' size='30' name='mb_user_uuid' value='".htmlentities($uuid."' readonly>";
   echo "</td>";
echo "</tr>";
*/

#login_count
echo "<tr>";
   echo "<td>"._mb("Login_count").": </td>";
   echo "<td>";
      echo "<input type='text' size='30' name='login_count' value='".htmlentities($login_count, ENT_QUOTES, "UTF-8")."'>";
   echo "</td>";
echo "</tr>";

#echo"</table>";

#resolution
#echo "<tr>";
#   echo "<td>Resolution: </td>";
#   echo "<td>";
      echo "<input type='hidden' size='30' name='resolution' value='".htmlentities($resolution, ENT_QUOTES, "UTF-8")."'>";
#   echo "</td>";
#echo "</tr>";


# blank row
echo "<tr>";
   echo "<td colspan='2'>&nbsp;</td>";
echo "</tr>";

echo "<tr>";
   echo "<td>&nbsp;</td>";
   echo "<td>";
        if($selected_user == 'new' || !isset($selected_user)){
           echo "<input type='button' value='save'  onclick='validate(\"save\")'>";
        }
        if(Mapbender::session()->get("mb_user_id") == $owner_id && $selected_user != 'new' && $selected_user != '' ){
           echo "<input type='button' value='save'  onclick='validate(\"update\")'>";
			if ((!isset($editSelf) || !$editSelf) && intval(Mapbender::session()->get("mb_user_id")) !== intval($selected_user)) {
	           echo "<input type='button' value='delete'  onclick='validate(\"delete\")'>";
			}
           if($withPasswordInsertion != 'true') {
           	  echo "<input type='button' value='Send login data to user'  onclick='sendRegisterData();'>";
           	  echo "&nbsp;<input type='button' value='New password ticket'  onclick='validate(\"new_pw_ticket\");'>";
           }
        }
   echo "</td>";
echo "</tr>";    
?>
<input type='hidden' name='action' value=''>
</table>
</div>
<div style="float:right;width:200px;height:400px;">
    Verwaltungseinheiten für räuml. Absicherung<br/>
    <select name="spatial[]" multiple style="width:250px;height:400px;background-color:white;">
        <?php echo $select_options; ?>
    </select>
</div>
</form>
<script type="text/javascript">
<!--
var user=[];
<?php
	for($i=0; $i<$cnt_user; $i++){
		echo "user[".($i)."]=[];\n";
		echo "user[".($i)."]['id']='" . $userArray[$i]->id  . "';\n";
		echo "user[".($i)."]['name']='" . $userArray[$i]->name  . "';\n";
		echo "user[".($i)."]['email']='" . $userArray[$i]->email  . "';\n";
	}
?>
// -->
</script>
</body>
</html>