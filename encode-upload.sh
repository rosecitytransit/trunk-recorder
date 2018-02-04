#! /bin/bash
#echo "Encoding: $1" 
filename="$1"
filesize=$(stat -c %s "$1")
if [[ $filesize -gt "10240" ]]; then
basename="${filename%.*}"
#mp3encoded="$basename.mp3"
#mp4encoded="$basename.m4a"
#json="$basename.json"
eval "nice -n 19 lame --silent --preset voice $filename $basename$2.mp3"
#eval "nice -n 19 /home/luke/bin/ffmpeg -i $filename  -c:a libfdk_aac -b:a 32k -cutoff 18000 $mp4encoded > /dev/null 2>&1"
rm $1
fi