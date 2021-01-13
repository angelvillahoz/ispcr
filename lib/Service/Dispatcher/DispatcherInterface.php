<?php
namespace CCR\BLAT\Service\Dispatcher;

// BLAT libraries with namespaces
use CCR\BLAT\Service\Exception\HandlerNotFoundException;
use CCR\BLAT\Service\Message\{CommandInterface, QueryInterface, QueryResult};
/**
 * Interface defining a dispatcher that dispatches a command or query to its
 * handler.
 */
interface DispatcherInterface
{
    /**
     * @param CommandInterface $command Command to dispatch.
     * @throws HandlerNotFoundException If no handler is registered to handle
     *     the command.
     */
    public function send(CommandInterface $command): void;
    /**
     * @param QueryInterface $query Query to dispatch.
     * @return QueryResult The result returned from the handler.
     * @throws HandlerNotFoundException If no handler is registered to handle
     *     the query.
     */
    public function request(QueryInterface $query): QueryResult;
}
