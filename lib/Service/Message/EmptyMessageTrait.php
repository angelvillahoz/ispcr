<?php
namespace CCR\BLAT\Service\Message;

// Third-party libraries
use Psr\Http\Message\ServerRequestInterface;
trait EmptyMessageTrait
{
    public static function fromRequest(ServerRequestInterface $request): self
    {
        return new self();
    }
    public function jsonSerialize(): array
    {
        return [];
    }
}
