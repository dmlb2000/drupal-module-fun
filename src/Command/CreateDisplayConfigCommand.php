<?php

namespace Drupal\pacifica_devel\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\Console\Core\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;


/**
 * Class CreateDisplayConfigCommand.
 *
 * Drupal\Console\Annotations\DrupalCommand (
 *     extension="pacifica_devel",
 *     extensionType="module"
 * )
 */
class CreateDisplayConfigCommand extends ContainerAwareCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setName('pacifica_devel:create:display_config')
      ->setDescription($this->trans('commands.pacifica_devel.create.display_config.description'))
      ->setHelp($this->trans('commands.pacifica_devel.create.display_config.help'))
      ->addOption('prefix', '', InputOption::VALUE_REQUIRED,
        $this->trans('commands.pacifica_devel.default.options.prefix'));
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $prefix = $input->getOption('prefix');
  }

}
