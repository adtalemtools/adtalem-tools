![Image description](img/adtalem-mt-logo.png)

Adtalem Drupal CLI Tools
====

Drush commands and shell scripts that help manage the Adtalem Drupal projects.

## Requirements
- Drush ^9.4
- Drupal 8

## Installation
Since this is a [global Drush command](http://docs.drush.org/en/master/commands/#global-drush-commands), it will only be
found when installed in certain directories. It is recommended to update your Composer installers path for drupal-drush
packages to:
 ```
 "drush/Commands/{$name}": ["type:drupal-drush"]
 ```
 Then install it as usual: 
 ```
 composer require adtalemtools/adtalem-tools
 ```

## User Commands

### drush users:adminlist
Lists Drupal admin users in a table format. See `drush users:adminlist --help`
for filtering options.

Aliases: admins-l, admins-list, list-admins

### drush users:admin
Restores administrator role to Content Admins on lower environments. See `drush users:admin --help`
for filtering options.

Aliases: uadmin, user-admin, admin-users