#!
sed remove /var/www/html/radio/unitreg.txt or compare if same?
params date >> /var/www/html/radio/unitreg.txt
if ((param > 1000) && (param < 8000))
	php unitloc.php params