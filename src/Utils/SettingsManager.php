<?php

namespace Drupal\Console\Core\Utils;

use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;
use Drupal\Console\Core\Command\Command;
use Drupal\Console\Core\Utils\ConfigurationManager;
use Drupal\Console\Core\Utils\NestedArray;

/**
 * Class SettingsManager.
 */
class SettingsManager
{
    /**
     * @var ConfigurationInterface
     */
    private $configurationManager = null;

    /**
     * @var TranslatorManagerInterface
     */
    protected $translator = null;

    /**
     * @var NestedArray
     */
    protected $nestedArray = null;

    /**
     * ChainDiscovery constructor.
     *
     * @param ConfigurationManager       $configurationManager
     * @param TranslatorManagerInterface $translator
     * @param NestedArray $nestedArray
     */
    public function __construct(
      ConfigurationManager $configurationManager,
      TranslatorManagerInterface $translator,
      NestedArray $nestedArray
    ) {
        $this->configurationManager = $configurationManager;
        $this->translator = $translator;
        $this->nestedArray = $nestedArray;
    }

    /**
     * @param $name
     * @param $value
     *
     * @return int
     */
    public function set($name, $value, $io)
    {
        $parser = new Parser();
        $dumper = new Dumper();

        $userConfigFile = sprintf(
          '%s/.console/config.yml',
          $this->configurationManager->getHomeDirectory()
        );

        if (!file_exists($userConfigFile)) {
            $io->error(
              sprintf(
                $this->translator->trans('commands.settings.set.messages.missing-file'),
                $userConfigFile
              )
            );
            return 1;
        }

        try {
            $userConfigFileParsed = $parser->parse(
              file_get_contents($userConfigFile)
            );
        } catch (\Exception $e) {
            $io->error(
              $this->translator->trans(
                'commands.settings.set.messages.error-parsing'
              ) . ': ' . $e->getMessage()
            );
            return 1;
        }

        $parents = array_merge(['application'], explode(".", $name));

        $this->nestedArray->setValue(
          $userConfigFileParsed,
          $parents,
          $value,
          true
        );

        try {
            $userConfigFileDump = $dumper->dump($userConfigFileParsed, 10);
        } catch (\Exception $e) {
            $io->error(
              [
                $this->translator->trans('commands.settings.set.messages.error-generating'),
                $e->getMessage()
              ]
            );

            return 1;
        }

        if ($name == 'language') {
            $this->getApplication()
              ->getTranslator()
              ->changeCoreLanguage($value);

            $translatorLanguage = $this->getApplication()->getTranslator()->getLanguage();
            if ($translatorLanguage != $value) {
                $io->error(
                  sprintf(
                    $this->translator->trans('commands.settings.set.messages.missing-language'),
                    $value
                  )
                );

                return 1;
            }
        }

        try {
            file_put_contents($userConfigFile, $userConfigFileDump);
        } catch (\Exception $e) {
            $io->error(
              [
                $this->translator->trans('commands.settings.set.messages.error-writing'),
                $e->getMessage()
              ]
            );

            return 1;
        }

        $io->success(
          sprintf(
            $this->translator->trans('commands.settings.set.messages.success'),
            $name,
            $value
          )
        );

        return 0;
    }
}
