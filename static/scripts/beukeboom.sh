#!/bin/bash
filters="fps=15,scale=400:-1:flags=lanczos"
if [[ $3 = "short" ]]
then
	# $4 - start time in seconds
	# $5 - duration in seconds
	palette="$2_s.png"
	gif_output="$2_s.gif"
	# dev
	# /Users/ericlevine/bin/ffmpeg -v warning -ss $4 -t $5 -i $1 -vf "$filters,palettegen" -y $palette
	# /Users/ericlevine/bin/ffmpeg -v warning -ss $4 -t $5 -i $1 -i $palette -lavfi "$filters [x]; [x][1:v] paletteuse" -y $gif_output
	# production
	ffmpeg -v warning -ss $4 -t $5 -i $1 -vf "$filters,palettegen" -y $palette
	ffmpeg -v warning -ss $4 -t $5 -i $1 -i $palette -lavfi "$filters [x]; [x][1:v] paletteuse" -y $gif_output
else
	palette="$2.png"
	gif_output="$2.gif"
	ffmpeg -v warning -i $1 -vf "$filters,palettegen" -y $palette
	ffmpeg -v warning -i $1 -i $palette -lavfi "$filters [x]; [x][1:v] paletteuse" -y $gif_output
fi
