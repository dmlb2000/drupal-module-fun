<?php

namespace Drupal\pacifica_devel\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Drupal\Console\Core\Command\ContainerAwareCommand;
use Drupal\Core\Form\FormState;
use Drupal\Component\Serialization\Yaml;

/**
 * Class PacificaFormSubmitCommand.
 *
 * Drupal\Console\Annotations\DrupalCommand (
 *     extension="pacifica_devel",
 *     extensionType="module"
 * )
 */
class PacificaFormSubmitCommand extends ContainerAwareCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setName('form:submit')
      ->setDescription($this->trans('commands.form.submit.description'))
      ->setHelp($this->trans('commands.form.submit.help'))
      ->addOption(
        'varsfile',
        null,
        InputOption::VALUE_REQUIRED,
        $this->trans('commands.form.submit.options.varsfile'))
      ->addOption(
        'formclass',
        null,
        InputOption::VALUE_REQUIRED,
        $this->trans('commands.form.submit.options.formclass'))
      ->addOption(
        'entityclass',
        null,
        InputOption::VALUE_REQUIRED,
        $this->trans('commands.form.submit.options.entityclass'))
      ->addOption(
        'operation',
        null,
        InputOption::VALUE_REQUIRED,
        $this->trans('commands.form.submit.options.operation'));
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $operation = $input->getOption('operation');
    $formclass = $input->getOption('formclass');
    $entityclass = $input->getOption('entityclass');
    $varsfile = $input->getOption('varsfile');
    $this->getIo()->info('execute');
    $form_state = new FormState();
    $this->getIo()->info(file_get_contents($varsfile));
    $form_state->setValues(Yaml::decode(file_get_contents($varsfile)));
    $entity = $entityclass::create();
    $form = \Drupal::entityTypeManager()->getFormObject($formclass, $operation)->setEntity($entity);
    \Drupal::formBuilder()->submitForm($form, $form_state);
    $this->getIo()->info(print_r($form_state->getValues(), true));
    $this->getIo()->info(print_r($form_state->getErrors(), true));
    $form_state->getFormObject()->getEntity()->save();
  }

}
