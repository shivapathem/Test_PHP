
## About Facility Catalogue
Coming Soon

## Prerequest of Project setup
1. IIS installation and configuration
2. URLrewrite module installation
3. php.ini configuration

## Installation and Project setup

git clone git@bitbucket.org:bbcworldwide/schedulingandpayroll-allocate7.git

## Navigate to Laravel branch
git checkout Dev/ALLOCATE7-FAST-BAU-FACILITYBOOKINGS

## Below content of .env must have in project root(src/mvc-app/.env)
APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:PSxTIS2VDJSKxPw89BsJvDt77ToApbVrKVbcnDNMCSA=
APP_DEBUG=true
APP_URL=http://localhost
LOG_CHANNEL=stack
DB_CONNECTION=sqlsrv
DB_HOST=dev-clus15-lsn1.national.core.bbc.co.uk
DB_PORT=
DB_DATABASE=Allocate7_WP
DB_USERNAME=
DB_PASSWORD=
BROADCAST_DRIVER=pusher
CACHE_DRIVER=file
QUEUE_CONNECTION=database
SESSION_DRIVER=file
SESSION_LIFETIME=120
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=mt1
MIX_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
MIX_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
MAIL_MAILER=smtp
MAIL_HOST=smtp.national.core.bbc.co.uk
MAIL_PORT=25
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=noreply@bbc.co.uk
MAIL_FROM_NAME="Allocate"

## out site of project root src/web.config should have below content
<?xml version="1.0" encoding="UTF-8"?>
<configuration>
    <system.webServer>
        <rewrite>
            <rules>
                <rule name="MVC APP Rule 1" stopProcessing="true">
                    <match url="^mvc-app(.*)/$" ignoreCase="false" />
                    <conditions>
                        <add input="{REQUEST_FILENAME}" matchType="IsDirectory" ignoreCase="false" negate="true" />
                    </conditions>
                    <action type="Redirect" redirectType="Permanent" url="mvc-app/{R:1}" />
                </rule>
                <rule name="MVC APP Rule 2" stopProcessing="true">
                    <match url="^mvc-app" ignoreCase="false" />
                    <conditions>
                        <add input="{REQUEST_FILENAME}" matchType="IsDirectory" ignoreCase="false" negate="true" />
                        <add input="{REQUEST_FILENAME}" matchType="IsFile" ignoreCase="false" negate="true" />
                    </conditions>
                    <action type="Rewrite" url="mvc-app/public/index.php" />
                </rule>
                <rule name="Rewrite Images Vendor" stopProcessing="true">
                    <match url="^images/vendor/(.*)" />
                    <action type="Rewrite" url="mvc-app/public/images/vendor/{R:1}" />
                </rule>
                <rule name="Rewrite Fonts Vendor" stopProcessing="true">
                    <match url="^fonts/vendor/(.*)" />
                    <action type="Rewrite" url="mvc-app/public/fonts/vendor/{R:1}" />
                </rule>
            </rules>
        </rewrite>
    </system.webServer>
</configuration>

## Laravel log directory (Outside Project)
Laravel logs are configured to be written outside the 'src' directory for security and organization.
Folder and File Permissions:
Grant Modify/ Write permission.

base_path('../../logs/laravel/laravel.log')
 
##