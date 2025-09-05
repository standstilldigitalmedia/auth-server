<?php
	require_once("SSDMRegisterPlayer.php");
	require_once("SSDMActivatePlayer.php");
	require_once("SSDMLoginPlayer.php");

	header("Content-Type: application/json");

	//Examples to remind me once I am no longer testing in a browser
	/*
	$incoming_player = json_decode(file_get_contents('php://input'), false);

	if(!property_exists($incoming_player, 'request_type'))
	{
		echo "request_type property does not exist";
	}
		
	if(!isset($incomming_player->request_type))
	{
		echo "Request type is not set";
	}
	*/


	/***************************** 
	 * This code is for testing purposes only.  
	 * It's just easier to see errors in my web browser using $_GET
	 * ***************************/
	
	$request_type = -1;
	$user_name = "";
	$display_name = "";
	$email = "";
	$password = "";
	$client_id = "";
	$player_id = "";
	$session_ticket = "";
	
	if($_SERVER['REQUEST_METHOD'] === 'POST') 
	{
		$request_type = @$_POST['request_type'];
		$user_name = @$_POST['user_name'];
		$display_name = @$_POST['display_name'];
		$email = @$_POST['email'];
		$password = @$_POST['password'];
		$client_id = @$_POST['client_id'];
		$player_id = @$_POST['player_id'];
		$session_ticket = @$_POST['session_ticket'];
	} 
	elseif($_SERVER['REQUEST_METHOD'] === 'GET') 
	{
		$request_type = @$_GET['request_type'];
		$user_name = @$_GET['user_name'];
		$display_name = @$_GET['display_name'];
		$email = @$_GET['email'];
		$password = @$_GET['password'];	
		$client_id = @$_GET['client_id'];
		$player_id = @$_GET['player_id'];
		$session_ticket = @$_GET['session_ticket'];
	} 
	else 
	{
		$player = new SSDMPlayer();
		$player->error = "Invalid input method";
		echo json_encode($player);
		return;
	}

	/********************* 
	 * End Test Code
	 * *******************/

	if($request_type == REGISTER_NEW_PLAYER_SESSION_TYPE)
	{
		$player = new SSDMRegisterPlayer($user_name, $display_name, $email, $password, $client_id);
		echo json_encode($player);
	}
	elseif($request_type == ACTIVATE_SESSION_TYPE)
	{
		$player = new SSDMActivatePlayer($client_id, $player_id, $session_ticket);
		echo json_encode($player);
	}
	elseif($request_type == LOGIN_SESSION_TYPE)
	{
		$player = new SSDMLoginPlayer($user_name, $password, $client_id);
		echo json_encode($player);
	}
?>
