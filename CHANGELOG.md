# CHANGELOG

### 2.0.3 (2016-07-04)

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
  
### 2.0.2 (2016-07-03)

- Fix travis builds (no notable changes)

### 2.0.1 (2016-07-03)

- Fix for multiple jar entries (*.jar)

### 2.0.0 (2016-07-03)

- Refactored usage, config params must now be passed in the `StandaloneServer\Config($params)` object.
- Support for PSR-3 logger interface.
- Console commands added (symfony/console)
  - ./bin/pjbservertools pjbserver:start <config_file>
  - ./bin/pjbservertools pjbserver:stop <config_file>
  - ./bin/pjbservertools pjbserver:restart <config_file>
  


### 1.1.0 (2016-07-02)

- PHP 5.5 as minimum required version.
- Improved unit testing and documentation.
- Added method `StandaloneServer::isProcessRunning()`.

