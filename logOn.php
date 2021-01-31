<?php
// Siddharth Hathi
// CS4
// Final Web Assignment
// 9 December 2019

session_start();

$_SESSION["loggedIn"] = FALSE;
$_SESSION["loggedInUser"] = "";

require("utils.php");
$db = setupDB();

$displayErrorMessage = FALSE;


        if ($_SERVER["REQUEST_METHOD"] == "POST")
        {
            if (!empty($_POST["signup"]))
            {
                if (!empty($_POST["password"]) and !empty($_POST["username"]))
                {
                    // Checking that the name they submitted isn't already in the database.
                    $query = "SELECT `username` FROM shathi_users WHERE 1";
                    $statement = $db->prepare($query);
                    $statement->execute();
                    $results = $statement->fetchAll();

                    $duplicate = FALSE;

                    foreach ($results as $result)
                    {
                        if ($result["username"] == $_POST["username"])
                        {
                            $duplicate = TRUE;
                        }
                    }
                    if (!$duplicate)
                    {
                        // hashes password and puts it into database
                        $username = $_POST['username'];
                        $hash = password_hash($_POST['password'], PASSWORD_DEFAULT, ['cost' => 12]);

                        $query = "INSERT INTO shathi_users(`username`, `password`) VALUES (:usernameInput, :passwordHash);";
                        $statement = $db->prepare($query);
                        $statement->execute(array('usernameInput' => $username, 'passwordHash' => $hash));

                        $_SESSION["loggedIn"] = TRUE;
                        $_SESSION["loggedInUser"] = htmlspecialchars($username);

                        header("Location: index.php");
                    }
                    else
                    {
                        $_SESSION["loggedIn"] = FALSE;
                        $displayErrorMessage = TRUE;
                    }
                    
                }
            }
            else
            {
                if (!empty($_POST["password"]) and !empty($_POST))
                {
                    // verifies username/password
                    $password = $_POST['password'];
                    $username = $_POST['username'];
                    $query = "SELECT `password` FROM shathi_users WHERE (`username` = :usernameInput);";
                    $statement = $db->prepare($query);
                    $statement->execute(array('usernameInput' => $username));
                    $results = $statement->fetchAll();
                    $user_hashedp = $results[0][0];
                    if (password_verify($password, $user_hashedp)) 
                    {
                        $_SESSION["loggedIn"] = TRUE;
                        $_SESSION["loggedInUser"] = htmlspecialchars($username);
                        header("Location: index.php");
                    } 
                    else 
                    {
                        $_SESSION["loggedIn"] = FALSE;
                        $displayErrorMessage = TRUE;
                    }
                }
            }
        }

        ?>
        
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Lakeside's Expanded Curriculum Guide</title>
    <link rel = "stylesheet" type = "text/css" href = "css/style.css" />

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

        <?php

        if (!empty($_GET["signup"]))
        {
        ?>
        <div class="logOnMain">
            <div class="container logOn">
                <img src="img/logo.png" alt="logo" class="logOnImage">

                <br><br><br>

                <div>
                    <form action="logOn.php?signup=TRUE" method="POST">
                        <h2 class="logOnText">username:</h2> 
                        <input type="text" name="username" class="logInEntry" required>
                        <br><br>
                        <h2 class="logOnText">password:</h2> 
                        <input type="password" id="password" name="password" class="logInEntry" required>
                        <br><br>
                        <h2 class="logOnText">re-enter password:</h2> 
                        <input type="password" name="password_confirm" class="logInEntry" oninput="check(this)" required>
                        <script language='javascript' type='text/javascript'>
                            function check(input) {
                                if (input.value != document.getElementById('password').value) {
                                    input.setCustomValidity('Password Must be Matching.');
                                } else {
                                    // input is valid -- reset the error message
                                    input.setCustomValidity('');
                                }
                            }
                        </script>
                        <br>
                        <?php
                        if ($displayErrorMessage)
                        {
                            ?>
                            <p class="error">That Username is already taken. Please try again. <p>
                            <?php
                        }
                        ?>
                        <br>
                        <input type="submit" name="signup" value="SIGN UP"> 
                    <form>
                </div>

                <br>

                <p> Already have an account? <a href="logOn.php">Log In </a> </p>
            </div>
        </div> 
        <?php
        }
        else
        { ?>
        <div class="logOnMain">
            <div class="container logOn">
                <img src="img/logo.png" alt="logo" class="logOnImage">

                <br><br><br>

                <div>
                    <form action="logOn.php" method="POST">
                        <h2 class="logOnText">username:</h2> 
                        <input type="text" name="username" class="logInEntry" required>
                        <br><br>
                        <h2 class="logOnText">password:</h2> 
                        <input type="password" name="password" class="logInEntry" required>
                        <br>
                        <?php
                        if ($displayErrorMessage)
                        {
                            ?>
                            <p class="error">Incorrect Username or Password. Please try again. <p>
                            <?php
                        }
                        ?>
                        <br>
                        <input type="submit" name="logIn" value="LOG IN"> 
                    <form>
                </div>

                <br>

                <p> Don't have an account? <a href="logOn.php?signup=TRUE"> Sign Up </a> </p>
            </div>
        </div>
        <?php
        }?>
    </body>

</html>