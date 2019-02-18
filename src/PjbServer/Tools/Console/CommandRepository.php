<?php

declare(strict_types=1);

namespace PjbServer\Tools\Console;

use Symfony\Component\Console\Command\Command as ConsoleCommand;

class CommandRepository
{
    /**
     * @var array<string, ConsoleCommand>
     */
    protected $commands;

    public function __construct()
    {
        $this->commands = [
            'pjbserver:start' => new Command\PjbServerStartCommand(),
            'pjbserver:restart' => new Command\PjbServerRestartCommand(),
            'pjbserver:stop' => new Command\PjbServerStopCommand(),
            'pjbserver:status' => new Command\PjbServerStatusCommand(),
            'pjbserver:reveal' => new Command\PjbServerRevealCommand()
        ];
    }

    public function getRegisteredCommand(string $name): ConsoleCommand
    {
        return $this->commands[$name];
    }

    /**
     * Return all registered commands.
     *
     * @return array<string, ConsoleCommand>
     */
    public function getRegisteredCommands(): array
    {
        return $this->commands;
    }
}
