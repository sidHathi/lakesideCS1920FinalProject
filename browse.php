<?php
// Siddharth Hathi
// CS4
// Final Web Assignment
// 9 December 2019

// Initializing session and passed varibles
session_start();
require("utils.php");
$db = setupDB();

if (empty($_GET["page"]))
{
    $page = 1;
}
else
{
    $page = $_GET["page"];
}

if (empty($_GET["dep"]))
{
    $dep = "any";
}
else
{
    $dep = $_GET["dep"];
}

if (empty($_GET["grade"]))
{
    $grade = 0;
}
else
{
    $grade = $_GET["grade"];
}

if (empty($_SESSION["loggedIn"]))
{
    $_SESSION["loggedIn"] = FALSE;
    $_SESSION["loggedInUser"] = "";
}


// Arrays that store data about every class
$classIds = [];
$classnames = [];
$departments = [];
$overallRatings = [];
$avgDif = [];
$hws = [];
$tests = [];

// Code that requests different classes from server depending on what parameters are passed through.
if ($_SERVER["REQUEST_METHOD"] == "POST" and !empty($_POST["search"]))
{
    $input = $_POST["search"];
    $query = "SELECT `id`, `name`, `department`, `grade` FROM shathi_classes WHERE `name` REGEXP :input;";
    $statement = $db->prepare($query);
    $statement->execute(array('input' => $input));
    $results = $statement->fetchAll();
}
else if ($_SERVER["REQUEST_METHOD"] == "POST" and (!empty($_POST["overall"]) or !empty($_POST["difficulty"])))
{
    // This code finds the average rating for every rated class and requests them based on the user's filters.
    if (($_POST["overall"] == 0) and ($_POST["difficulty"] == 0))
    {
        $query = "SELECT `id`, `name`, `department`, `grade` FROM shathi_classes;";
        $statement = $db->prepare($query);
        $statement->execute();
        $results = $statement->fetchAll();
    }
    else
    {
        $difficulty = $_POST["difficulty"];
        $overall = $_POST["overall"];

        $query = "SELECT `class_id`, `overall_rating`, `difficulty` FROM shathi_reviews;";
        $statement = $db->prepare($query);
        $statement->execute();
        $results = $statement->fetchAll();

        $IDs = [];
        $overallRs = [];
        $difficultyRs = [];

        $averageOR = [];
        $averageDR = [];

        foreach ($results as $result)
        {
            array_push($IDs, $result["class_id"]);
            array_push($overallRs, $result["overall_rating"]);
            array_push($difficultyRs, $result["difficulty"]);
        }

        foreach ($IDs as $id)
        {
            $indeces = [];
            for ($i = 0; $i < count($IDs); $i++)
            {
                if ($IDs[$i] == $id)
                {
                    array_push($indeces, $i);
                }
            }
            $totaloverall = 0;
            $totalDiff = 0;
            foreach ($indeces as $index)
            {
                $totaloverall += $overallRs[$index];
                $totalDiff += $difficultyRs[$index];
            }
            $averageORating = $totaloverall/(count($indeces));
            $averageDRating = $totalDiff/(count($indeces));
            array_push($averageOR, $averageORating);
            array_push($averageDR, $averageDRating);
        }

        $viableIDs = [];

        if (($_POST["overall"] != 0) and ($_POST["difficulty"] != 0))
        {
            for ($i = 0; $i < count($IDs); $i++)
            {
                if ((abs($averageOR[$i]-$overall) < 0.5) and (abs($averageDR[$i]-$difficulty) < 0.5))
                {
                    array_push($viableIDs, $IDs[$i]);
                }
            }
        }
        else if ($_POST["overall"] != 0)
        {
            for ($i = 0; $i < count($IDs); $i++)
            {
                if ((abs($averageOR[$i]-$overall) < 0.5))
                {
                    array_push($viableIDs, $IDs[$i]);
                }
            }
        }
        else if ($_POST["difficulty"] != 0)
        {
            for ($i = 0; $i < count($IDs); $i++)
            {
                if ((abs($averageDR[$i]-$difficulty) < 0.5))
                {
                    array_push($viableIDs, $IDs[$i]);
                }
            }
        }
        if (count($viableIDs) > 0)
        {
            $query = "SELECT `id`, `name`, `department`, `grade` FROM shathi_classes WHERE `id` IN (".implode(',',$viableIDs).")";
            $statement = $db->prepare($query);
            $statement->execute();
            $results = $statement->fetchAll();
        }
        else
        {
            $results = [];
        }
    }
}
else
{
    if (($grade != 0) and ($dep != "any"))
    {
        $query = "SELECT `id`, `name`, `department`, `grade` FROM shathi_classes WHERE `department` = :depInput AND `grade` = :gradeInput;";
        $statement = $db->prepare($query);
        $statement->execute(array('depInput' => $dep, 'gradeInput' => $grade));
    }
    else if ($dep != "any")
    {
        $query = "SELECT `id`, `name`, `department`, `grade` FROM shathi_classes WHERE `department` = :depInput;";
        $statement = $db->prepare($query);
        $statement->execute(array('depInput' => $dep));
    }
    else if ($grade != 0)
    {
        $query = "SELECT `id`, `name`, `department`, `grade` FROM shathi_classes WHERE `grade` = :gradeInput;";
        $statement = $db->prepare($query);
        $statement->execute(array('gradeInput' => $grade));
    }
    else
    {
        $query = "SELECT `id`, `name`, `department`, `grade` FROM shathi_classes;";
        $statement = $db->prepare($query);
        $statement->execute();
    }
    $results = $statement->fetchAll();
}

foreach ($results as $result)
{
    // This code populates the class arrays based on query results
    array_push($classIds, $result["id"]);
    array_push($classnames, $result["name"]);
    if ($result["department"] == "M")
    {
        array_push($departments, "MATH");
    }
    elseif ($result["department"] == "E")
    {
        array_push($departments, "ENGLISH");
    }
    elseif ($result["department"] == "L")
    {
        array_push($departments, "LANG");
    }
    elseif ($result["department"] == "A")
    {
        array_push($departments, "ART");
    }
    elseif ($result["department"] == "H")
    {
        array_push($departments, "HISTORY");
    }
    elseif ($result["department"] == "S")
    {
        array_push($departments, "SCIENCE");
    }
    else
    {
        array_push($departments, "PE");
    }
}

// Function that gets a classes reviews based on its index in the classIds array
function getReviews($i, $classIds)
{
    $db = setupDB();

    $query = "SELECT `overall_rating`, `difficulty`, `assessments`, `hw_day` FROM shathi_reviews WHERE `class_id` = :classID;";
    $statement = $db->prepare($query);
    $statement->execute(array('classID' => $classIds[$i]));
    $reviews = $statement->fetchAll();

    $ratings = [];
    $difficulties = [];
    $indTests = [];
    $hw_day = [];

    foreach ($reviews as $review)
    {
        array_push($ratings, $review["overall_rating"]);
        array_push($difficulties, $review["difficulty"]);
        array_push($indTests, $review["assessments"]);
        array_push($hw_day, $review["hw_day"]);
    }

    $a = array_filter($ratings);
    if(count($a) > 0) {
        array_push($GLOBALS["overallRatings"], array_sum($a)/count($a));
    }
    else
    {
        array_push($GLOBALS["overallRatings"], 0);
    }

    $a = array_filter($difficulties);
    if(count($a) > 0) {
        array_push($GLOBALS["avgDif"], array_sum($a)/count($a));
    }
    else
    {
        array_push($GLOBALS["avgDif"], 0);
    }

    $a = array_filter($indTests);
    if(count($a) > 0) {
        array_push($GLOBALS["tests"], array_sum($a)/count($a));
    }
    else
    {
        array_push($GLOBALS["tests"], 0);
    }

    $a = array_filter($hw_day);
    if(count($a) > 0) {
        array_push($GLOBALS["hws"], array_sum($a)/count($a));
    }
    else
    {
        array_push($GLOBALS["hws"], 0);
    }
}

// Function that builds next page buttons buttons based on the current page and the number of classes
function buildNextPageButtons($case)
{
    if ($case == 1)
    {
        ?>
        <div class="pageButtons">
            <p class="page"> Showing page <?php print($GLOBALS["page"]) ?> of <?php print(((int)(count($GLOBALS["classIds"])/10))+1) ?> </p>
            <?php $url = "browse.php?dep=" . $GLOBALS["dep"] . "&grade=" . $GLOBALS["grade"] . "&page=" . ($GLOBALS["page"]+1); 
            print("<a class='button page' href='" . $url . "'>NEXT PAGE</a>");
            ?>
        </div>
        <?php
    }
    else if ($case == 2)
    {
        ?>
        <div class="pageButtons">
            <p class="page"> Showing page <?php print($GLOBALS["page"]) ?> of <?php print(((int)(count($GLOBALS["classIds"])/10))+1) ?> </p>
            <?php $url = "browse.php?dep=" . $GLOBALS["dep"] . "&grade=" . $GLOBALS["grade"] . "&page=" . ($GLOBALS["page"]-1); 
            print("<a class='button page' href='" . $url . "'>PREV PAGE</a>");
            ?>
        </div>
        <?php
    }
    else
    {
        ?>
        <div class="pageButtons">
            <p class="page"> Showing page <?php print($GLOBALS["page"]) ?> of <?php print(((int)(count($GLOBALS["classIds"])/10))+1) ?> </p>
            <?php $url = "browse.php?dep=" . $GLOBALS["dep"] . "&grade=" . $GLOBALS["grade"] . "&page=" . ($GLOBALS["page"]+1); 
            print("<a class='button page' href='" . $url . "'>PREV PAGE</a>");
            $url = "browse.php?dep=" . $GLOBALS["dep"] . "&grade=" . $GLOBALS["grade"] . "&page=" . ($GLOBALS["page"]-1); 
            print("<a class='button page' href='" . $url . "'>NEXT PAGE</a>");
            ?>
        </div>
        <?php
    }
}

?>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Lakeside's Expanded Curriculum Guide</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel = "stylesheet" type = "text/css" href = "css/style.css" />
    <script src="https://kit.fontawesome.com/2b48f43300.js" crossorigin="anonymous"></script>

    <script>
		// Javascript!
		function updateFromDB(newName)
		{
			// The object that helps us do an AJAX request
			xmlhttp = new XMLHttpRequest();

			// Set up the 'callback function', which will be called AFTER we send the request
  			xmlhttp.onreadystatechange = function() {
				// If the operation was completed successfully...
				if (this.readyState == 4 && this.status == 200) {
					// Clear the contents of the textbox
		                	document.getElementById("textbox").value = "";
				}
			};

			// Send the request to updateNames.php with a POST parameter called newname
			xmlhttp.open("POST","updateNames.php",true);
			xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
			xmlhttp.send("newname="+newName);
		}

		function getDBContents()
		{
			xmlhttp = new XMLHttpRequest();
  			xmlhttp.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200) {
					// Use the responseText (the content printed by getDBContents.php)
					// and place it inside the <span> element "dbContents"
	                document.getElementById("dbContents").innerHTML = this.responseText;
				}
			};
			xmlhttp.open("POST","getDBContents.php",true);
			xmlhttp.send();
		}

        document.getElementById("search").addEventListener("keyup", function(event)
        {
            event.preventDefault();
            if (event.keyCode === 13)
            {
                xmlhttp = new XMLHttpRequest();
                xmlhttp.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        // Use the responseText (the content printed by getDBContents.php)
                        // and place it inside the <span> element "dbContents"
                        document.getElementById("dbContents").innerHTML = this.responseText;
                    }
                };
                xmlhttp.open("POST","getDBContents.php",true);
                xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xmlhttp.send("name="+(document.getElementById("search").value);
            }
        });

	</script>

</head>

<body>

    <div class="topnav">
        <a href ="index.php" class="home"><img src="img/logo.png" alt="logo"></a>

            <div class="navRight">
                <a class="item" href="index.php">HOME</a>
                <a class="active" href="browse.php">BROWSE</a>
                <?php
                if ($_SESSION["loggedIn"])
                {
                    ?>
                    <a class="item logOnButton" href="logOn.php?logout">LOG OUT</a>
                    <?php
                }
                else
                {?>
                    <a class="item logOnButton" href="logOn.php">LOG IN</a>
                <?php } ?>
            </div>
        </div>

<div class ="main">
    <section id="banner2">
			<div class="inner">
                <h1> EXPLORE </h1>
                <form>
                    <p> See what students have to say about Lakeside's entire course catalog:</p>
                    <br><br>
                    <div class="optionInput">
                        <p class="left"> Find classes in: </p>
                        <select id="department" onchange="location = this.value;">
                            <option value="browse.php?grade=<?php print($grade) ?>">All Departments</option>
                            <option value="browse.php?dep=M&grade=<?php print($grade) ?>" <?php
                            if ($dep == "M")
                            {
                                print("selected");
                            }
                            ?>>Math</option>
                            <option value="browse.php?dep=E&grade=<?php print($grade) ?>" <?php
                            if ($dep == "E")
                            {
                                print("selected");
                            }
                            ?>>English</option>
                            <option value="browse.php?dep=H&grade=<?php print($grade) ?>" <?php
                            if ($dep == "H")
                            {
                                print("selected");
                            }
                            ?>>History</option>
                            <option value="browse.php?dep=S&grade=<?php print($grade) ?>" <?php
                            if ($dep == "S")
                            {
                                print("selected");
                            }
                            ?>>Sciences</option>
                            <option value="browse.php?dep=L&grade=<?php print($grade) ?>" <?php
                            if ($dep == "L")
                            {
                                print("selected");
                            }
                            ?>>Languages</option>
                        </select>
                    </div>
                    <br>
                    <div>
                        <p class="left" style="margin-left: 120px;">For: </p>
                        <select id="department" onchange="location = this.value">
                            <option value="browse.php?dep=<?php print($dep) ?>">All Grades</option>
                            <option value="browse.php?grade=1&dep=<?php print($dep) ?>" <?php
                            if ($grade == 1)
                            {
                                print("selected");
                            }
                            ?>>Freshmen</option>
                            <option value="browse.php?grade=2&dep=<?php print($dep) ?>" <?php
                            if ($grade == 2)
                            {
                                print("selected");
                            }
                            ?>>Sophomores</option>
                            <option value="browse.php?grade=3&dep=<?php print($dep) ?>" <?php
                            if ($grade == 3)
                            {
                                print("selected");
                            }
                            ?>>Juniors</option>
                            <option value="browse.php?grade=4&dep=<?php print($dep) ?>" <?php
                            if ($grade == 4)
                            {
                                print("selected");
                            }
                            ?>>Seniors</option>
                        </select>
                    </div>
                </form>
                <br><br><br>
			</div>
    </section>

    <section>
    <span id="dbContents"></span>

        <div class="filters">
            <h2>filter by</h2>
            <div class="center">
            <form action="browse.php" method="POST">
                <p>Overall Rating:</p>
                    <div class="filterBlock">
                        <?php
                        $selectedOIndex = 0;
                        $selectedDIndex = 0;
                        if (($_SERVER["REQUEST_METHOD"] == "POST") and !empty($_POST["overall"]) and !empty($_POST["difficulty"]))
                        {
                            $selectedOIndex = $_POST["overall"];
                            $selectedDIndex = $_POST["difficulty"];
                        }
                        ?>
                        <input type="radio" name="overall" value="5" <?php if ($selectedOIndex == 5) {print("checked");} ?>/>
                            <p class="starHeader"> 5/5 </p>
                            <span class="fa fa-star fa-lg checked "></span>
                            <span class="fa fa-star fa-lg checked "></span>
                            <span class="fa fa-star fa-lg checked "></span>
                            <span class="fa fa-star fa-lg checked "></span>
                            <span class="fa fa-star fa-lg checked "></span>
                        <br /><br>
                        <input type="radio" name="overall" value="4" <?php if ($selectedOIndex == 4) {print("checked");} ?>/>  
                            <p class="starHeader"> 4/5 </p>
                            <span class="fa fa-star fa-lg checked "></span>
                            <span class="fa fa-star fa-lg checked "></span>
                            <span class="fa fa-star fa-lg checked "></span>
                            <span class="fa fa-star fa-lg checked "></span>
                            <span class="fa fa-star fa-lg  "></span>
                        <br/><br>
                        <input type="radio" name="overall" value="3" <?php if ($selectedOIndex == 3) {print("checked");} ?>/> 
                            <p class="starHeader"> 3/5 </p>
                            <span class="fa fa-star fa-lg checked "></span>
                            <span class="fa fa-star fa-lg checked "></span>
                            <span class="fa fa-star fa-lg checked "></span>
                            <span class="fa fa-star fa-lg  "></span>
                            <span class="fa fa-star fa-lg  "></span> 
                        <br/><br>
                        <input type="radio" name="overall" value="2" <?php if ($selectedOIndex == 2) {print("checked");} ?>/> 
                            <p class="starHeader"> 2/5 </p>
                            <span class="fa fa-star fa-lg checked "></span>
                            <span class="fa fa-star fa-lg checked "></span>
                            <span class="fa fa-star fa-lg  "></span>
                            <span class="fa fa-star fa-lg  "></span>
                            <span class="fa fa-star fa-lg  "></span>
                        <br/><br>
                        <input type="radio" name="overall" value="1" <?php if ($selectedOIndex == 1) {print("checked");} ?>/>
                            <p class="starHeader"> 1/5 </p>
                            <span class="fa fa-star fa-lg checked "></span>
                            <span class="fa fa-star fa-lg  "></span>
                            <span class="fa fa-star fa-lg  "></span>
                            <span class="fa fa-star fa-lg  "></span>
                            <span class="fa fa-star fa-lg  "></span>
                        <br/> <br>
                        <input type="radio" name="overall" value="0" <?php if ($selectedOIndex == 0) {print("checked");} ?>/>
                            <p class="starHeader"> No Filter </p>
                    </div>
                    <p>Difficulty Rating:</p>
                    <div class="filterBlock">
                        <input type="radio" name="difficulty" value="5" <?php if ($selectedDIndex == 5) {print("checked");} ?>/>
                            <p class="starHeader"> 5/5 </p>
                            <span class="fa fa-circle fa-lg checkedR "></span>
                            <span class="fa fa-circle fa-lg checkedR "></span>
                            <span class="fa fa-circle fa-lg checkedR "></span>
                            <span class="fa fa-circle fa-lg checkedR "></span>
                            <span class="fa fa-circle fa-lg checkedR "></span>
                        <br /><br>
                        <input type="radio" name="difficulty" value="4" <?php if ($selectedDIndex == 4) {print("checked");} ?>/>  
                            <p class="starHeader"> 4/5 </p>
                            <span class="fa fa-circle fa-lg checkedR "></span>
                            <span class="fa fa-circle fa-lg checkedR "></span>
                            <span class="fa fa-circle fa-lg checkedR "></span>
                            <span class="fa fa-circle fa-lg checkedR "></span>
                            <span class="fa fa-circle fa-lg  "></span>
                        <br/><br>
                        <input type="radio" name="difficulty" value="3" <?php if ($selectedDIndex == 3) {print("checked");} ?>/> 
                            <p class="starHeader"> 3/5 </p>
                            <span class="fa fa-circle fa-lg checkedR "></span>
                            <span class="fa fa-circle fa-lg checkedR "></span>
                            <span class="fa fa-circle fa-lg checkedR "></span>
                            <span class="fa fa-circle fa-lg  "></span>
                            <span class="fa fa-circle fa-lg  "></span>
                        <br/><br>
                        <input type="radio" name="difficulty" value="2" <?php if ($selectedDIndex == 2) {print("checked");} ?>/> 
                            <p class="starHeader"> 2/5 </p>
                            <span class="fa fa-circle fa-lg checkedR "></span>
                            <span class="fa fa-circle fa-lg checkedR "></span>
                            <span class="fa fa-circle fa-lg  "></span>
                            <span class="fa fa-circle fa-lg  "></span>
                            <span class="fa fa-circle fa-lg  "></span>
                        <br/><br>
                        <input type="radio" name="difficulty" value="1" <?php if ($selectedDIndex == 1) {print("checked");} ?>/>
                            <p class="starHeader"> 1/5 </p>
                            <span class="fa fa-circle fa-lg checkedR "></span>
                            <span class="fa fa-circle fa-lg  "></span>
                            <span class="fa fa-circle fa-lg  "></span>
                            <span class="fa fa-circle fa-lg  "></span>
                            <span class="fa fa-circle fa-lg  "></span>
                        <br/><br>
                        <input type="radio" name="difficulty" value="0" <?php if ($selectedDIndex == 0) {print("checked");} ?>/>
                            <p class="starHeader"> No Filter </p>
                    </div>
                <input value="APPLY" type="submit">
            </form>
            </div>
        </div>
        <div class= "items">
            <div class="fullSearchbox">
            <form action="browse.php" method="POST">
                <input id="search" name="search" type="text" class="hulkingSearchBar" placeholder="Seach for classes by name">
            </form>
            </div>
            <br>
            <div class="classesList">
                <?php
                $lastIndex = $page*10-1;
                if ($page*10 > count($classIds))
                {
                    $lastIndex = count($classIds);
                }
                else
                {
                    $lastIndex = ($page*10)-1;
                }
                for ($i = ($page-1)*10; $i < $lastIndex; $i++)
                {
                    getReviews($i, $classIds);
                    $rIndex = $i-(($page-1)*10); 
                ?>
                    <div class="classInList">
                        <div class="leftStuff">
                            <a href="class.php?classid=<?php print($classIds[$i]) ?>" class="titleLink"><h2 class="classTitle"> <?php print($classnames[$i]) ?> </h2></a>
                            <p class="subtext"> <?php print($departments[$i]) ?> </p>

                            <p class="subheading"> overall rating: <strong><?php print(round($overallRatings[$rIndex])) ?>/5</strong></p>
                            <?php
                            for ($j = 0; $j < round($overallRatings[$rIndex]); $j++)
                            { ?>
                            <span class="fa fa-star checked shifted"></span>
                            <?php 
                            } ?>
                            <?php
                            for ($j = 0; $j < (5-round($overallRatings[$rIndex])); $j++)
                            { ?>
                            <span class="fa fa-star shifted"></span>
                            <?php 
                            } ?>
                            <br>
                            <p class="subheading"> difficulty: <strong><?php print(round($avgDif[$rIndex], 1)) ?>/5</strong></p>
                            <?php
                            for ($j = 0; $j < round($avgDif[$rIndex]); $j++)
                            { ?>
                            <span class="fa fa-circle checkedR shifted"></span>
                            <?php 
                            } ?>
                            <?php
                            for ($j = 0; $j < (5-round($avgDif[$rIndex])); $j++)
                            { ?>
                            <span class="fa fa-circle shifted"></span>
                            <?php 
                            } ?>
                            
                        </div>
                        <div class="quickStats">

                            <p class="statHeader"> reported homework per day:</p>
                            <h2 class="stat"> <?php print(round($hws[$rIndex])) ?> min </h2>
                            
                            <p class="statHeader"> reported assesments per semester:</p>
                            <h2 class="stat"> <?php print(round($tests[$rIndex])) ?> </h2>
                        </div>
                    </div>

                <?php }

                if (($page)*10 < (count($classIds)))
                {
                    if (($page-1) == 0)
                    {
                        buildNextPageButtons(1);
                    }
                    else
                    {
                        buildNextPageButtons(3);
                    }
                }
                else
                {
                    if (($page-1) > 0)
                    {
                        buildNextPageButtons(2);
                    }
                }
                ?>
        </div>

        
    </section>

</div>

</body>

</html>