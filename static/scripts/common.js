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
});
