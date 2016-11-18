<?php
echo("<section class='header row'>");
	if (isset($_SESSION['loggedIn'])){
		echo("<div class='large-12 columns loggedIn'>");
			echo("<form action='' method='post'>");
				echo("<ul class='right'>");
					echo("<li>");
						echo("<span class='userName'>".$currentUser["first_name"]."</span>");
					echo("</li>");
					echo("<li>");
						// include "views/partials/_logout.php";
						echo("<div>");
							echo("<input type='hidden' name='action' value='logout'>");
							echo("<input type='hidden' name='goto' value='/'>");
							echo("<input type='submit' class='logout' value='Log out'>");
						echo("</div>");
						
					echo("</li>");
				echo("</ul>");
			echo("</form>");
		echo("</div>");
	}

	echo("<div class='large-12 columns'>");
		// echo("<h1>GIF Beukeboom</h1>");
		echo("<div class='logo center'>");
			echo("<a href='https://www.gifbeukeboom.com/'><img src='/static/images/logo.png' /></a>");
		echo("</div>");
	echo("</div>");
echo("</section>");
