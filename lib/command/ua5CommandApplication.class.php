<?php


class ua5CommandApplication extends sfSymfonyCommandApplication
{
  public function unsetTask(sfTask $task)
  {
    foreach ($task->getAliases() as $alias)
    {
      if (isset($this->tasks[$alias]))
      {
        unset($this->tasks[$alias]);
      }
    }
    unset($this->tasks[$task->getFullName()]);

  }

  public function registerTask(sfTask $task)
  {
    if (method_exists($task, 'preRegister')) {
      $task->preRegister($this);
    }

    return parent::registerTask($task);
  }

}
