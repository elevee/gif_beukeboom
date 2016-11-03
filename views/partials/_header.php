<?php
echo("<section class='header row'>");
	if (isset($_SESSION['loggedIn'])){
		echo("<div class='large-12 columns'>");
			include "views/partials/_logout.php";
		echo("</div>");
	}

	// if($user){
	// 	echo 'Oh snap! A user!';
	// } else {
	// 	echo '<a href="' . htmlspecialchars($loginUrl) . '">Log in with Facebook!</a>';
	// }

	echo("<div class='large-12 columns'>");
		echo("<h1>GIF Beukeboom</h1>");
	echo("</div>");
echo("</section>");
