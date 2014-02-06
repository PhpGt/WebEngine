#!/bin/bash

#Exit immediately if a simple command exits with a non-zero status.
set -e

# Ensure we're in the script's directory.
cd `dirname $0`

if [[ ! -e "composer.phar" ]]
then
    echo "Composer not found - installing..."
    if [[ ! -e "installer" ]]
    then
        wget https://getcomposer.org/installer --no-check-certificate
    fi
    
    php installer
else
	echo "Composer found - updating..."
    php composer.phar self-update
fi

if [[ ! -e "chromedriver" ]]
then
	wget -N http://chromedriver.storage.googleapis.com/2.9/chromedriver_linux64.zip
	unzip chromedriver_linux64.zip
	rm chromedriver_linux64.zip
fi
chmod +x chromedriver

if [[ ! -e "selenium-server-standalone-2.39.0.jar" ]]
then
	wget -N http://selenium.googlecode.com/files/selenium-server-standalone-2.39.0.jar
fi
chmod +x selenium-server-standalone-2.39.0.jar

php composer.phar install --dev
exit 0