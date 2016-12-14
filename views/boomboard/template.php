<?php
echo('<div class="row">');
	echo('<div class="large-12 columns header">');
		echo('<h1>BoomBoard</h1>');
	echo('</div>');
echo('</div>');
echo('<div class="row">');
	echo('<div class="large-4 large-offset-4 columns">');
		echo('<select name="type">');
			echo('<option>2016/17 Season</option>');
		echo('</select>');
	echo('</div>');
echo('</div>');
echo('<div class="row">');
	echo('<div class="large-12 columns">');
		echo('<table><tbody>');
			foreach ($goals as $index => $gl) {
				echo('<tr>');
					echo('<td class="rank">'.($index+1).'</td>');
					echo('<td>');
						echo('<img src="https://nhl.bamcontent.com/images/headshots/current/168x168/'.$gl["playerId"].'.jpg" /><br/>');
						echo($gl["scorer"]);
					echo('</td>');
					echo('<td>');
						echo('<div class="goal">');
							echo(isset($gl["videoLinkout"]) ? "<h7 class='video_linkout'>"."<a href='".$gl["videoLinkout"]."' target='_blank'>"."<i class='fa fa-television fa-lg' aria-hidden='true'></i>"."</a></h7>" : "");
							echo(isset($gl["shortGifUri"]) && strlen($gl["shortGifUri"]) > 0) || (!isset($gl["gifUri"]) && !isset($gl["shortGifUri"]) || !isset($gl["videoLinkout"]))? "" : "<h7><a href='#' data-open='trimModal' class='trim'><i class='fa fa-scissors fa-lg' aria-hidden='true'></i></a></h7>";
							// echo('<i class="favorite fa '.(isset($gl["favorited"]) ? "fa-heart":"fa-heart-o").' fa-lg" aria-hidden="true"></i>');
							// echo(isset($gl["popularity"]) ? "<span class='popularity'>".$gl["popularity"]."</span>" : "");
							include_once dirname(__FILE__).'/../partials/_trim.php';
							$short_gif_set = isset($gl["shortGifUri"]) && strlen($gl["shortGifUri"]) > 0;
							$gif_set = isset($gl["gifUri"]) && strlen($gl["gifUri"]) > 0;
							if(isset($gl["videoLinkout"])){	
								echo('<div class="'.($short_gif_set || $gif_set ? "goalGif" : "goalPlaceholder").' loading" style="position: relative;" data-playbackId="'.$gl["id"].'" data-playbackUrl="'.$gl["videoLinkout"].'">');
									if($short_gif_set){
										echo('<img src="'.$gl["placeholder_img"].'" data-gif="'.$gl["shortGifUri"].'" />');
										echo('<i class="fa fa-play fa-5x" aria-hidden="true"></i>');
										echo('<i class="fa fa-circle-o-notch fa-spin fa-4x fa-fw"></i>');
										echo('<span class="sr-only">Loading...</span>');
									} else if($gif_set){
										echo('<img src="'.$gl["placeholderImg"].'" data-gif="'.$gl["gifUri"].'" />');
										echo('<i class="fa fa-play fa-5x" aria-hidden="true"></i>');
										echo('<i class="fa fa-circle-o-notch fa-spin fa-4x fa-fw"></i>');
										echo('<span class="sr-only">Loading...</span>');
									} else if (isset($gl["placeholderImg"])) { // No GIF yet!
										echo('<img src="'.$gl["placeholderImg"].'" />');
										echo('<span class="processing">Processing</span>');
									} else {
										echo('<p>No goal video available to GIF!</p>');
									}
								echo('</div>');
							} else {
								echo("<p>No goal video available to GIF!</p>");
							}
						echo('</div>');
					echo('</td>');
					echo('<td>');
						echo('<i class="favorite fa '.(isset($gl["favorited"]) ? "fa-heart":"fa-heart-o").' fa-lg" aria-hidden="true"></i>');
						echo('<span class="popularity">'.$gl["popularity"].'</span><br />');
						echo('<span class="date" >12/13 VS</span> Blah');
					echo('</td>');
				echo('</tr>');
			}
		echo('</tbody></table>');
	echo('</div>');
echo('</div>');