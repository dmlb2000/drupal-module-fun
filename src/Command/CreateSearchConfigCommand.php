<?php

namespace Drupal\pacifica_devel\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\Console\Core\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;


/**
 * Class CreateSearchConfigCommand.
 *
 * Drupal\Console\Annotations\DrupalCommand (
 *     extension="pacifica_devel",
 *     extensionType="module"
 * )
 */
class CreateSearchConfigCommand extends ContainerAwareCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setName('pacifica_devel:create:search_config')
      ->setDescription($this->trans('commands.pacifica_devel.create.search_config.description'))
      ->addOption('prefix', '', InputOption::VALUE_REQUIRED,
        $this->trans('commands.pacifica_devel.default.options.prefix'));
  }


  /**
   * {@inheritdoc}
   */
  private function findFieldStorageFromVid($vid) {
    $prefix = 'field.storage';
    foreach(\Drupal\field\Entity\FieldConfig::loadMultiple() as $field_config) {
      $deps = $field_config->get('dependencies');
      $field_storage_id = null;
      $found_taxonomy = False;
      foreach($deps['config'] as $config_id) {
        if ('taxonomy.vocabulary.'.$vid === $config_id) {
          $found_taxonomy = True;
          break;
        }
        if (substr($config_id, 0, strlen($prefix)) === $prefix) {
          $field_storage_id = $config_id;
        }
      }
      if ($found_taxonomy) {
        break;
      }
    }
    return $field_storage_id;
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $prefix = $input->getOption('prefix');
    \Drupal::configFactory()->getEditable('search_api.server.'.$prefix.'_solr_server')
      ->setData(array(
        'uuid' => \Drupal::service('uuid')->generate(),
        'id' => $prefix.'_solr_server',
        'name' => ucfirst($prefix).' Solr Server',
        'backend' => 'search_api_solr',
        'backend_config' => array(
          'connector' => 'standard',
          'connector_config' => array(
            'scheme' => 'http',
            'host' => 'localhost',
            'port' => 8983,
            'path' => '/',
            'core' => $prefix,
            'solr_install_dir' => '/opt/solr',
          )
        )
      ))
      ->save();
    $index_data = array(
      'uuid' => \Drupal::service('uuid')->generate(),
      'id' => $prefix.'_solr_index',
      'name' => ucfirst($prefix).' Solr Index',
      'read_only' => False,
      'server' => $prefix.'_solr_server',
      'options' => array(
        'index_directly' => False,
        'cron_limit' => 50
      ),
      'dependencies' => array(
        'config' => array('search_api.server.'.$prefix.'_solr_server'),
        'module' => array('node', 'taxonomy', 'search_api', 'search_api_solr')
      ),
      'field_settings' => array(
        $prefix.'_category' => array(
          'label' => ucfirst($prefix).' Category',
          'datasource_id' => 'entity:node',
          'property_path' => $prefix.'_category',
          'type' => 'integer'
        ),
        $prefix.'_tags' => array(
          'label' => ucfirst($prefix).' Tags',
          'datasource_id' => 'entity:node',
          'property_path' => $prefix.'_tags',
          'type' => 'integer'
        ),
        'name' => array(
          'label' => 'Name',
          'datasource_id' => 'entity:taxonomy_term',
          'property_path' => 'name',
          'type' => 'string'
        ),
        'title' => array(
          'label' => 'Title',
          'datasource_id' => 'entity:node',
          'property_path' => 'title',
          'type' => 'string'
        )
      ),
      'datasource_settings' => array(
        'entity:node' => array(
          'bundles' => array(
            'default' => False,
            'selected' => array()
          )
        ),
        'entity:taxonomy_term' => array(
          'bundles' => array(
            'default' => False,
            'selected' => array()
          )
        )
      )
    );
    foreach(\Drupal\taxonomy\Entity\Vocabulary::loadMultiple() as $vid => $vocab) {
      if (substr($vid, 0, strlen($prefix)) === $prefix) {
        $vid_fs = $this->findFieldStorageFromVid($vid);
        $short_vid_fs = end(explode('.', $vid_fs));
        array_push($index_data['datasource_settings']['entity:taxonomy_term']['bundles']['selected'], $vid);
        array_push($index_data['dependencies']['config'], $vid_fs);
        $index_data['field_settings'][$short_vid_fs] = array(
          'label' => $vocab->label(),
          'datasource_id' => 'entity:node',
          'property_path' => $short_vid_fs,
          'type' => 'integer',
          'dependencies' => array(
            'config' => array(
              $vid_fs
            )
          )
        );
      }
    }
    foreach(\Drupal\node\Entity\NodeType::loadMultiple() as $ctid => $content_type) {
      if (substr($ctid, 0, strlen($prefix)) === $prefix) {
        array_push($index_data['datasource_settings']['entity:node']['bundles']['selected'], $ctid);
        array_push($index_data['dependencies']['config'], 'field.storage.node.'.$ctid);
        $index_data['field_settings'][$ctid] = array(
          'label' => $content_type->label(),
          'datasource_id' => 'entity:node',
          'property_path' => $ctid,
          'type' => 'integer',
          'dependencies' => array(
            'config' => array(
              'field.storage.node.'.$ctid
            )
          )
        );
      }
    }
    \Drupal::configFactory()->getEditable('search_api.index.'.$prefix.'_solr_index')
      ->setData($index_data)
      ->save();
  }

}
