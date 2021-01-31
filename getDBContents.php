<?php
	require("utils.php");
	
	$db = setupDB();

	if ($_SERVER["REQUEST_METHOD"] == "POST")
	{
		if (!empty($_POST["name"]))
		{
			$query = "SELECT `name`, `department`, `grade`, `description` FROM shathi_classes"; 
			$statement = $db->prepare($query);
			$statement->execute(array('input' => $_POST["name"]));
			$rows = $statement->fetchAll();
			foreach ($rows as $row)
			{
				print($row['name'] . "<br/>");
			}
		}
	}
?>
