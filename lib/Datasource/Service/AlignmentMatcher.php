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
        string $forwardPrime,
        string $reversePrime,
        string $outputFormat
    ): Array {
        $objects = $this->isPcrDataSource->query(
            $speciesShortName,
            $genomeAssemblyReleaseVersion,
            $forwardPrime,
            $reversePrime,
            $outputFormat
        );
        $objectResults = [];
        switch ($outputFormat) {
            case "fa":
                foreach ($objects as $objectResult ) {
                    $objectResults[] = $objectResult;
                }
                break;
            case "plsx":
                foreach ( $objects as $coordinate => $alignment ) {
                    if ( (isset($objectResults[$coordinate]) === false) ||
                        ($objectResults[$coordinate]->score < $alignment->score) ) {
                            $objectResults[$coordinate] = $alignment;
                    }
                }
                break;
            default:
        }

        return $objectResults;
    }
}
