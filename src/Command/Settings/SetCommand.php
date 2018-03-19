<?php

/**
 * @file
 * Contains \Drupal\Console\Core\Command\Settings\SetCommand.
 */

namespace Drupal\Console\Core\Command\Settings;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\Console\Core\Command\Command;
use Drupal\Console\Core\Utils\SettingsManager;

/**
 * Class SetCommand
 *
 * @package Drupal\Console\Core\Command\Settings
 */
class SetCommand extends Command
{
    /**
     * @var SettingsManager
     */
    protected $settingsManager;

    /**
     * CheckCommand constructor.
     *
     * @param SettingsManager $settingsManager
     */
    public function __construct(
      SettingsManager $settingsManager
    ) {
        $this->settingsManager = $settingsManager;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('settings:set')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                $this->trans('commands.settings.set.arguments.name'),
                null
            )
            ->addArgument(
                'value',
                InputArgument::REQUIRED,
                $this->trans('commands.settings.set.arguments.value'),
                null
            )
            ->setDescription($this->trans('commands.settings.set.description'));
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $settingName = $input->getArgument('name');
        $settingValue = $input->getArgument('value');
        return $this->settingsManager->set($settingName, $settingValue, $this->getIo());
    }
}
