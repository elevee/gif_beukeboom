$(document).ready(function(){

	function queryAPI(o){ //type= "season" season=""
		o["type"] ? o["type"] : "season";
		o["season"] ? o["season"] : "2016/17";
		// $o["team"] ? $o["team"] : null;
		// if type is "team", we need a team
		return $.get({
			url: "/static/scripts/boomboard_api.php",
			dataType: 'json',
			data: {
				type: o["type"],
				season: o["season"],
				team: o["team"]
			},
			// success: function(r){
				// console.log(r);
				// return JSON.parse(r);
				// return r;
			// }
		});
	}

	function renderResult(r){
		console.log(r);
		var $table = $('table tbody');
		console.log($table);
		for(i=0,j=r.results.length;i<j;i++){
			var str = "<tr>";
				str += "<td>"+(i+1)+"</td>";
				str += "<td>";
					str += r["results"]["video_linkout"] ? "<h7 class='video_linkout'>"+"<a href='"+r["results"]["video_linkout"]+"' target='_blank'>"+"<i class='fa fa-television fa-lg' aria-hidden='true'></i>"+"</a></h7>" : "";
					str += (r["results"]["shortGifUri"] && r["results"]["shortGifUri"].length > 0) || (!r["results"]["gifUri"] && !r["results"]["shortGifUri"])? "" : "<h7>"+"<a href='#' data-open='trimModal' class='trim'>"+"<i class='fa fa-scissors fa-lg' aria-hidden='true'></i>"+"</a></h7>";
					// str += "<i class='favorite fa "+(r["favorited"]) ? "fa-heart":"fa-heart-o")+" fa-lg' aria-hidden='true'></i>";
				str += "</td>";
				str += "</tr>";
				console.log(str);
			$table.append(str);
		}
	}

	// var result = queryAPI({
	// 	"type": "season"
	// });

	// console.log(result);

	$.when(queryAPI({
		"type": "season"
	})).done(function(r){
		// console.log("renderResultESPONSE:");
		// console.log(r);
		renderResult(r);
	});

});