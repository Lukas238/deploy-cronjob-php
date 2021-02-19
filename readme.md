# deploy-cronjob-php 


This is a poor's man Jenkins, to deploy, using a "git pull" command, a static website, 1 minute after receving a webhook from the repository.


## Cronjob setup

1. Change to the root user: `sudo su`
2. Edit the crontab: `crontab -e`
3. Add a new job to run every minute: `* * * * * /usr/bin/php /var/www/deploy/deploy.php`
    - Replace `/usr/bin/php` with your system PHP path. You can find it using `whereis php`.
    - Replace `/var/www/deploy/deploy.php` wiht the full path for the deplopy.php file in your instalation.
