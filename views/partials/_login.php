<?php

if (isset($state['loginError'])) {
	echo("<p>".$state['loginError']."</p>");
}

echo("<div class='large-4 large-centered columns' style='float: none;'>");
	echo("<div class='login-box'>");
		echo("<h1>Log In</h1>");
		echo("<p>Please log in to view the page you requested.</p>");
		echo("<form action='' method='post'>");
			echo("<div class='row'>");
				echo("<div class='large-12 columns'>");
					echo("<label for='email' placeholder='Email'>Email: <input type='text' name='email' id='email'></label>");
				echo("</div>");
				echo("<div class='large-12 columns'>");
					echo("<label for='password' placeholder='Password'>Password: <input type='password' name='password' id='password'></label>");
				echo("</div>");
				echo("<div class='large-12 large-centered columns'>");
					echo("<input type='hidden' name='action' value='login'>");
					echo("<input type='submit' class='button expand' value='Log In'/>");
				echo("</div>");
			echo('<div>');
			
			echo("<div class='row'>");
				
			echo("</div>");
		echo("</form>");
	echo("</div>");
echo("</div>");