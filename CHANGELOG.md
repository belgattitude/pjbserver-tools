# CHANGELOG

## 4.0.0 (2021-04-19)

- Support for php 8.0 and php 7.4
- Removed support for php 7.1, 7.2 and 7.3

## 3.1.0 (2019-12-21)

- Support for symfony/console ^5.0

## 3.0.0 (2019-02-18)

- No breaking changes. Just more type checks and support for symfony/console ^4.0.

### Added

- Support for symfony/console ^4.0

### Changed

- PHP 7.1 with strict_types.
- PHP 7.2 and 7.3 tested.
- PHPUnit upgraded to 7.5

### Removed

- Drop support for hhvm (travis).
- Drop support for soluble/japha < 2.5.0.
- Drop support for symfony/console < 3.0.0.

## 2.2.0 (2017-09-18)

### Updated

- Updated standalone server to [php-java-bridge 7.1.3](https://sourceforge.net/projects/php-java-bridge/files/Binary%20package/),
  you can still explicitly set the 6.2.1 version in the config `'server_jar' => "/my/path/pjb621_standalone/JavaBridge.jar"`

## 2.1.2 (2017-01-18)

### Improved

- Updated documentation

### Removed

- Removed obsolete resources in `./tools` directory

## 2.1.1 (2017-01-15)

### Changed

- Updated embedded `JavaBridge.jar` from [belgattitude/php-java-bridge](https://github.com/belgattitude/php-java-bridge) latest build.
- Preliminary support for pre/post composer hooks

### Fixed

- [#7](https://github.com/belgattitude/pjbserver-tools/pull/7) fix markdown in README.md
- HHVM badge updated for testing HHVM 3.9


## 2.1.0 (2016-10-27)

### Changed

- Refactored system process handling (`PjbServer\Tools\System\Process`)
- Console, start and stop commands messages :
  - pjbserver:start inform if the server was already started.
  - pjbserver:stop inform if the server was already stopped.

## 2.0.6 (2016-10-24)

### Added

- Nothing

### Changed

- Reworked default configuration, 'log_file', 'pid_file' and 'server_jar' are now
  commented and use defaults.

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- Nothing


## 2.0.5 (2016-10-24)

### Added

- Experimental support for Mac OSX
- Added PHP7.1 tests in travis

### Deprecated

- Substitution of {base_dir} and {tcp_port} variables in config variables does not prove
  a good option, they'll be removed for the v3.0.0 release. See
  [#4](https://github.com/belgattitude/pjbserver-tools/issues/4)

### Removed

- Nothing.

### Fixed

- [#3](https://github.com/belgattitude/pjbserver-tools/issues/3) fix an
  incompatibility with OSX using 'ps --no-headers' to check if pid is running.
  Replaced by 'kill -0' which should be more compatible.


## 2.0.4 (2016-09-06)

- Added possibility to modify standalone server number of threads from config file.
- New command to reveal internal cli command used to start the server
  see ./bin/pjbservertools pjbserver:reveal ./config/pjbserver.config.php.dist

## 2.0.3 (2016-07-04)

- Added composer commands aliases with default configuration file
  from ./config/pjbserver/config.php.dist and verbosity level to debug.
  Will start on port 8089 by default.
    - composer pjbserver:start
    - composer pjbserver:stop
    - composer pjbserver:restart
    - composer pjbserver:status

- Added console command to get the status of the server
  - ./bin/pjbservertools pjbserver:status <config_file>
- Added console logger to all commands:
  - To get more verbose message try the usual command with -v, -vv or -vvv
    i.e: ./bin/pjbservertools -vvv pjbserver:start ./config/pjbserver.config.php.dist

## 2.0.2 (2016-07-03)

- Fix travis builds (no notable changes)

## 2.0.1 (2016-07-03)

- Fix for multiple jar entries (*.jar)

## 2.0.0 (2016-07-03)

- Refactored usage, config params must now be passed in the `StandaloneServer\Config($params)` object.
- Support for PSR-3 logger interface.
- Console commands added (symfony/console)
  - ./bin/pjbservertools pjbserver:start <config_file>
  - ./bin/pjbservertools pjbserver:stop <config_file>
  - ./bin/pjbservertools pjbserver:restart <config_file>



## 1.1.0 (2016-07-02)

- PHP 5.5 as minimum required version.
- Improved unit testing and documentation.
- Added method `StandaloneServer::isProcessRunning()`.

