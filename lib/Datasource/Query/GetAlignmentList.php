<?php
namespace CCR\BLAT\Datasource\Query;

// Third-party libraries
use Psr\Http\Message\ServerRequestInterface;
// BLAT libraries with namespaces
use CCR\BLAT\Service\Exception\InvalidMessageException;
use CCR\BLAT\Service\Message\QueryInterface;
class GetAlignmentList implements QueryInterface
{
    public static function fromRequest(ServerRequestInterface $serverRequestInterface): self
    {
        $parsedBody = $serverRequestInterface->getParsedBody();
        if ( isset($parsedBody["selectedSpeciesScientificName"]) &&
            isset($parsedBody["selectedGenomeAssemblyReleaseVersion"]) &&
            isset($parsedBody["minimumIdentityPercentage"]) &&
            isset($parsedBody["sequence"]) ) {
            $speciesShortName = explode(")", explode("(", $parsedBody["selectedSpeciesScientificName"])[1])[0];
            $genomeAssemblyReleaseVersion = $parsedBody["selectedGenomeAssemblyReleaseVersion"];
            $minimumIdentityPercentage = $parsedBody["minimumIdentityPercentage"];
            $sequence = $parsedBody["sequence"];
            $outputFormat = "blast9";

            return new self(
                $speciesShortName,
                $genomeAssemblyReleaseVersion,
                $minimumIdentityPercentage,
                $sequence,
                $outputFormat
            );
        }

        throw new InvalidMessageException(self::class);
    }

    private $speciesShortName;
    private $genomeAssemblyReleaseVersion;
    private $minimumIdentityPercentage;
    private $sequence;
    private $outputFormat;

    public function __construct(
        string $speciesShortName,
        string $genomeAssemblyReleaseVersion,
        string $minimumIdentityPercentage,
        string $sequence,
        string $outputFormat
    ) {
        $this->speciesShortName = $speciesShortName;
        $this->genomeAssemblyReleaseVersion = $genomeAssemblyReleaseVersion;
        $this->minimumIdentityPercentage = $minimumIdentityPercentage;
        $this->sequence = $sequence;
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

    public function getMinimumIdentityPercentage(): string
    {
        return $this->minimumIdentityPercentage;
    }

    public function getSequence(): string
    {
        return $this->sequence;
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
