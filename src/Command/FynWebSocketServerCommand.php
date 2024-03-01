<?php

namespace App\Command;

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Service\ChatService;

class FynWebSocketServerCommand extends Command
{
    protected static $defaultName = 'app:fyn-websocket-server';

    private $chatService;

    public function __construct(ChatService $chatService)
    {
        parent::__construct();

        $this->chatService = $chatService;
    }

    protected function configure()
    {
        $this->setDescription('Starts the WebSocket server for the chat');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $server = IoServer::factory(
            new HttpServer(
                new WsServer($this->chatService)
            ),
            8080
        );

        $output->writeln('WebSocket server started on port 8080.');
        $server->run();
    }
}
