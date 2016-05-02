###############################################################################
# Dockerfile to build TestArmada's Admiral server
# Based on tutum/lamp
###############################################################################
# Usage:
# docker build -t testarmada/admiral .
# docker run -d -p 8080:80 -p 3306:3306 --name admiral -e MYSQL_PASS="password" testarmada/admiral

FROM tutum/lamp:latest

MAINTAINER Mike Tanaka

# Remove tutum's hello world app and add TestArmada's Admiral
RUN rm -fr /app && git clone --recursive git://github.com/TestArmada/admiral.git /app

# Setup Admiral config files
RUN cp /app/config/sample-localSettings.php /app/config/localSettings.php

# Setup localSettings.json with DB credentials and root context
RUN cp /app/config/sample-localSettings.json /app/config/localSettings.json
RUN cd /app/config; perl -pi -e 's/"username": "root",/"username": "admin",/' localSettings.json
RUN cd /app/config; perl -pi -e 's/"password": "root"/"password": "password"/' localSettings.json
RUN cd /app/config; perl -pi -e 's/"contextpath": "\/testswarm"/"contextpath": "\/"/' localSettings.json

# Update .htaccess to remove <Files> section which throws an error
RUN cp /app/config/sample-.htaccess /app/.htaccess
RUN cd /app; perl -pi -e 's/<Files/#<Files/' .htaccess
RUN cd /app; perl -pi -e 's/Order/#Order/' .htaccess
RUN cd /app; perl -pi -e 's/Deny/#Deny/' .htaccess
RUN cd /app; perl -pi -e 's/<\/Files>/#<\/Files/' .htaccess

# Set root context in .htaccess
RUN cd /app; perl -pi -e 's/RewriteBase \/testswarm/RewriteBase \//' .htaccess

# Add robots.txt file
RUN cp /app/config/sample-robots.txt /app/robots.txt

# Setup app cache
RUN chmod 777 /app/cache

# Update uaparser regexes
RUN cd /app;/usr/bin/php external/ua-parser/php/uaparser-cli.php -g

# Create /mysql-setup.sh to setup Admiral database
RUN echo "/usr/bin/mysql -u admin --password=password -e 'CREATE DATABASE testswarm'" > /mysql-setup.sh
RUN echo "cd /app/scripts;/usr/bin/php install.php" >> /mysql-setup.sh

EXPOSE 80 3306

CMD ["/run.sh"]
