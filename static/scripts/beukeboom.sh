#!/bin/sh

palette="$2.png"

filters="fps=15,scale=400:-1:flags=lanczos"

# echo -v warning -i $1 -vf "$filters,palettegen" -y $palette
# echo -v warning -i $1 -i $palette -lavfi "$filters [x]; [x][1:v] paletteuse" -y "$2.gif"
ffmpeg -v warning -i $1 -vf "$filters,palettegen" -y $palette
ffmpeg -v warning -i $1 -i $palette -lavfi "$filters [x]; [x][1:v] paletteuse" -y "$2.gif"