<?php
namespace Drush\Commands\adtalem_tools;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Consolidation\AnnotatedCommand\CommandData;
use Drupal\user\Entity\User;
use Drush\Commands\DrushCommands;
use Drush\Drupal\Commands\core\UserCommands;
use Drush\Exceptions\UserAbortException;
use Symfony\Component\Console\Input\InputOption;

class AtgeToolsDeployCommands extends DrushCommands
{

  /**
   * Get the BLT multisite config as an array.
   *
   * @command deploy:config
   *
   * @param array $options An associative array of options.
   *
   * @option config A named config array.
   * @option item A named config item value.
   * @usage deploy:config --config=adtalem_multisites
   *   Stores the entire multisites config as an array.
   * @usage deploy:config --config=adtalem_multisites --key=remote
   *   Grabs the remote option values from the config array.
   * @usage deploy:config --config=adtalem_multisites --key=remote --site=rossu
   *   Grabs the remote option values from the config array by site.
   * @aliases blt-config
   * @bootstrap full
   * @throws \Exception
   * @return array
   *   The "adtalem_multisites" BLT config as an array.
   */

  function config($options) {

    $site = $options['site'];
    $key = $options['key'];

    if (defined('DRUPAL_ROOT')) {
      $blt_file = DRUPAL_ROOT . '/../blt/blt.yml';
    } else {
      $blt_file = dirname(__FILE__) . '/../..' . '/blt/blt.yml';
    }

    $blt_config = \Symfony\Component\Yaml\Yaml::parseFile($blt_file);

    if (empty($options['site'])) {
      return $blt_config['adtalem_multisites'][$site][$key];
    }

    if (empty($options['key'])) {
      return $blt_config['adtalem_multisites'][0][$key];
    } else {
      return $blt_config['adtalem_multisites'];
    }
  }

}
