#!/bin/bash
sed -i "/^$1 /d" /var/www/html/radio/units.txt
echo "$1 $2 `date +%s`" >> /var/www/html/radio/units.txt
if [ "$1" -gt 1000 ] && [ "$1" -lt 8000 ]
then
	php unitloc.php $1 $2 &
fi