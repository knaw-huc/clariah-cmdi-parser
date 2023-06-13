FROM php:7.4-apache

COPY classes /var/www/html/classes
COPY tweaker /var/www/html/tweaker

EXPOSE 80