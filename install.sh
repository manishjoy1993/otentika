#!/bin/bash
DBNAME='owebcom_magento'
DBHOST='localhost'
DBUSER='owebcom'
DBPASS='n0%s(JX8P4xS'
URL='http://otentikaweb.com/'
FIRSTNAME='Manish'
LASTNAME='Prakash'
EMAIL='manisharies.iitg@gmail.com'
USERNAME='admin'
PASSWORD='java@123'
SAMPLE='y'

#read -ep"Database: " DBNAME
#read -ep"Database username: " DBUSER
#read -ep"Database password: " DBPASS
#read -ep"Magento URL: http://" URL
#read -ep"Magento admin firstname: " FIRSTNAME
#read -ep"Magento admin lastname: " LASTNAME
#read -ep"Magento admin e-mail: " EMAIL
#read -ep"Magento admin username: " USERNAME
#read -ep"Magento admin password: " PASSWORD
#read -ep"Install sample data? [y/n]: " SAMPLE

if [ "$SAMPLE" == "y" ]
then
    rm 2.1.2.tar.gz
	wget https://github.com/magento/magento2/archive/2.1.2.tar.gz
	rm -rf magento
	echo "unzipping magento"
	tar -zxf 2.1.2.tar.gz
	mv magento2-2.1.2 magento
	#mv magento/* magento/.htaccess* .
	chmod -R o+w magento/app/etc magento/pub magento/var
	echo "creating db and importing sample file"
	#mysql -u $DBUSER -p$DBPASS -e "DROP DATABASE IF EXISTS $DBNAME;"
	#mysql -u $DBUSER -p$DBPASS -e "CREATE DATABASE IF NOT EXISTS $DBNAME;"
	chmod o+w magento/var magento/var/.htaccess magento/app/etc
	cp auth.json magento/auth.json
	cd magento
	composer install
	php bin/magento sampledata:deploy
	cd ..

	rm -rf web
	cd magento
	mv -f * ../
	#chmod -R 777 web	
	cd ..
else
	echo "Unknown value for sample data. Should be 'y' or 'n', exiting ..."
	exit
fi	
echo "installing magento"
#cd web 
php bin/magento setup:install --base-url=$URL \
--db-host=$DBHOST --db-name=$DBNAME --db-user=$DBUSER --db-password=$DBPASS \
--admin-firstname=$FIRSTNAME --admin-lastname=$LASTNAME --admin-email=$EMAIL \
--admin-user=$USERNAME --admin-password=$PASSWORD --language=en_US \
--currency=USD --timezone=America/Chicago --use-rewrites=1 --backend-frontname=admin


php bin/magento deploy:mode:set developer
php bin/magento cache:disable
php bin/magento indexer:reindex
php bin/magento cron:run
php bin/magento setup:cron:run



chmod -R 777 var/ pub/

echo "installing magento complete"

echo "installing magento complete"
echo "URL: $URL"
echo "Admin User: $USERNAME"
echo "Admin Pssword: $PASSWORD"
