<?php
namespace CCR\ISPCR\Datasource\Query;

// Third-party libraries
use Psr\Http\Message\ServerRequestInterface;
// ISPCR libraries with namespaces
use CCR\ISPCR\Service\Exception\InvalidMessageException;
use CCR\ISPCR\Service\Message\QueryInterface;
class GetAlignmentList implements QueryInterface
{
    public static function fromRequest(ServerRequestInterface $serverRequestInterface): self
    {
        $parsedBody = $serverRequestInterface->getParsedBody();
        if ( isset($parsedBody["selectedSpeciesScientificName"]) &&
            isset($parsedBody["selectedGenomeAssemblyReleaseVersion"]) &&
            isset($parsedBody["forwardPrime"]) &&
            isset($parsedBody["reversePrime"])) {
            $speciesShortName = explode(")", explode("(", $parsedBody["selectedSpeciesScientificName"])[1])[0];
            $genomeAssemblyReleaseVersion = $parsedBody["selectedGenomeAssemblyReleaseVersion"];
            $forwardPrime = $parsedBody["forwardPrime"];
            $reversePrime = $parsedBody["reversePrime"];
            $outputFormat = "fa";

            return new self(
                $speciesShortName,
                $genomeAssemblyReleaseVersion,
                $forwardPrime,
                $reversePrime,
                $outputFormat
            );
        }

        throw new InvalidMessageException(self::class);
    }

    private $speciesShortName;
    private $genomeAssemblyReleaseVersion;
    private $forwardPrime;
    private $reversePrime;
    private $outputFormat;

    public function __construct(
        string $speciesShortName,
        string $genomeAssemblyReleaseVersion,
        string $forwardPrime,
        string $reversePrime,
        string $outputFormat
    ) {
        $this->speciesShortName = $speciesShortName;
        $this->genomeAssemblyReleaseVersion = $genomeAssemblyReleaseVersion;
        $this->forwardPrime = $forwardPrime;
        $this->reversePrime = $reversePrime;
        $this->outputFormat = $outputFormat;
    }

    public function getSpeciesShortName(): string
    {
        return $this->speciesShortName;
    }

    public function getGenomeAssemblyReleaseVersion(): string
    {
        return $this->genomeAssemblyReleaseVersion;
    }

    public function getForwardPrime(): string
    {
        return $this->forwardPrime;
    }

    public function getReversePrime(): string
    {
        return $this->reversePrime;
    }

    public function getOutputFormat(): string
    {
        return $this->outputFormat;
    }

    public function jsonSerialize(): array
    {
        return [];
    }
}
