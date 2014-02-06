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

php composer.phar install --dev
exit 0