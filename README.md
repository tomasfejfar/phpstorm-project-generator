# Share PhpStorm settings in team

> **Warning: Early alpha version, use at you own risk!**

* update paths in `\PhpStormGen\ConfigFiles\PathHelperFactory`

## Usage

* Make sure you backup:
  * your `~/.PhpStormXY` folder
  * your `/project/.idea` folder
  * ideally make sure you're using settings repository in PhpStorm
  
### Export current project's code style
* run `php /path/to/this/repo/bin/console ex -vvv` in your project (make sure to backup your setting first)

### Set current project to stored code style 
* run `php /path/to/this/repo/bin/console ex -vvv` in your project (make sure to backup your setting first)
