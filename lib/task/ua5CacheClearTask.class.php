<?php

class ua5CacheClearTask extends sfCacheClearTask
{
  protected $ignored_files = array(
    ".gitignore",
    ".sf",
    );

  public function preRegister(ua5CommandApplication $commandApplication)
  {
    $commandApplication->unsetTask($this);
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    if (!sfConfig::get('sf_cache_dir') || !is_dir(sfConfig::get('sf_cache_dir')))
    {
      throw new sfException(sprintf('Cache directory "%s" does not exist.', sfConfig::get('sf_cache_dir')));
    }

    // finder to find directories (1 level) in a directory
    $dirFinder = sfFinder::type('dir')->discard($this->ignored_files)->maxdepth(0)->relative();

    // iterate through applications
    $apps = null === $options['app'] ? $dirFinder->in(sfConfig::get('sf_apps_dir')) : array($options['app']);
    foreach ($apps as $app)
    {
      $this->checkAppExists($app);

      if (!is_dir(sfConfig::get('sf_cache_dir').'/'.$app))
      {
        continue;
      }

      // iterate through environments
      $envs = null === $options['env'] ? $dirFinder->in(sfConfig::get('sf_cache_dir').'/'.$app) : array($options['env']);
      foreach ($envs as $env)
      {
        if (!is_dir(sfConfig::get('sf_cache_dir').'/'.$app.'/'.$env))
        {
          continue;
        }

        $this->logSection('cache', sprintf('Clearing cache type "%s" for "%s" app and "%s" env', $options['type'], $app, $env));

        $appConfiguration = ProjectConfiguration::getApplicationConfiguration($app, $env, true);

        $this->lock($app, $env);

        $event = $appConfiguration->getEventDispatcher()->notifyUntil(new sfEvent($this, 'task.cache.clear', array('app' => $appConfiguration, 'env' => $env, 'type' => $options['type'])));
        if (!$event->isProcessed())
        {
          // default cleaning process
          $method = $this->getClearCacheMethod($options['type']);
          if (!method_exists($this, $method))
          {
            throw new InvalidArgumentException(sprintf('Do not know how to remove cache for type "%s".', $options['type']));
          }
          $this->$method($appConfiguration);
        }

        $this->unlock($app, $env);
      }
    }

    // clear global cache
    if (null === $options['app'] && 'all' == $options['type'])
    {
      $this->getFilesystem()->remove(sfFinder::type('file')->discard($this->ignored_files)->in(sfConfig::get('sf_cache_dir')));
    }
  }

  protected function clearConfigCache(sfApplicationConfiguration $appConfiguration)
  {
    $subDir = sfConfig::get('sf_cache_dir').'/'.$appConfiguration->getApplication().'/'.$appConfiguration->getEnvironment().'/config';

    if (is_dir($subDir))
    {
      // remove cache files
      $this->getFilesystem()->remove(sfFinder::type('file')->discard($this->ignored_files)->in($subDir));
    }
  }

  protected function clearModuleCache(sfApplicationConfiguration $appConfiguration)
  {
    $subDir = sfConfig::get('sf_cache_dir').'/'.$appConfiguration->getApplication().'/'.$appConfiguration->getEnvironment().'/modules';

    if (is_dir($subDir))
    {
      // remove cache files
      $this->getFilesystem()->remove(sfFinder::type('file')->discard($this->ignored_files)->in($subDir));
    }
  }
}
