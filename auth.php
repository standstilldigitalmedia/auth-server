<?php
	require_once("SSDMPlayer.php");
	

	header("Content-Type: application/json");

	//Examples to remind me once I am no longer testing in a browser
	/*
	

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
	
	/*$request_type = -1;
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
		$player = new SSDMPlayer(null);
		$player->error = "Invalid input method";
		echo json_encode($player);
		return;
	}*/

	/*$incoming_player = new stdClass();
	if(isset($request_type))
		$incoming_player->request_type = $request_type;
	if(isset($user_name))
		$incoming_player->user_name = $user_name;
	if(isset($display_name))
		$incoming_player->display_name = $display_name;
	if(isset($email))
		$incoming_player->email = $email;
	if(isset($password))
		$incoming_player->password = $password;
	if(isset($client_id))
		$incoming_player->client_id = $client_id;
	if(isset($player_id))
		$incoming_player->player_id = $player_id;
	if(isset($session_ticket))
		$incoming_player->session_ticket = $session_ticket;*/

	/********************* 
	 * End Test Code
	 * *******************/
	$incoming_player = json_decode(file_get_contents('php://input'), false);
	$player = new SSDMPlayer($incoming_player);
	echo json_encode($player);
?>
