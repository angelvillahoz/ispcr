<?php
namespace CCR\ISPCR\Datasource\Service;

// ISPCR libraries with namespaces
use CCR\ISPCR\Service\External\IsPcrDataSource;
class AlignmentMatcher
{
    private $isPcrDataSource;
    public function __construct(IsPcrDataSource $isPcrDataSource)
    {
        $this->isPcrDataSource = $isPcrDataSource;
    }
    /**
     * Returns an array of string or Alignment objects from the results 
     * obtained from the ISPCR data source depending on the output format.
     */
    public function get(
        string $speciesShortName,
        string $genomeAssemblyReleaseVersion,
        string $forwardPrimer,
        string $reversePrimer,
        string $maximumPcrProductSize,
        string $minimumPerfectMatchSize,
        string $minimumGoodMatchesSize,
        bool $flipReversePrimer,
        string $outputFormat
    ): Array {
        $objects = $this->isPcrDataSource->query(
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
        $objectResults = [];
        foreach ( $objects as $objectResult ) {
            $objectResults[] = $objectResult;
        }

        return $objectResults;
    }
}
