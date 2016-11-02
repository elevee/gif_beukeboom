$(document).ready(function(){
	var date_input = $('.day').attr('data-date');
	var json_url = "https://statsapi.web.nhl.com/api/v1/schedule?startDate="+date_input+"&endDate="+date_input+"&expand=schedule.teams,schedule.linescore,schedule.broadcasts.all,schedule.ticket,schedule.game.content.media.epg,schedule.decisions,schedule.scoringplays,schedule.game.content.highlights.scoreboard,team.leaders&leaderCategories=points,goals,assists&site=en_nhl&teamId=";
	// console.log("json url we're fetching is ", json_url);
	if(date_input){
		$.get(json_url, null, function(data){
			// console.log("success!", data);
			$games = $('.games table');
			$('.day span').html("<a href='/?date="+ moment(date_input).subtract(1, 'days').format("YYYY-MM-DD") + "'><span class='yesterday'>&lt </span></a>" + moment(date_input).format("dddd, MMMM Do") + "<a href='/?date="+ moment(date_input).add(1, 'days').format("YYYY-MM-DD") + "'><span class='tomorrow'> &gt</span></a>");
			if(data["dates"] && data["dates"].length > 0){
				var date 	= data["dates"][0]["date"], 
					games 	= data["dates"][0]["games"];

				for(i=0,j=games.length;i<j;i++){
					var awayTeam 			= games[i]["teams"]["away"]["team"]["teamName"],
						homeTeam 			= games[i]["teams"]["home"]["team"]["teamName"],
						awayScore 			= games[i]["teams"]["away"]["score"],
						homeScore 			= games[i]["teams"]["home"]["score"],
						detailedState		= games[i]["status"]["detailedState"], //doesn't include time remaining
						current_period_val	= games[i]["linescore"]["currentPeriod"],
						current_period  	= games[i]["linescore"]["currentPeriodOrdinal"],
						timeLeft 			= games[i]["linescore"]["currentPeriodTimeRemaining"],
						awayAbbrev 			= le(games[i]["teams"]["away"]["team"]["abbreviation"]),
						homeAbbrev			= le(games[i]["teams"]["home"]["team"]["abbreviation"]),
						gameId	 			= games[i]["gamePk"],
						isLoser;
						
					var detail = detailedState; //we'll show "Final" and "Scheduled" if not in progress

					// console.log("Game "+awayTeam+" vs "+homeTeam+" is "+detail);

					if(detailedState == "In Progress" || detailedState == "In Progress - Critical"){
						detail = timeLeft+" "+current_period;
					}

					if(detailedState == "Final"){ 
						isLoser = (parseInt(awayScore) < parseInt(homeScore) ? "away" : "home");
						if (current_period_val && current_period_val > 3){ //game went to OT+
							detail = detailedState +" ("+current_period+")";
						}
					}

					if(detailedState == "Scheduled"){
						detail = toLocalTime( games[i]["gameDate"] );
					}

					var str = "";
					str += "<tr onClick='window.location = \"/games/"+gameId+"\";'>";
						str += "<td width='40%' class='team_away "+(isLoser === "away" ? "loser" : "")+"'>";
							str += "<img src='http://a.espncdn.com/combiner/i?img=/i/teamlogos/nhl/500/"+awayAbbrev+".png&h=75&w=75' />"+awayTeam;
						str += "</td>";
						str += "<td class='score' width='20%'><span>"+awayScore+" - "+homeScore+"</span><br/><span class='score_detail'>"+detail+"<span></td>";
						str += "<td width='40%' class='team_home "+(isLoser === "home" ? "loser" : "")+"'>"+homeTeam;
							str += "<img src='http://a.espncdn.com/combiner/i?img=/i/teamlogos/nhl/500/"+homeAbbrev+".png&h=75&w=75' />";
						str += "</td>";
					str += "</tr>";
					$games.append(str);
				}
			} else { //nothing under date key in sched response
				var str = "<p class='noGames'>No games scheduled today.</p>";
				$games.append(str);
			}
			
		});
	}

	function toLocalTime(utc){
		if(utc && utc.length > 0){
			var gameTime = new Date(utc);
			var hours = gameTime.getHours();
			var minutes = gameTime.getMinutes();

			var suffix = "AM";

			if (hours >= 12) {
			    suffix = "PM";
			    hours = hours - 12;
			}

			if (hours == 0) {
			    hours = 12;
			}

			if (minutes < 10) {
			    minutes = "0" + minutes;
			}

			return hours + ":" + minutes + " " + suffix;
		}
	}

	//quick example of goal data:
	var goalUri = "https://nhl.bamcontent.com/nhl/id/v1/45401703/details/web-v1.json"

	// $.get(goalUri, null, function(data){
	// 	 console.log("Example of a goal data fetch: ", data);
	// });
});