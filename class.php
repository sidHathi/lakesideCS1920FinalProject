<?php
// Siddharth Hathi
// CS4
// Final Web Assignment
// 9 December 2019

session_start();
require("utils.php");
$db = setupDB();

// Variables that store class info
$classID = 1;
$classname = "";
$department = "";
$grade = 0;
$description = "";

// Varibles that store ratings info
$user_ids = [];
$overall_ratings = [];
$difficulties = [];
$teachingRs = [];
$contentRs = [];
$funRs = [];
$projectRs = [];
$hwRs = [];
$testRs = [];
$reviewTexts = [];
$user_names = [];

if (empty($_SESSION["loggedIn"]))
{
    $_SESSION["loggedIn"] = FALSE;
    $_SESSION["loggedInUser"] = "";
}

if ($_SERVER["REQUEST_METHOD"] == "GET")
{
    if (!empty($_GET["classid"]))
    {
        $classID = $_GET["classid"];
        $query = "SELECT `name`, `department`, `grade`, `description` FROM shathi_classes WHERE `id`= :classInput";
		$statement = $db->prepare($query);
		$statement->execute(array('classInput' => $classID));
        $results = $statement->fetchAll();
        foreach ($results as $result)
        {
            $classname = htmlspecialchars($result["name"]);
            $department = htmlspecialchars($result["department"]);
            $grade = htmlspecialchars($result["grade"]);
            $description = htmlspecialchars($result["description"]);
        }

        $query = "SELECT `user_id`, `overall_rating`, `difficulty`, `teaching`, `content`, `fun`, `projects`, `hw_day`, `assessments`, `review_text` FROM shathi_reviews WHERE `class_id` = :classInput;";
        $statement = $db->prepare($query);
        $statement->execute(array('classInput' => $classID));
        $results = $statement->fetchAll();

        foreach ($results as $result)
        {
            $user_id = $result["user_id"];
            array_push($user_ids, $user_id);
            $overall_rating = $result["overall_rating"];
            array_push($overall_ratings, $overall_rating);
            $difficulty = $result["difficulty"];
            array_push($difficulties, $difficulty);
            $teaching = $result["teaching"];
            array_push($teachingRs, $teaching);
            $content = $result["content"];
            array_push($contentRs, $content);
            $fun = $result["fun"];
            array_push($funRs, $fun);
            $project = $result["projects"];
            array_push($projectRs, $project);
            $hw_day = $result["hw_day"];
            array_push($hwRs, $hw_day);
            $test = $result["assessments"];
            array_push($testRs, $test);
            $review_text = $result["review_text"];
            array_push($reviewTexts, $review_text);
        }

        foreach ($user_ids as $id)
        {
            $query = "SELECT `username` FROM shathi_users WHERE `id` = :idInput;";
            $statement = $db->prepare($query);
            $statement->execute(array('idInput' => $id));
            $results = $statement->fetchAll();

            $username = "";
            foreach ($results as $result)
            {
                $username = $result["username"];
            }
            array_push($user_names, $username);
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

	<title>Lakeside's Expanded Curriculum Guide</title>
    <link rel = "stylesheet" type = "text/css" href = "css/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">


</head>

<body>
        <div class="topnav">
        <a href ="index.php" class="home"><img src="img/logo.png" alt="logo"></a>

            <div class="navRight">
                <a class="item" href="index.php">HOME</a>
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

        <div class = "main">

            <section id="banner" class="img2">
                <div class="inner">
                        <h1 class="classHeader"> <?php print($classname) ?> </h1>
                        <a href="#reviews"><h3 class="right">(<?php print(count($user_ids)) ?> ratings)</h3></a>
                </div>
                <div class="classStats">
                    <div class="statHead">
                        <h3 class="statsTitle"> quick stats <h3>
                    </div>
                    <br>
                    <div class="statNames">
                        <p class = statMainName> homework/day </p>
                        <p class = statMainName> tests/term </p>
                        <p class = statMainName> projects/term </p>
                    </div>

                    <div class="quickStatsMain">
                        <?php

                        if (count($user_ids) > 0)
                        {
                            $averagehw = 0;
                            $averagetest = 0;
                            $averageproject = 0;
                            $a = array_filter($hwRs);
                            if(count($a)) {
                                $averagehw = array_sum($a)/count($a);
                            }
                            $a = array_filter($testRs);
                            if(count($a)) {
                                $averagetest = array_sum($a)/count($a);
                            }
                            $a = array_filter($projectRs);
                            if(count($a)) {
                                $averageproject = array_sum($a)/count($a);
                            }
                        ?>
                        <p class="statMain"> <?php print(round($averagehw, 1)); ?> min </p>
                        <p class="statMain"> <?php print(round($averagetest, 1)); ?> </p>
                        <p class="statMain"> <?php print(round($averageproject, 1)); ?> </p>
                        <?php } 
                        else
                        { ?>
                        <p class="statMain"> unavailable </p>
                        <p class="statMain"> unavailable </p>
                        <p class="statMain"> unavailable </p>
                        <?php } ?>
                    </div>
                </div>
            </section>

            
            <div class="container">
            <?php
            if (count($user_ids) > 0)
            {
                $averageOverall = 0;
                $averageDiff = 0;
                $averageTeach = 0;
                $averageCont = 0;
                $averageFun = 0;
                $a = array_filter($overall_ratings);
                if(count($a)) {
                    $averageOverall = array_sum($a)/count($a);
                }
                $a = array_filter($difficulties);
                if(count($a)) {
                    $averageDiff = array_sum($a)/count($a);
                }
                $a = array_filter($teachingRs);
                if(count($a)) {
                    $averageTeach = array_sum($a)/count($a);
                }
                $a = array_filter($contentRs);
                if(count($a)) {
                    $averageCont = array_sum($a)/count($a);
                }
                $a = array_filter($funRs);
                if(count($a)) {
                    $averageFun = array_sum($a)/count($a);
                }
            ?>
                <div class = "left">
                     <header class="major">
                        <h3>overall rating</h3>
                     </header>
                     <h1 class="hulkingNumber green"> <?php print(round($averageOverall, 1)) ?> </h1>	

                     <div class="center">
                     <?php
                    for ($j = 0; $j < round($averageOverall); $j++)
                    { ?>
                    <span class="fa fa-star fa-2x checked shifted big"></span>
                    <?php 
                    } ?>
                    <?php
                    for ($j = 0; $j < (5-round($averageOverall)); $j++)
                    { ?>
                    <span class="fa fa-star fa-2x shifted big"></span>
                    <?php 
                    } ?>
                    </div>
                    <br><br><br>
               </div>

                <div class="right">
                    <header class="major">
                        <h3>individual ratings</h3>
                    </header>
                        <hr>
                        <div class="leftMini">
                            <h1 class="petiteNumber yellow"> <?php print(round($averageDiff, 0)) ?> </h1>
                            <p class="individualRanking">DIFFICULTY</p>
                            <h1 class="petiteNumber yellow"> <?php print(round($averageTeach, 0)) ?> </h1>
                            <p class="individualRanking">TEACHING</p>
                        </div>
                        <div class="rightMini">
                            <h1 class="petiteNumber yellow"> <?php print(round($averageCont, 0)) ?> </h1>
                            <p class="individualRanking padded">CONTENT</p>
                            <h1 class="petiteNumber yellow"> <?php print(round($averageFun, 0)) ?> </h1>
                            <p class="individualRanking padded">FUN</p>
                        </div>  
                </div>
            <?php }
            else {
            ?>
                <br>
                <p> There are no reviews for this class. </p>
                <br>
                <?php
            } ?>
           </div>


            <br><br>
           <header class="major nopadding">
				<h2>user reviews</h2>
            </header>	

           <section id="reviews">
                <div class= "items review">
                    <br>
                    <div class="classesList">
                            <div class="writeReview">
                                <p class="subheading"> <?php print(count($user_ids)) ?> reviews </p>
                                <?php $url = "reviewform.php?classid=" . $classID; 
                                print("<a class='button' href='" . $url . "'> WRITE A REVIEW </a>")
                                ?>
                            </div>
                            <hr>

                            <?php
                            for ($i = 0; $i < count($user_ids); $i++)
                            {
                            ?>
                            <div class="reviewCard">
                                <div class="ratings">
                                    <div class="leftStuff">
                                        <h2 class="classTitle"> <?php print($user_names[$i]); ?> </h2>

                                        <div class="overallRating">
                                            <p class="subheading"> overall rating: <strong><?php print($overall_ratings[$i]) ?>/5</strong></p>
                                            <?php
                                            for ($j = 0; $j < $overall_ratings[$i]; $j++)
                                            { ?>
                                            <span class="fa fa-star fa-lg checked shifted"></span>
                                            <?php 
                                            } ?>
                                            <?php
                                            for ($j = 0; $j < (5-$overall_ratings[$i]); $j++)
                                            { ?>
                                            <span class="fa fa-star fa-lg shifted"></span>
                                            <?php 
                                            } ?>
                                        </div>

                                        <br>

                                        <div class = "reviewGrid">                                    
                                            <p class="subheading"> difficulty: <strong><?php print($difficulties[$i]) ?>/5</strong></p>
                                            <?php
                                            for ($j = 0; $j < $difficulties[$i]; $j++)
                                            { ?>
                                            <span class="fa fa-circle fa-lg checkedR shifted"></span>
                                            <?php 
                                            } ?>
                                            <?php
                                            for ($j = 0; $j < (5-$difficulties[$i]); $j++)
                                            { ?>
                                            <span class="fa fa-circle fa-lg shifted"></span>
                                            <?php 
                                            } ?>
                                        </div>

                                        <div class="reviewGrid">
                                            <p class="subheading"> teaching: <strong><?php print($teachingRs[$i]) ?>/5</strong></p>
                                            <?php
                                            for ($j = 0; $j < $teachingRs[$i]; $j++)
                                            { ?>
                                            <span class="fa fa-star fa-lg checked shifted"></span>
                                            <?php 
                                            } ?>
                                            <?php
                                            for ($j = 0; $j < (5-$teachingRs[$i]); $j++)
                                            { ?>
                                            <span class="fa fa-star fa-lg shifted"></span>
                                            <?php 
                                            } ?>
                                        </div>

                                        <div class="reviewGrid">
                                            <p class="subheading"> class content: <strong><?php print($contentRs[$i]) ?>/5</strong></p>
                                            <?php
                                            for ($j = 0; $j < $contentRs[$i]; $j++)
                                            { ?>
                                            <span class="fa fa-star fa-lg checked shifted"></span>
                                            <?php 
                                            } ?>
                                            <?php
                                            for ($j = 0; $j < (5-$contentRs[$i]); $j++)
                                            { ?>
                                            <span class="fa fa-star fa-lg shifted"></span>
                                            <?php 
                                            } ?>
                                        </div>


                                        <div class="reviewGrid last">
                                            <p class="subheading"> fun: <strong><?php print($funRs[$i]) ?>/5</strong></p>
                                            <?php
                                            for ($j = 0; $j < $funRs[$i]; $j++)
                                            { ?>
                                            <span class="fa fa-star fa-lg checked shifted"></span>
                                            <?php 
                                            } ?>
                                            <?php
                                            for ($j = 0; $j < (5-$funRs[$i]); $j++)
                                            { ?>
                                            <span class="fa fa-star fa-lg shifted"></span>
                                            <?php 
                                            } ?>
                                        </div>
                                        
                                    </div>
                                    <div class="quickStats">

                                        <p class="statHeader"> reported homework per day:</p>
                                        <h2 class="stat"> <?php 
                                            print($hwRs[$i])
                                         ?> min </h2>
                                        
                                        <p class="statHeader"> reported assesments per semester:</p>
                                        <h2 class="stat"> <?php 
                                            print($testRs[$i]);
                                         ?> </h2>
                                    </div>

                                </div>

                                <div class="reviewText">
                                    <p> <?php print($reviewTexts[$i]); ?> </p>
                                </div>
                            </div>

                            <?php }

                            if (count($user_ids) == 0)
                            {
                                ?>
                                <p> There are no reviews for this class. </p>
                                <br>
                                <?php
                            }
                            ?>
                    </div>
                </div>
                
            </section>

        </div>

</body>

</html>