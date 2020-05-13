<?php
	if(isset($_REQUEST["sid"])) {
		session_id($_REQUEST["sid"]);
		session_start();
		
//		require_once dirname(__FILE__)."/classes/class_user.php";
//
//		$user = new User();
//		$guis = $user->getApplicationsByPermission();

		echo json_encode(array(
			"user_id" => $_SESSION["mb_user_id"],
			"user_name" => $_SESSION["mb_user_name"],
			"user_guis" => $_SESSION["mb_user_application_guis"],
		));
	}
?>

