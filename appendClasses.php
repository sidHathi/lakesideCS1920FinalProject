<?php
require("utils.php");

$db = setupDB();

$txt_file = file_get_contents("everyClass.txt");
$rows = explode("--------------", $txt_file);
array_shift($rows);

$_classes = [];
for ($i=0; $i<(count($rows)/2 - 1); $i++)
{
    $classes[$i] = [$rows[2*$i], $rows[2*$i+1]];
}

//print_r($classes);

foreach ($classes as $class)
{
    $classid = explode(" ", $class[0]);
    array_shift($classid);

    $brokenid = str_split($classid[0]);

    //print_r($brokenid);

    $depid = $brokenid[0];
    //print($depid);

    $gradeid = $brokenid[1];
    //print($gradeid);

    unset($classid[0]);

    $name = join(" ", $classid);
    //print($name);

    $description = iconv('UTF-8', 'ASCII//TRANSLIT', $class[1]);

    if ($depid == "M")
    {
        $gradeid = 0;
    }
    else
    {
        $gradeid = (int)$gradeid;
    }

    $query = "INSERT INTO shathi_classes(`name`, `department`, `grade`, `description`) VALUES (:nameinput, :depInput, :gradeInput, :desInput);";
    $statement = $db->prepare($query);
    $statement->execute(array('nameinput' => $name, 'depInput' => $depid, 'gradeInput' => $gradeid, 'desInput' => $description));

}

?>