<?php
namespace CCR\BLAT\Service\Dispatcher;

// BLAT libraries with namespaces
use CCR\BLAT\Service\Exception\HandlerNotFoundException;
/**
 * Interface for resolving the callable to handle a message.
 */
interface CallableResolverInterface
{
    /**
     * Resolves the callable that handles the given message.
     * @param object $message Message to resolve the callable for.
     * @throws HandlerNotFoundException If no callable can be resolved to handle
     *     the given message.
     * @return callable The callable that handles the given message.
     */
    public function resolve($message): callable;
}
