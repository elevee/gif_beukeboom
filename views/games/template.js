$(document).ready(function(){
	// var str = "https://statsapi.web.nhl.com/api/v1/schedule?startDate=2016-03-18&endDate=2016-03-18&expand=schedule.teams,schedule.linescore,schedule.broadcasts.all,schedule.ticket,schedule.game.content.media.epg,schedule.decisions,schedule.scoringplays,schedule.game.content.highlights.scoreboard,team.leaders&leaderCategories=points,goals,assists&site=en_nhl&teamId=";
	var gameId 			= $('.game').attr('data-gameId'),
		goalId 			= "45504503", //remove after done with this test
		sched_uri 		= "https://statsapi.web.nhl.com/api/v1/schedule?gamePk=" + gameId + "&expand=schedule.teams,schedule.linescore,schedule.broadcasts.all,schedule.ticket,schedule.game.content.media.epg,schedule.decisions,schedule.scoringplays,schedule.game.content.highlights.scoreboard,team.leaders&leaderCategories=points,goals,assists&site=en_nhl&teamId=",
		media_uri 		= "https://statsapi.web.nhl.com/api/v1/game/"+gameId+"/content",
		goal_uri		= "https://nhl.bamcontent.com/nhl/id/v1/"+goalId+"/details/web-v1.json";
		stand_uri		= "https://statsapi.web.nhl.com/api/v1/standings/wildCardWithLeaders?expand=standings.record,standings.team,standings.division,standings.conference,team.schedule.next,team.schedule.previous&season=20162017";
		db_uri			= "/static/scripts/api_db.php",
		schedResponse 	= null,
		mediaResponse 	= null,
		goalResponse    = null;

	var getSched = function(){
		return $.ajax({
			url: sched_uri,
			dataType: 'json',
			success: function(data){
				console.log("Sched info: ", data);
				schedResponse = data;
			}
		});
	};

	var getMedia = function(){
		return $.ajax({
			url: media_uri,
			dataType: 'json',
			success: function(data){
				console.log("Media info: ", data);
				mediaResponse = data;
			}
		});
	};

	var getGoal = function(){ //delete if unnec.
		return $.ajax({
			url: goal_uri,
			dataType: 'json',
			success: function(data){
				console.log("Goal info: ", data);
				goalResponse = data;
			}
		});
	};

	var getStandings = function(){ //delete if unnec.
		return $.ajax({
			url: stand_uri,
			dataType: 'json',
			success: function(data){
				console.log("Standings info: ", data);
				goalResponse = data;
			}
		});
	};

	// var getGoals = function(gameId){  //DB call
	// 	return $.ajax({
	// 		url: db_uri + "?gameId="+gameId,
	// 		dataType: 'json',
	// 		success: function(data){
	// 			console.log("DB API info: ", data);
	// 			// mediaResponse = data;
	// 		}
	// 	});
	// }

	// getSched();
	// getMedia();
	// getGoal();
	// getStandings();


	// Retrieves GIF image path we have put in the data-alt attribute. 
	// Looping through each of the images, using jQuery .data() method:
	var getGifs = function() {
		var gif = [];
		$('.goals img').each(function() {
			var data = $(this).data('gif');
			gif.push(data);
		});
		return gif;
	}
	var gifs = getGifs();

	// Preload all the GIFs.
	var image = [];
	
	var $goals = $(".goals .goalGif");
	$.each(gifs, function(index, el) {
		image[index]     = new Image();
		image[index].onload = function () {
			$goals.each(function(i, gl){
				var found = $(gl).find("img[data-gif='"+el+"']");
				if( found && found.length > 0 ){
					$(gl).removeClass("loading").addClass("paused");
					$(gl).on('click', function() { //attaching listener only after gif has loaded fully
						var $this   = $(this),
							$index  = $this.index(),
							$img    = $this.children('img'),
							$imgSrc = $img.attr('src'),
							$imgAlt = $img.attr('data-gif'),
							$imgExt = $imgAlt.split('.');

						if($imgExt.slice(-1)[0] === 'gif') {
							$img.attr('src', $img.data('gif')).attr('data-gif', $imgSrc);
							$this.removeClass('paused');
						} else {
							$img.attr('src', $imgAlt).attr('data-gif', $imgSrc);
							$this.addClass('paused');
						}
					});
				}
			});
		};
		image[index].src = gifs[index];
	});

	// so accordion will collapse and expand as expected
	$('.accordion-title').click(function(accordion){
		$this = $(this);
		$content = $this.next('.accordion-content');
		$content.is(':visible') ? $content.slideUp() : $content.slideDown();
	});

	//When trim button is pressed on a goal
	$('.trim').click(function(e){
		e.preventDefault();
		var $gl = $(this).closest('.goal'),
			id = $gl.find('.goalGif, .goalPlaceholder').attr('data-playbackId'),
			url = $gl.find('.video_linkout a').attr('href'),
			$trimDiv = $('#trimModal');

		console.log('GID is '+gameId);
		$trimDiv.attr('data-goalId', id);
		$trimDiv.attr('data-gameId', gameId);
		$trimDiv.find('video').attr('src', url);
	});

	//When trim button is pressed on trimGoal modal
	$('.trimVideo').on('click', function(e){
		e.preventDefault();
		var $trimModal = $('#trimModal');
		var currentTime = $trimModal.find('video')[0].currentTime;
		var $button = $(this);
		// console.log(currentTime);
		if(currentTime && confirm("Happy with playhead position of "+currentTime+"?")){
			$button.attr('disabled', true);
			$.post({
				url: "/static/scripts/customGif.php",
				data: {
					goalId: $trimModal.attr('data-goalId'),
					currentTime: currentTime,
					gameId: $trimModal.attr('data-gameId')
				},
				success: function(r){
					// console.log("Response: "+r);
					$trimModal.attr('data-goalId', '');
					$trimModal.find('video').attr('src', '');
					$('.confirmTrimModal').click();
					$button.attr('disabled', false);
				}
			});
		}
	});
});