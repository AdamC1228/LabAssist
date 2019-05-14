#!/bin/bash

#Install needed repositories
yum install epel-release -y
yum install http://rpms.remirepo.net/enterprise/remi-release-7.rpm


#Install nice things that will help
yum install yum-utils wget git screen -y


#Install Webserver
yum install httpd -y
systemctl enable httpd


#Install PHP
yum-config-manager --enable remi-php73
yum install php php-ldap php-mbstring php-mysqlnd php-xml php-pgsql -y


#Wrap up with an overall yum update
yum update -y


#PHP has been isntalled, now restart apache
systemctl restart httpd


#Install Postgresql
yum install https://download.postgresql.org/pub/repos/yum/reporpms/EL-7-x86_64/pgdg-redhat-repo-latest.noarch.rpm
yum install postgresql96-server postgresql96-contrib-y
postgresql96-setup initdb
systemctl enable postgresql-9.6
systemctl start postgresql-9.6


#Adding required entry to hba_conf
echo "local   labassist       labassist                               trust" >> /var/lib/pgsql/9.6/data/pg_hba.conf


#Remember to create psql user and create the database. 
#create database labassist
#create user labassist with encrypted password '';
#grant all privileges on database labassist to labassist;


#Set sebool
setsebool -P httpd_can_network_connect on


#Open needed firewall ports
firewall-cmd --zone=public --add-port=80/tcp --permanent
firewall-cmd --zone=public --add-port=443/tcp --permanent
firewall-cmd --reload



echo "Please note the following:"
echo "Packages are installed with basic setup. You may wish to refine the settings to further suit your needs"
echo "PostgreSQL is installed, however you must manually create the user for the site to run as. 
echo "PostgreSQL is installed, however you must manually create the database that the site will use."
echo "Adjust the following lin in /var/lib/pgsql/9.6/data/pg_hba.conf with your database name and user name..."
echo "     local   labassist       labassist                               trust"
echo "ALERT: HTTPD_CAN_CONNECT has been enabled!"
