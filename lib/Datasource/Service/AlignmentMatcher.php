<?php
namespace CCR\BLAT\Datasource\Service;

// BLAT libraries with namespaces
use CCR\BLAT\Service\External\BlatDataSource;
class AlignmentMatcher
{
    private $blatDataSource;
    public function __construct(BlatDataSource $blatDataSource)
    {
        $this->blatDataSource = $blatDataSource;
    }
    /**
     * Returns an array of string or Alignment objects from the results 
     * obtained from the BLAT data source depending on the output format.
     */
    public function get(
        string $speciesShortName,
        string $genomeAssemblyReleaseVersion,
        string $minimumIdentityPercentage,
        string $sequence,
        string $outputFormat
    ): Array {
        $objects = $this->blatDataSource->query(
            $speciesShortName,
            $genomeAssemblyReleaseVersion,
            $minimumIdentityPercentage,
            $sequence,
            $outputFormat
        );
        $objectResults = [];
        switch ($outputFormat) {
            case "blast9":
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
