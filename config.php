<?php
	session_start();

	define('API_URL_BASE', 'localhost');
	
	define('MAX_NAME_LENGTH', 30);
	define('MAX_EMAIL_LENGTH', 255);
	define('MIN_NAME_LENGTH', 6);
	define('MIN_PASSWORD_LENGTH', 8);
	define('MAX_CLIENT_ID_LENGTH', 255);

	define("ID_TICKET_LENGTH", 16);
	define('REGISTER_TICKET_LENGTH', 6);
	define('LOGIN_TICKET_LENGTH', 16);
	define('REMEMBER_ME_TICKET_LENGTH', 16);


	define('ACTIVATE_EXPIRATION_DATE', '+15 minutes');
	define('REMEMBER_ME_EXPIRATION_DATE', '+1 month');
	define('LOGIN_EXPIRATION_DATE', '+2 minutes');

	define("REGISTER_SESSION", 1);
	define("ACTIVATE_SESSION", 2);
	define("LOGIN_SESSION", 3);
	define("REMEMBER_ME_SESSION", 4);

	enum QueryType
	{
		case Insert;
		case Select;
		case Delete;
		case Update;
	}
?>