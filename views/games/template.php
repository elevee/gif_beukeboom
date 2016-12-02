<?php
$applicablePeriods = array();
foreach ($game["goals"] as $k => $gl) {
	if (!in_array($gl["period"], $applicablePeriods)){
		$applicablePeriods[] = $gl["period"]; 
	}
}

function displayGoals($period, $goals, &$applicablePeriods) {
	if (isset($period)){
		$output = "";
		if (!goalScoredInPeriod($period, $goals, $applicablePeriods)){
			return "No scoring.";
		}
		foreach ($goals as $k => $gl) {
			if($gl["period"] == $period){
				// echo "<pre>";
				// print_r($gl);
				// echo "</pre>";
				$output .= "<div class='goal ". ($gl["isHomeTeam"] ? "home":"away")."Goal'>";
					$output .= "<span class='name'>".$gl["scorer"]."</span>";
					if ($period !== "5"){ //shootout goals apparently don't count towards goal total
						$output .= isset($gl["seasonTotal"]) ? "<span class='seasonTotal'>  (".$gl["seasonTotal"].")</span>" : "";
					}
					$output .= "<br/>";
					$output .= isset($gl["video_linkout"]) ? "<h7 class='video_linkout'>"."<a href='".$gl["video_linkout"]."' target='_blank'>"."<i class='fa fa-television fa-lg' aria-hidden='true'></i>"."</a></h7>" : "";
					$output .= (isset($gl["shortGifUri"]) && strlen($gl["shortGifUri"]) > 0) || (!isset($gl["gifUri"]) && !isset($gl["shortGifUri"]))? "" : "<h7>"."<a href='#' data-open='trimModal' class='trim'>"."<i class='fa fa-scissors fa-lg' aria-hidden='true'></i>"."</a></h7>";
					include_once dirname(__FILE__).'/../partials/_trim.php';
					$short_gif_set = isset($gl["shortGifUri"]) && strlen($gl["shortGifUri"]) > 0;
					$gif_set = isset($gl["gifUri"]) && strlen($gl["gifUri"]) > 0;
					if(isset($gl["video_linkout"])){	
						$output .= "<div class='".($short_gif_set || $gif_set ? "goalGif" : "goalPlaceholder")." loading' style='position: relative;' data-playbackId='".$gl["goalId"]."' data-playbackUrl='".$gl["video_linkout"]."'>";
							if($short_gif_set){
								$output .= "<img src='".$gl["placeholder_img"]."' data-gif='".$gl["shortGifUri"]."' />";
								$output .= "<i class='fa fa-play fa-5x' aria-hidden='true'></i>";
								$output .= "<i class='fa fa-circle-o-notch fa-spin fa-4x fa-fw'></i>";
								$output .= "<span class='sr-only'>Loading...</span>";
							} else if($gif_set){
								$output .= "<img src='".$gl["placeholder_img"]."' data-gif='".$gl["gifUri"]."' />";
								$output .= "<i class='fa fa-play fa-5x' aria-hidden='true'></i>";
								$output .= "<i class='fa fa-circle-o-notch fa-spin fa-4x fa-fw'></i>";
								$output .= "<span class='sr-only'>Loading...</span>";
							} else if (isset($gl["placeholder_img"])) { // No GIF yet!
								$output .= "<img src='".$gl["placeholder_img"]."' />";
								$output .= "<span class='processing'>Processing</span>";
							} else {
								$output .= "<p>No goal video available to GIF!</p>";
							}
						$output .= "</div>";
					} else {
						$output .= "<p>No goal video available to GIF!</p>";
					}
					$output .= "<span class='time'>". ($period == "5" ? "" : $gl["time"]) ."</span>";
				$output .= "</div>";
			}
		}
		return $output;
	}
}

function goalScoredInPeriod($period, $goals, &$applicablePeriods){
	if (isset($goals) && is_array($goals)){
		foreach ($goals as $k => $gl) {
			if(in_array($period, $applicablePeriods)){
				return true;
			}
		}
	}
	return false;
}

if(isset($game) && is_array($game)){
	// print_r($game);
	// echo("<pre>");
	// print_r($game["goals"]);
	// echo("</pre>");
	echo("<section class='game large-12 columns' data-gameId='".$state['id']."'>");
		echo("<div class='row'>");
			echo("<div class='large-12 columns matchup'>");
				echo("<div class='large-5 small-5 columns away_team'>");
					echo("<div class='team_logo'>");
						echo("<img src='http://a.espncdn.com/combiner/i?img=/i/teamlogos/nhl/500/".$game["awayAbbrev"].".png&h=400' />");
					echo("</div>");
					echo("<div class='row teamName'>");
						echo("<span>".$game["awayTeamName"]."</span>");
					echo("</div>");
					echo("<div class='row record'>");
						echo("<span>".$game["awayRecord"]."</span>");
					echo("</div>");
					echo("<div class='row'>");
						echo("<h1 class='score'>".$game["awayTeamScore"]."</h1>");
					echo("</div>");
				echo("</div>");
				echo("<div class='large-2 small-2 columns status'>");
					echo("<span>".$game["status"]);
						if ($game["status"] == "In Progress" && isset($game["time_left"])){
							echo("<br />".$game["time_left"]."  ".$game["period"]);
						}
					echo("</span>");
					echo("<div class='row'>");
						// if($game['isToday']){
						// 	echo("Ayyo row 2");
						// 	echo($game["awayStandings"]);
						// }
					echo("</div>");
				echo("</div>");
				echo("<div class='large-5 small-5 columns home_team'>");
					echo("<div class='team_logo'>");
						echo("<img src='http://a.espncdn.com/combiner/i?img=/i/teamlogos/nhl/500/".$game["homeAbbrev"].".png&h=400' />");
					echo("</div>");
					echo("<div class='row teamName'>");
						echo("<span>".$game["homeTeamName"]."</span>");
					echo("</div>");
					echo("<div class='row record'>");
						echo("<span>".$game["homeRecord"]."</span>");
					echo("</div>");
					echo("<div class='row'>");
						echo("<h1 class='score'>".$game["homeTeamScore"]."</h1>");
					echo("</div>");
				echo("</div>");
			echo("</div>");
		echo("</div>");
		echo("<div class='row row-padding'>");
		 echo("<ul class='goals accordion large-12 columns' data-accordion role='tablist'>");
		  echo("<li class='accordion-navigation'>");
		        echo("<a href='#period1' role='tab' class='accordion-title' id='period1-heading' aria-controls='period1'>First Period</a>");
		        echo("<div id='period1' class='accordion-content active' role='tabpanel' data-tab-content aria-labelledby='period1-heading'>");
	        		echo(displayGoals("1", $game["goals"], $applicablePeriods));
		        echo("</div>");
		    echo("</li>");
		    echo("<li class='accordion-navigation'>");
		        echo("<a href='#period2' role='tab' class='accordion-title' id='period2-heading' aria-controls='period2'>Second Period</a>");
		        echo("<div id='period2' class='accordion-content' role='tabpanel' data-tab-content aria-labelledby='period2-heading'>");
			        echo(displayGoals("2", $game["goals"], $applicablePeriods));
		        echo("</div>");
		    echo("</li>");
		    echo("<li class='accordion-navigation'>");
		        echo("<a href='#period3' role='tab' class='accordion-title' id='period3-heading' aria-controls='period3'>Third Period</a>");
		        echo("<div id='period3' class='accordion-content' role='tabpanel' data-tab-content aria-labelledby='period3-heading'>");
			        echo(displayGoals("3", $game["goals"], $applicablePeriods));
		        echo("</div>");
		    echo("</li>");
			if(goalScoredInPeriod("4", $game["goals"], $applicablePeriods)) {
				echo("<li class='accordion-navigation'>");
			        echo("<a href='#period4' role='tab' class='accordion-title' id='period4-heading' aria-controls='period4'>Overtime</a>");
			        echo("<div id='period4' class='accordion-content' role='tabpanel' data-tab-content aria-labelledby='period4-heading'>");
			        	echo(displayGoals("4", $game["goals"], $applicablePeriods));
			        echo("</div>");
			    echo("</li>");
			}
			if(goalScoredInPeriod("5", $game["goals"], $applicablePeriods)) {
			    echo("<li class='accordion-navigation'>");
			        echo("<a href='#period5' role='tab' class='accordion-title' id='period5-heading' aria-controls='period5'>Shootout</a>");
			        echo("<div id='period5' class='accordion-content' role='tabpanel' data-tab-content aria-labelledby='period5-heading'>");
				        echo(displayGoals("5", $game["goals"], $applicablePeriods));
			        echo("</div>");
			    echo("</li>");
			}
		  echo("</ul>");
		echo("</div>");
	 echo("</div>");
	 echo("<a href='#' data-open='confirmTrimModal' class='confirmTrimModal'></a>"); //to be triggered remotely
	echo("</section>");
}