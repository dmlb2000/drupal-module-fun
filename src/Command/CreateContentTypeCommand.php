<?php

namespace Drupal\pacifica_devel\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\Console\Core\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;
use Drupal\Core\Field\FieldStorageDefinitionInterface;


/**
 * Class CreateContentTypeCommand.
 *
 * Drupal\Console\Annotations\DrupalCommand (
 *     extension="pacifica_devel",
 *     extensionType="module"
 * )
 */
class CreateContentTypeCommand extends ContainerAwareCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setName('pacifica_devel:create:content_type')
      ->setDescription($this->trans('commands.pacifica_devel.default.description'))
      ->setHelp($this->trans('commands.pacifica_devel.default.help'))
      ->addOption('prefix', '', InputOption::VALUE_REQUIRED,
        $this->trans('commands.pacifica_devel.default.options.prefix'))
      ->addOption('name', '', InputOption::VALUE_REQUIRED,
        $this->trans('commands.pacifica_devel.default.options.name'));
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $prefix = $input->getOption('prefix');
    $name = $input->getOption('name');
    $this->create_vocabularies($prefix, $name);
    $this->create_content_type($prefix, $name);
    $this->create_field_storage($prefix, $name);
    $this->create_fields($prefix, $name);
  }

  /**
   * {@inheritdoc}
   */
  private function create_fields($prefix, $name) {
    $new_fields = array(array(
      'field_name' => $prefix.'_'.$name.'_type',
      'entity_type' => 'node',
      'bundle' => $prefix.'_'.$name,
      'label' => ucfirst($prefix).' '.ucfirst($name).' Type',
      'field_storage' => \Drupal\field\Entity\FieldStorageConfig::loadByName('node', $prefix.'_'.$name.'_type'),
      'settings' => array(
        'handler' => 'default:taxonomy_term',
        'handler_settings' => array(
          'target_bundles' => array(
            $prefix.'_'.$name.'_types' => $prefix.'_'.$name.'_types'
          )
        )
      )
    ), array(
      'field_name' => $prefix.'_tags',
      'entity_type' => 'node',
      'bundle' => $prefix.'_'.$name,
      'label' => ucfirst($prefix).' Tags',
      'field_storage' => \Drupal\field\Entity\FieldStorageConfig::loadByName('node', $prefix.'_tags'),
      'settings' => array(
        'handler' => 'default:taxonomy_term',
        'handler_settings' => array(
          'target_bundles' => array(
            $prefix.'_tags' => $prefix.'_tags'
          )
        )
      )
    ), array(
      'field_name' => $prefix.'_category',
      'entity_type' => 'node',
      'bundle' => $prefix.'_'.$name,
      'label' => ucfirst($prefix).' Category',
      'field_storage' => \Drupal\field\Entity\FieldStorageConfig::loadByName('node', $prefix.'_category'),
      'settings' => array(
        'handler' => 'default:taxonomy_term',
        'handler_settings' => array(
          'target_bundles' => array(
            $prefix.'_category' => $prefix.'_category'
          )
        )
      )
    ));
    foreach($new_fields as $new_field_config) {
      if (!\Drupal\field\Entity\FieldConfig::load('node.'.$prefix.'_'.$name.'.'.$new_field_config['field_name'])) {
        $new_field = \Drupal\field\Entity\FieldConfig::create($new_field_config);
        $new_field->save();
      }
    }
    foreach(\Drupal\node\Entity\NodeType::loadMultiple() as $ctid => $content_type) {
      if (substr($ctid, 0, strlen($prefix)) === $prefix) {
        if (!\Drupal\field\Entity\FieldConfig::load('node.'.$ctid.'.'.$prefix.'_'.$name)) {
          $new_field = \Drupal\field\Entity\FieldConfig::create(array(
            'field_name' => $prefix.'_'.$name,
            'entity_type' => 'node',
            'bundle' => $ctid,
            'label' => ucfirst($prefix).' '.ucfirst($name),
            'field_storage' => \Drupal\field\Entity\FieldStorageConfig::loadByName('node', $prefix.'_'.$name),
            'settings' => array(
              'handler' => 'default:node',
              'handler_settings' => array(
                'target_bundles' => array(
                  $prefix.'_'.$name => $prefix.'_'.$name
                )
              )
            )
          ));
          $new_field->save();
        }
        if ($ctid !== $prefix.'_'.$name) {
          if (!\Drupal\field\Entity\FieldConfig::load('node.'.$prefix.'_'.$name.'.'.$ctid)) {
            $new_field = \Drupal\field\Entity\FieldConfig::create(array(
              'field_name' => $ctid,
              'entity_type' => 'node',
              'bundle' => $prefix.'_'.$name,
              'label' => $content_type->label(),
              'field_storage' => \Drupal\field\Entity\FieldStorageConfig::loadByName('node', $ctid),
              'settings' => array(
                'handler' => 'default:node',
                'handler_settings' => array(
                  'target_bundles' => array(
                    $ctid => $ctid
                  )
                )
              )
            ));
            $new_field->save();
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  private function create_field_storage($prefix, $name) {
    $field_storages = array(array(
      'field_name' => $prefix.'_'.$name.'_type',
      'type' => 'entity_reference',
      'entity_type' => 'node',
      'settings' => array(
        'target_type' => 'taxonomy_term'
      ),
      'cardinality' => 1
    ), array(
      'field_name' => $prefix.'_category',
      'type' => 'entity_reference',
      'entity_type' => 'node',
      'settings' => array(
        'target_type' => 'taxonomy_term'
      ),
      'cardinality' => 1
    ), array(
      'field_name' => $prefix.'_tags',
      'type' => 'entity_reference',
      'entity_type' => 'node',
      'settings' => array(
        'target_type' => 'taxonomy_term'
      ),
      'cardinality' => FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED
    ), array(
      'field_name' => $prefix.'_'.$name,
      'type' => 'entity_reference',
      'entity_type' => 'node',
      'settings' => array(
        'target_type' => 'node'
      ),
      'cardinality' => FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED
    ));
    foreach($field_storages as $field_stor_config){
      if (!\Drupal\field\Entity\FieldStorageConfig::load('node.'.$field_stor_config['field_name'])) {
        $field_storage = \Drupal\field\Entity\FieldStorageConfig::create($field_stor_config);
        $field_storage->save();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  private function create_content_type($prefix, $name) {
    if(!\Drupal\node\Entity\NodeType::load($prefix.'_'.$name)) {
      $content_type = \Drupal\node\Entity\NodeType::create(array(
        'type' => $prefix.'_'.$name,
        'name' => ucfirst($prefix).' '.ucfirst($name),
        'description' => ucfirst($name).' for Content in '.ucfirst($prefix),
        'title_label' => ucfirst($prefix).' '.ucfirst($name),
        'base' => $prefix.'_'.$name,
        'custom' => TRUE
      ));
      $content_type->save();
    }
  }

  /**
   * {@inheritdoc}
   */
  private function create_vocabularies($prefix, $name) {
    $new_vocabs = array(array(
      'vid' => $prefix.'_category',
      'description' => 'Categories for content types '.$prefix,
      'name' => ucfirst($prefix). ' Categories'
    ), array(
      'vid' => $prefix.'_tags',
      'description' => 'Tags for content types '.$prefix,
      'name' => ucfirst($prefix). ' Tags'
    ), array(
      'vid' => $prefix.'_relationships',
      'description' => 'Relationships for content types '.$prefix,
      'name' => ucfirst($prefix).' Relationships'
    ), array(
      'vid' => $prefix.'_'.$name.'_types',
      'description' => 'Types of '.ucfirst($name).' in '.ucfirst($prefix),
      'name' => ucfirst($prefix).' '.ucfirst($name).' Types'
    ));
    $vocabs = \Drupal\taxonomy\Entity\Vocabulary::loadMultiple();

    foreach($new_vocabs as $vocab) {
      if (!isset($vocabs[$vocab['vid']])) {
        $vocabulary = \Drupal\taxonomy\Entity\Vocabulary::create($vocab);
        $vocabulary->save();
      }
    }
  }
}
