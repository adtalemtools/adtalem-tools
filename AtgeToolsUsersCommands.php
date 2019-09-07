<?php

namespace Drush\Commands\adtalem_tools;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Consolidation\AnnotatedCommand\CommandData;
use Drupal\user\Entity\User;
use Drush\Commands\DrushCommands;
use Drush\Drupal\Commands\core\UserCommands;
use Drush\Exceptions\UserAbortException;
use Symfony\Component\Console\Input\InputOption;

class UsersCommands extends DrushCommands {

  /**
   * Display a list of Drupal users.
   *
   * @command users:adminlist
   *
   * @aliases admins-l, admins-list, list-admins
   * @bootstrap full
   * @field-labels
   *   uid: User ID
   *   name: User name
   *   login: Last login
   *   status: Status
   *   role: User role
   * @table-style default
   * @default-fields uid,name,role,status,login
   * @return \Consolidation\OutputFormatters\StructuredData\RowsOfFields
   */

  public function listAdmins() {
    $query = \Drupal::entityQuery('user')
      ->condition('uid', 0, '!=');

    $query->condition('status', 1);
    $query->condition('roles', 'administrator');

    $ids = $query->execute();

    echo '----------------------------------------------------------------------------------' . PHP_EOL;
    echo 'Administrators' . PHP_EOL;

    if ($users = User::loadMultiple($ids)) {
      $command = new UserCommands();
      $rows = [];
      if (!empty($users)) {
        /** @var \Drupal\user\UserStorage $user_storage */
        foreach ($users as $id => $user) {
          $rows[$id] = $command->infoArray($user);
        }
        $result = new RowsOfFields($rows);
        $result->addRendererFunction([$command, 'renderRolesCell']);
      }
      if (!empty($result)) {
        return $result;
      }
    }
  }

  /**
   * Restore administrator perms to content admins on lower envs.
   *
   * @command users:admin
   *
   * @aliases uadmin, user-admin, admin-users
   * @bootstrap full
   * @return array
   */
  public function adminUsers() {
    $query = \Drupal::entityQuery('user')
      ->condition('uid', 0, '!=');

    $query->condition('status', 1);
    $query->condition('roles', 'content_editor');

    $ids = $query->execute();

    if (!empty($ids)) {
      echo 'Restoring administrator roles' . PHP_EOL;
      echo '----------------------------------------------------------------------------------' . PHP_EOL;
      /** @var \Drupal\user\UserStorage $user_storage */

      foreach ($ids as $user) {
        $account = \Drupal\user\Entity\User::load($user);
        $is_admin = $account->hasRole('administrator');
        if ($is_admin != true) {
          $username = $account->getUsername();
          $account = user_load_by_name($username);
          $account->addRole('administrator');
          $account->save();

          $successmessage = 'Administrator role has been added to ' . $username . '.' . PHP_EOL;

          if (!empty($successmessage)) {
            print $successmessage;
          }
        }
      }
    }

    $completedmessage = 'Administrator role restoration completed.' . PHP_EOL;
    echo '----------------------------------------------------------------------------------' . PHP_EOL;
    if (!empty($completedmessage)) {
      print $completedmessage;
    }
  }
}


