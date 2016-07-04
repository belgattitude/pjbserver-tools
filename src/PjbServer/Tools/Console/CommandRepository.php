<?php

namespace PjbServer\Tools\Console;

class CommandRepository
{

    /**
     * @var array
     */
    protected $commands;

    public function __construct()
    {
        $this->commands = [
            'pjbserver:start' => new Command\PjbServerStartCommand(),
            'pjbserver:restart' => new Command\PjbServerRestartCommand(),
            'pjbserver:stop' => new Command\PjbServerStopCommand(),
            'pjbserver:status' => new Command\PjbServerStatusCommand()
        ];
    }

    /**
     * @param $name
     * @return \Symfony\Component\Console\Command\Command
     */
    public function getRegisteredCommand($name)
    {
        return $this->commands[$name];
    }

    /**
     * Return all registered commands
     * @return array
     */
    public function getRegisteredCommands()
    {
        return $this->commands;
    }
}
