<?php
// Siddharth Hathi
// CS4
// Final Web Assignment
// 9 December 2019

session_start();

require("utils.php");
$db = setupDB();

$displayErrorMessage = FALSE;

$classID = 1;
$classname = "";
$department = "";
$grade = 0;
$description = "";

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
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    if (!empty($_GET["classid"]) and !empty($_POST["reviewText"]))
    {
        if ($_SESSION["loggedIn"])
        {
            $userID = 0;
            $currentUser = htmlspecialchars($_SESSION["loggedInUser"]);
            $query = "SELECT `id` FROM shathi_users WHERE `username` = :nameInput;";
            $statement = $db->prepare($query);
            $statement->execute(array('nameInput' => $currentUser));
            $results = $statement->fetchAll();
            
            foreach ($results as $result)
            {
                $userID = $result["id"];
            }
            $classID = $_GET["classid"];
            $hwperday = $_POST["hwperday"];
            $proj = $_POST["projects"];
            $tests = $_POST["tests"];
            $reviewText = $_POST["reviewText"];
            $overallRating = $_POST["overall"];
            $difficulty = $_POST["difficulty"];
            $teaching = $_POST["teaching"];
            $content = $_POST["content"];
            $fun = $_POST["fun"];
            $query = "INSERT INTO shathi_reviews(`user_id`, `class_id`, `overall_rating`, `difficulty`, `teaching`, `content`, `fun`, `projects`, `hw_day`, `assessments`, `review_text`) VALUES (:userInput, :classInput, :overallInput, :diffInput, :teachInput, :contentInput, :funInput, :projInput, :hwInput, :testInput, :textInput);";
            $statement = $db->prepare($query);
            $statement->execute(array('userInput' => $userID, 'classInput' => $classID, 'overallInput' => $overallRating, 'diffInput' => $difficulty, 'teachInput' => $teaching, 'contentInput' => $content, 'funInput' => $fun, 'projInput' => $proj, 'hwInput' => $hwperday, 'testInput' => $tests, 'textInput' => $reviewText));
            $url = "class.php?classid=" . $classID;
            header("Location: " . $url);
            
        }
        else
        {
            header("Location: logOn.php");
        }
    }
}
else
{
    if (!$_SESSION["loggedIn"])
    {
        header("Location: logOn.php");
    }
}

?>

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

        <div class="logOnMain">
            <div class="container logOn">
 
                <header class="major nopadding">
                    <h2>write a review</h2>
                    <p class="subtext align-center"> <?php print($classname) ?> </p>
                </header>

                <br><br>
                <form action="reviewform.php?classid=<?php print($classID) ?>" method="POST">
                    <div class="reviewFormDiv">
                        <div class="starInputs">
                            <div class="reviewFormBlock">
                                <p class="subheading"> Overall Rating </p>
                                <input type="radio" name="overall" value="5" checked/>
                                    <p class="starHeader"> 5/5 </p>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg checked "></span>
                                <br /><br>
                                <input type="radio" name="overall" value="4" />  
                                    <p class="starHeader"> 4/5 </p>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg  "></span>
                                <br/><br>
                                <input type="radio" name="overall" value="3" /> 
                                    <p class="starHeader"> 3/5 </p>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg  "></span>
                                    <span class="fa fa-star fa-lg  "></span> 
                                <br/><br>
                                <input type="radio" name="overall" value="2" /> 
                                    <p class="starHeader"> 2/5 </p>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg  "></span>
                                    <span class="fa fa-star fa-lg  "></span>
                                    <span class="fa fa-star fa-lg  "></span>
                                <br/><br>
                                <input type="radio" name="overall" value="1" />
                                    <p class="starHeader"> 1/5 </p>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg  "></span>
                                    <span class="fa fa-star fa-lg  "></span>
                                    <span class="fa fa-star fa-lg  "></span>
                                    <span class="fa fa-star fa-lg  "></span>
                                <br/>
                            </div>

                            <div class="reviewFormBlock">
                                <p class="subheading"> Class Difficulty </p>
                                <input type="radio" name="difficulty" value="5" checked/>
                                    <p class="starHeader"> 5/5 </p>
                                    <span class="fa fa-circle fa-lg checkedR "></span>
                                    <span class="fa fa-circle fa-lg checkedR "></span>
                                    <span class="fa fa-circle fa-lg checkedR "></span>
                                    <span class="fa fa-circle fa-lg checkedR "></span>
                                    <span class="fa fa-circle fa-lg checkedR "></span>
                                <br /><br>
                                <input type="radio" name="difficulty" value="4" />  
                                    <p class="starHeader"> 4/5 </p>
                                    <span class="fa fa-circle fa-lg checkedR "></span>
                                    <span class="fa fa-circle fa-lg checkedR "></span>
                                    <span class="fa fa-circle fa-lg checkedR "></span>
                                    <span class="fa fa-circle fa-lg checkedR "></span>
                                    <span class="fa fa-circle fa-lg  "></span>
                                <br/><br>
                                <input type="radio" name="difficulty" value="3" /> 
                                    <p class="starHeader"> 3/5 </p>
                                    <span class="fa fa-circle fa-lg checkedR "></span>
                                    <span class="fa fa-circle fa-lg checkedR "></span>
                                    <span class="fa fa-circle fa-lg checkedR "></span>
                                    <span class="fa fa-circle fa-lg  "></span>
                                    <span class="fa fa-circle fa-lg  "></span>
                                <br/><br>
                                <input type="radio" name="difficulty" value="2" /> 
                                    <p class="starHeader"> 2/5 </p>
                                    <span class="fa fa-circle fa-lg checkedR "></span>
                                    <span class="fa fa-circle fa-lg checkedR "></span>
                                    <span class="fa fa-circle fa-lg  "></span>
                                    <span class="fa fa-circle fa-lg  "></span>
                                    <span class="fa fa-circle fa-lg  "></span>
                                <br/><br>
                                <input type="radio" name="difficulty" value="1" />
                                    <p class="starHeader"> 1/5 </p>
                                    <span class="fa fa-circle fa-lg checkedR "></span>
                                    <span class="fa fa-circle fa-lg  "></span>
                                    <span class="fa fa-circle fa-lg  "></span>
                                    <span class="fa fa-circle fa-lg  "></span>
                                    <span class="fa fa-circle fa-lg  "></span>
                                <br/>
                            </div>

                            <div class="reviewFormBlock">
                                <p class="subheading"> Teaching </p>
                                <input type="radio" name="teaching" value="5" checked/>
                                    <p class="starHeader"> 5/5 </p>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg checked "></span>
                                <br /><br>
                                <input type="radio" name="teaching" value="4" />  
                                    <p class="starHeader"> 4/5 </p>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg  "></span>
                                <br/><br>
                                <input type="radio" name="teaching" value="3" /> 
                                    <p class="starHeader"> 3/5 </p>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg  "></span>
                                    <span class="fa fa-star fa-lg  "></span> 
                                <br/><br>
                                <input type="radio" name="teaching" value="2" /> 
                                    <p class="starHeader"> 2/5 </p>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg  "></span>
                                    <span class="fa fa-star fa-lg  "></span>
                                    <span class="fa fa-star fa-lg  "></span>
                                <br/><br>
                                <input type="radio" name="teaching" value="1" />
                                    <p class="starHeader"> 1/5 </p>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg  "></span>
                                    <span class="fa fa-star fa-lg  "></span>
                                    <span class="fa fa-star fa-lg  "></span>
                                    <span class="fa fa-star fa-lg  "></span>
                                <br/>
                            </div>

                            <div class="reviewFormBlock">
                                <p class="subheading"> Class Content </p>
                                <input type="radio" name="content" value="5" checked/>
                                    <p class="starHeader"> 5/5 </p>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg checked "></span>
                                <br /><br>
                                <input type="radio" name="content" value="4" />  
                                    <p class="starHeader"> 4/5 </p>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg  "></span>
                                <br/><br>
                                <input type="radio" name="content" value="3" /> 
                                    <p class="starHeader"> 3/5 </p>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg  "></span>
                                    <span class="fa fa-star fa-lg  "></span> 
                                <br/><br>
                                <input type="radio" name="content" value="2" /> 
                                    <p class="starHeader"> 2/5 </p>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg  "></span>
                                    <span class="fa fa-star fa-lg  "></span>
                                    <span class="fa fa-star fa-lg  "></span>
                                <br/><br>
                                <input type="radio" name="content" value="1" />
                                    <p class="starHeader"> 1/5 </p>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg  "></span>
                                    <span class="fa fa-star fa-lg  "></span>
                                    <span class="fa fa-star fa-lg  "></span>
                                    <span class="fa fa-star fa-lg  "></span>
                                <br/>
                            </div>
                            <div class="reviewFormBlock">
                                <p class="subheading"> Fun </p>
                                <input type="radio" name="fun" value="5" checked/>
                                    <p class="starHeader"> 5/5 </p>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg checked "></span>
                                <br /><br>
                                <input type="radio" name="fun" value="4" />  
                                    <p class="starHeader"> 4/5 </p>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg  "></span>
                                <br/><br>
                                <input type="radio" name="fun" value="3" /> 
                                    <p class="starHeader"> 3/5 </p>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg  "></span>
                                    <span class="fa fa-star fa-lg  "></span> 
                                <br/><br>
                                <input type="radio" name="fun" value="2" /> 
                                    <p class="starHeader"> 2/5 </p>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg  "></span>
                                    <span class="fa fa-star fa-lg  "></span>
                                    <span class="fa fa-star fa-lg  "></span>
                                <br/><br>
                                <input type="radio" name="fun" value="1"  />
                                    <p class="starHeader"> 1/5 </p>
                                    <span class="fa fa-star fa-lg checked "></span>
                                    <span class="fa fa-star fa-lg  "></span>
                                    <span class="fa fa-star fa-lg  "></span>
                                    <span class="fa fa-star fa-lg  "></span>
                                    <span class="fa fa-star fa-lg  "></span>
                                <br/>
                            </div>
                        </div> 
                    </div>
                    <br><br>
                    <div class="reviewNumberDiv">
                        <p class="subheading"> Minutes of Homework per Day </p>
                        <input name="hwperday" type="number" class="logInEntry reviewNumberEntry" placeholder="(0-90)" required>
                    </div>
                    <div class="reviewNumberDiv">
                        <p class="subheading"> Major Assessments Per Semsester </p>
                        <input name="tests" type="number" class="logInEntry reviewNumberEntry" placeholder="(0-90)" rquired>
                    </div>
                    <div class="reviewNumberDiv">
                        <p class="subheading"> Major Projects Per Semster </p>
                        <input name="projects" type="number" class="logInEntry reviewNumberEntry" placeholder="(0-90)" required>
                    </div>

                    <div class="textareaDiv">
                        <p class="subheading"> Your Experience </p>
                        <textarea name="reviewText" rows="6" placeholder="Write about your experience in the class." required></textarea>
                    </div>
                    <input type="submit" class="reviewSubmit">

                </form>
                <br>
            </div>
        </div>
    </body>

</html>