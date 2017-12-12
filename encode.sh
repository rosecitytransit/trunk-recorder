#! /bin/bash
filesize=$(stat -c %s "$1")
if [[ $filesize -gt "10240" ]]; then
eval "nice -n 19 lame --silent --preset voice $1"
rm $1
fi
