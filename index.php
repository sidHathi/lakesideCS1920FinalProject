<?php
// Siddharth Hathi
// CS4
// Final Web Assignment
// 9 December 2019

// php session configuration
session_Start();

require("utils.php");
$db = setupDB();

if (empty($_SESSION["loggedIn"]))
{
    $_SESSION["loggedIn"] = FALSE;
    $_SESSION["loggedInUser"] = "";
}

// Fetches data from server
$query = "SELECT `id`, `name` FROM shathi_classes;";
$statement = $db->prepare($query);
$statement->execute();
$results = $statement->fetchAll();

createXMLfile($results);

// function that builds an xml file for the search bar suggestions
function createXMLfile($results){
		
    $filePath = 'links.xml';
    $dom     = new DOMDocument('1.0', 'utf-8'); 
    $root      = $dom->createElement('pages'); 
    foreach ($results as $result)
    {
        $className = iconv('UTF-8', 'ASCII//TRANSLIT', htmlspecialchars($result["name"]));
        $classID = htmlspecialchars($result["id"]);

        $link = $dom->createElement('link');
        $title     = $dom->createElement('title', $className); 
        $link->appendChild($title);
        $url     = $dom->createElement('url', "class.php?classid=" . $classID); 
        $link->appendChild($url);
    
        $root->appendChild($link);
    }
    $dom->appendChild($root); 
    $dom->save($filePath); 
} 

// Initializes arrays for storing class data
$classIds = [];

$classnames = [];
$classDeps = [];
$overallRatings = [];
$difficulties = [];
$teachingRs = [];
$contentRs = [];
$funRs = [];

// gets data on popular classes from database
$query = "SELECT `class_id` FROM shathi_reviews;";
$statement = $db->prepare($query);
$statement->execute();
$results = $statement->fetchAll();

foreach ($results as $result)
{
    array_push($classIds, $result["class_id"]);
}

$values = array_count_values($classIds);
arsort($values);
$popular = array_slice(array_keys($values), 0, 9, true);

// Initializes arrays
if (count($popular) > 0)
{
    foreach ($popular as $id)
    {
        $query = "SELECT `name`, `department` FROM shathi_classes WHERE `id` = :idInput";
        $statement = $db->prepare($query);
        $statement->execute(array('idInput' => $id));
        $results = $statement->fetchAll();

        foreach ($results as $result)
        {
            array_push($classnames, $result["name"]);
            array_push($classDeps, $result["department"]);
        }

        $query = "SELECT `overall_rating`, `difficulty`, `teaching`, `content`, `fun` FROM shathi_reviews WHERE `class_id` = :idInput;";
        $statement = $db->prepare($query);
        $statement->execute(array('idInput' => $id));
        $results = $statement->fetchAll();
        
        $totalOverall = 0;
        $totalDiff = 0;
        $totalTeach = 0;
        $totalCont = 0;
        $totalFun = 0;

        foreach ($results as $result)
        {
            $totalOverall += $result["overall_rating"];
            $totalDiff += $result["difficulty"];
            $totalTeach += $result["teaching"];
            $totalCont += $result["content"];
            $totalFun += $result["fun"];
        }

        $averageOverall = $totalOverall/count($results);
        array_push($overallRatings, $averageOverall);
        $averageDiff = $totalDiff/count($results);
        array_push($difficulties, $averageDiff);
        $averageTeach = $totalTeach/count($results);
        array_push($teachingRs, $averageTeach);
        $averageCont = $totalCont/count($results);
        array_push($contentRs, $averageCont);
        $averageFun = $totalFun/count($results);
        array_push($funRs, $averageFun);
    }
}

// Function that returns a string version of the department shorthand
function getDepartment($dep)
{
    if ($dep == "M")
    {
        return "MATH";
    }
    else if ($dep == "E")
    {
        return "ENGLISH";
    }
    else if ($dep == "S")
    {
        return "SCIENCE";
    }
    else if ($dep == "H")
    {
        return "HISTORY";
    }
    else if ($dep == "A")
    {
        return "ART";
    }
    else
    {
        return "LANG";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <script>
        // Javascript from w3 sample
		function showResult(str) {
			if (str.length==0) {
				document.getElementById("livesearch").innerHTML="";
				document.getElementById("livesearch").style.border="0px";
				return;
			}
			if (window.XMLHttpRequest) {
				// code for IE7+, Firefox, Chrome, Opera, Safari
				xmlhttp=new XMLHttpRequest();
			} else {  // code for IE6, IE5
				xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
			}
			xmlhttp.onreadystatechange=function() {
				if (this.readyState==4 && this.status==200) {
				document.getElementById("livesearch").innerHTML=this.responseText;
				document.getElementById("livesearch").style.border="1px solid #A5ACB2";
				}
			}
			xmlhttp.open("GET","livesearch.php?q="+str,true);
			xmlhttp.send();
		}
	</script>

    <meta charset="UTF-8">

	<title>Lakeside's Expanded Curriculum Guide</title>
	<link rel = "stylesheet" type = "text/css" href = "css/style.css" />

</head>

<body>

    <div class="topnav">
        <a href ="index.php" class="home"><img src="img/logo.png" alt="logo"></a>

        <div class="navRight">
            <a class="active" href="index.php">HOME</a>
            <a class="item" href="browse.php">BROWSE</a>
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

    <div class="main">

        <section id="banner" class="img1">
            <div class="inner">
                    <h1> RETHINK LAKESIDE</h1>
                    <h3>Read what other students think about your classes <br><br><strong>before</strong> you sign up for them. </h3>
                    <br><br>
                    <div class="searchbox" id="searchbox">
                        <form method="POST" action="browse.php">
                            <input name="search" type="text" class="searchBar" placeholder="Enter the name of a class, department or teacher at Lakeside" onkeyup="showResult(this.value)">
                            <div id="livesearch"></div>
                        </form>
                    </div>
            </div>
        </section>

        <section id="browse" class="centerSection">
            <div class="container">
                <header class="major">
					<h2>top classes</h2>
                </header>	

                <?php
                if (count($classnames) > 0)
                {
                    if (count($classnames) >= 3)
                    {
                        ?>
                        <div class="topRow">
                            <?php
                            for ($i = 0; $i < 3; $i++)
                            {
                            ?>
                            <div class="card">
                                <div class="popularClass card<?php print($classDeps[$i]) ?>">
                                    <div class="cardInner">
                                        <h2 class="cardHeader"> <?php print($classnames[$i]); ?> </h2>
                                        <p class="cardSubHead"> <?php print(getDepartment($classDeps[$i])); ?> </p>
                                        <br>
                                        <p class="cardRating"> overall rating: <strong><?php print(round($overallRatings[$i])) ?>/5</strong></p>
                                        <p class="cardRating"> difficulty: <strong><?php print(round($difficulties[$i])) ?>/5</strong></p>
                                        <p class="cardRating"> content rating: <strong><?php print(round($contentRs[$i])) ?>/5</strong></p>
                                        <p class="cardRating"> teaching: <strong><?php print(round($teachingRs[$i])) ?>/5</strong></p>
                                        <p class="cardRating"> fun: <strong><?php print(round($funRs[$i])) ?>/5</strong></p>
                                        <br><br><br><br>
                                        <a href="class.php?classid=<?php print($popular[$i]) ?>" class="button"> Learn More </a>
                                    </div>
                                </div>
                            </div>
                            <?php
                            } ?>
                        </div>
                        <?php
                    }
                    else
                    {
                        print("<p>Insufficient Data</p>");
                    }

                    if (count($classnames) >= 6)
                    {
                        ?>
                        <div class="topRow">
                            <?php
                            for ($i = 3; $i < 6; $i++)
                            {
                            ?>
                            <div class="card">
                                <div class="popularClass card<?php print($classDeps[$i]) ?>">
                                    <div class="cardInner">
                                        <h2 class="cardHeader"> <?php print($classnames[$i]); ?> </h2>
                                        <p class="cardSubHead"> <?php print(getDepartment($classDeps[$i])); ?> </p>
                                        <br>
                                        <p class="cardRating"> overall rating: <strong><?php print(round($overallRatings[$i])) ?>/5</strong></p>
                                        <p class="cardRating"> difficulty: <strong><?php print(round($difficulties[$i])) ?>/5</strong></p>
                                        <p class="cardRating"> content rating: <strong><?php print(round($contentRs[$i])) ?>/5</strong></p>
                                        <p class="cardRating"> teaching: <strong><?php print(round($teachingRs[$i])) ?>/5</strong></p>
                                        <p class="cardRating"> fun: <strong><?php print(round($funRs[$i])) ?>/5</strong></p>
                                        <br><br><br><br>
                                        <a href="class.php?classid=<?php print($popular[$i]) ?>" class="button"> Learn More </a>
                                    </div>
                                </div>
                            </div>
                            <?php
                            } ?>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </section>      

            <br><br><br>

            <!-- Two -->
			<section id="banner2">
				<div class="inner">
                    <h1> EXPLORE </h1>
                    <br><br>
                    <p> See what students have to say about Lakeside's entire course catalog:</p>
                    <p> Find the classes you're interested in. </p>
                    <br><br><br>
                    <a href="browse.php" class="button"> Learn More </a>

				</div>
            </section>
            
            <br><br>

            <section id="review">
                <div class="container">
                    <header class="major">
                        <h2>about Lakeside</h2>
                        <br>
                        <img src="img/randoLakeside3.jpg" alt="pic of Lakeside" id="lakeside"/>
                        <img src="img/red-square.jpg" alt="pic of Lakeside" id="lakeside"/>
                        <br>
                        <div class="imgDescription">
                        <hr>
                        <p>"The mission of Lakeside School is to develop in intellectually capable young people the creative minds, healthy bodies, and ethical spirits needed to contribute wisdom, compassion, and leadership to a global society. 
                            We provide a rigorous and dynamic academic program through which effective educators lead students to take responsibility for learning.We are committed to sustaining a school in which individuals representing diverse 
                            cultures and experiences instruct one another in the meaning and value of community and in the joy and importance of lifelong learning." -Bernie Noe, Head of School </p>
                        </div>
                        <br><br>
                        <a href="https://www.lakesideschool.org/" class="button"> Learn More </a>
                    </header>	
                </div>
            </section>

            <br><br>

            <div class="copyright">
				&copy; Siddharth Hathi 2019. Images: <a href="https://lakesideschool.org/">Lakeside</a>
			</div>


        <br><br>

    </div>

    <!-- SCRIPTS -->
    <script src="js/jquery.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.stellar.min.js"></script>
    <script src="js/wow.min.js"></script>
    <script src="js/owl.carousel.min.js"></script>
    <script src="js/jquery.magnific-popup.min.js"></script>
    <script src="js/smoothscroll.js"></script>
    <script src="js/custom.js"></script>

</body>

<html>