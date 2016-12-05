<?php
echo('<div class="reveal" id="trimModal" data-reveal data-goalId data-gameId>');
  echo('<h1>Trim Goal</h1>');
  echo('<p class="lead">Full-length GIFs are bulky and increase load time. Let\'s trim the goal to just the best part.</p>');
  echo('<p>Simply click trim when the video playhead is <span class="emphasis">at the point when the puck enters the net.</span></p>');
  echo('<p>TIPS (literally): Often times the best view of the goal is from the replays. Especially on a deflected shot. Be patient or scroll ahead.</p>');

  echo('<div class="row center">');
  	echo('<video controls autoplay autostart></video>');
  echo('</div>');
  echo('<br/>');
  echo('<div class="row center">');
  	echo('<button class="trimVideo button">Trim<button>');
  echo('</div>');

  echo('<button class="close-button" data-close aria-label="Close modal" type="button">');
    echo('<span aria-hidden="true">&times;</span>');
  echo('</button>');
echo('</div>');


echo('<div class="reveal" id="confirmTrimModal" data-reveal data-goalId>');
  echo('<h1>Thank you</h1>');
  echo('<p class="lead">Thank you for making GIF Beukeboom better for everyone!</p>');
  echo('<p>The short GIF should be available shortly on reload.</p>');

  echo('<div class="row center">');
  	echo('<button class="trimVideo button" data-close aria-label="Close modal" type="button">OK<button>');
  echo('</div>');
echo('</div>');