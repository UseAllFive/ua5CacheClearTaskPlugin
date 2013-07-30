ua5CacheClearTaskPlugin
=======================

Introduction
------------
Override the default `./symfony cache:clear` / `./symfony cc` command to not remove `.gitignore` files.  This is useful when you have a `.gitignore` file in your cache directory.

Installation
------------

1. Download the plugin
2. Enable the plugin in `config/ProjectConfiguration.class.php`
3. edit `./symfony` to use the plugin's `cli.php` file intead of symfony's, as was done in the following diff:
    * **NOTE:** We are using composer for our dependencies, and so our path to symfony is `lib/vendor/symfony/symfony1`, compared to the standard `lib/vendor/symfony`.  Please make your edits according to your own setup.

```diff
diff -Naur a/symfony b/symfony
@@ -11,4 +11,9 @@

 chdir(dirname(__FILE__));
 require_once(dirname(__FILE__).'/config/ProjectConfiguration.class.php');
-include(sfCoreAutoload::getInstance()->getBaseDir().'/command/cli.php');
+
+//-- Require Autoload here so we can have a ua5-specific cli.php in ua5CacheClearPlugin
+require_once(dirname(__FILE__).'/lib/vendor/symfony/symfony1/lib/autoload/sfCoreAutoload.class.php');
+sfCoreAutoload::register();
+require_once(dirname(__FILE__).'/plugins/ua5CacheClearTaskPlugin/lib/command/ua5CommandApplication.class.php');
+include(dirname(__FILE__).'/plugins/ua5CacheClearTaskPlugin/lib/command/cli.php');
```
