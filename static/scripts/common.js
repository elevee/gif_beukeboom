function le (input) {
	//logo abbrev exceptions
	var exceptions = {
		"SJS": "SJ",
		"TBL": "TB",
		"LAK": "LA"
	}
	return exceptions[input] ? exceptions[input] : input;
}

$(document).ready(function(){
	$(document).foundation();

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

	$('i.favorite').on('click', function(e){
		e.preventDefault();
		var goalId = $(this).closest('.goal').find('.goalGif, .goalPlaceholder').attr('data-playbackId');
		var $this = $(this);
		$.post({
			url: "/static/scripts/favorite.php",
			data: {
				goalId: goalId
			},
			success: function(r){
				var _r = JSON.parse(r);
				if (_r['result'] == "added"){
					// console.log("added!")
					$this.removeClass('fa-heart-o').addClass('fa-heart');
				} else {
					// console.log("other");
					$this.removeClass('fa-heart').addClass('fa-heart-o');
				}
			}
		});
	});	
});
