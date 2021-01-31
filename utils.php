<?php
function setupDB()
{
	$db = new PDO(
		"mysql:dbname=1920project;host=mysql.1920.lakeside-cs.org", 
		"student1920", "m545CS41920");
		
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
						   
	return $db;
}
?>
