<?php
echo("<form action='' method='post'>");
	echo("<div>");
		echo("<input type='hidden' name='action' value='logout'>");
		echo("<input type='hidden' name='goto' value='/'>");
		echo("<input type='submit' class='logout' value='Log out'>");
	echo("</div>");
echo("</form>");