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
            isset($parsedBody["forwardPrimer"]) &&
            isset($parsedBody["reversePrimer"]) &&
            isset($parsedBody["maximumPcrProductSize"]) &&
            isset($parsedBody["minimumPerfectMatchSize"]) &&
            isset($parsedBody["minimumGoodMatchesSize"]) &&
            isset($parsedBody["flipReversePrimer"]) &&
            isset($parsedBody["selectedOutputFormat"])) {
            $speciesShortName = explode(")", explode("(", $parsedBody["selectedSpeciesScientificName"])[1])[0];
            $genomeAssemblyReleaseVersion = $parsedBody["selectedGenomeAssemblyReleaseVersion"];
            $forwardPrimer = $parsedBody["forwardPrimer"];
            $reversePrimer = $parsedBody["reversePrimer"];
            $maximumPcrProductSize = $parsedBody["maximumPcrProductSize"];
            $minimumPerfectMatchSize = $parsedBody["minimumPerfectMatchSize"];
            $minimumGoodMatchesSize = $parsedBody["minimumGoodMatchesSize"];
            $flipReversePrimer = $parsedBody["flipReversePrimer"];
            $outputFormat = $parsedBody["selectedOutputFormat"];

            return new self(
                $speciesShortName,
                $genomeAssemblyReleaseVersion,
                $forwardPrimer,
                $reversePrimer,
                $maximumPcrProductSize,
                $minimumPerfectMatchSize,
                $minimumGoodMatchesSize,
                $flipReversePrimer,
                $outputFormat
            );
        }

        throw new InvalidMessageException(self::class);
    }

    private $speciesShortName;
    private $genomeAssemblyReleaseVersion;
    private $forwardPrimer;
    private $reversePrimer;
    private $maximumPcrProductSize;
    private $minimumPerfectMatchSize;
    private $minimumGoodMatchesSize;
    private $flipReversePrimer;
    private $outputFormat;

    public function __construct(
        string $speciesShortName,
        string $genomeAssemblyReleaseVersion,
        string $forwardPrimer,
        string $reversePrimer,
        int $maximumPcrProductSize,
        int $minimumPerfectMatchSize,
        int $minimumGoodMatchesSize,
        bool $flipReversePrimer,
        string $outputFormat
    ) {
        $this->speciesShortName = $speciesShortName;
        $this->genomeAssemblyReleaseVersion = $genomeAssemblyReleaseVersion;
        $this->forwardPrimer = $forwardPrimer;
        $this->reversePrimer = $reversePrimer;
        $this->maximumPcrProductSize = $maximumPcrProductSize;
        $this->minimumPerfectMatchSize = $minimumPerfectMatchSize;
        $this->minimumGoodMatchesSize = $minimumGoodMatchesSize;
        $this->flipReversePrimer = $flipReversePrimer;
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

    public function getForwardPrimer(): string
    {
        return $this->forwardPrimer;
    }

    public function getReversePrimer(): string
    {
        return $this->reversePrimer;
    }

    public function getMaximumPcrProductSize(): int
    {
        return $this->maximumPcrProductSize;
    }

    public function getMinimumPerfectMatchSize(): int
    {
        return $this->minimumPerfectMatchSize;
    }

    public function getMinimumGoodMatchesSize(): int
    {
        return $this->minimumGoodMatchesSize;
    }

    public function getFlipReversePrimer(): bool
    {
        return $this->flipReversePrimer;
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
