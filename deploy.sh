echo "---------------- running pull command ----------------"
git pull origin master
echo "---------------- pull command complete ----------------"
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy
php bin/magento setup:static-content:deploy fr_FR
grunt less:view
chmod -R 777 var/
chmod -R 777 pub/