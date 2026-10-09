<?
//
// config.inc -- edit this file and nothing else.
//
// Dave's Message Board v1.2
// Requires PHP 3.0 and MySQL 3.22 or better.
//

$DB_HOST  = "db";
$DB_USER  = "board";
$DB_PASS  = "board98";
$DB_NAME  = "board";

$BOARD_NAME  = "Dave's Message Board";
$BOARD_ADMIN = "dave@davespage.geocities.com";

// md5 of the admin password. Default password is: letmein
$ADMIN_PASS = "0d107d09f5bbe40cade3de5c71e9e9b7";

$PER_PAGE    = 15;   // threads per page on the index
$FLOOD_SECS  = 30;   // seconds a visitor must wait between posts
$SESS_LIFE   = 1800; // seconds before an idle session is thrown away
?>
