<?php
namespace CCR\ISPCR\Datasource\Query;

// ISPCR libraries with namespaces
use CCR\ISPCR\Datasource\Query\GetAlignmentList;
use CCR\ISPCR\Datasource\Service\AlignmentMatcher;
use CCR\ISPCR\Service\Message\QueryResult;
class GetAlignmentListHandler
{
    private $alignmentMatcher;

    public function __construct(AlignmentMatcher $alignmentMatcher) {
        $this->alignmentMatcher = $alignmentMatcher;
    }

    public function __invoke(GetAlignmentList $getAlignmentList): QueryResult
    {
        if ( ($getAlignmentList->getSpeciesShortName() !== "") &&
            ($getAlignmentList->getGenomeAssemblyReleaseVersion() !== "") &&
            ($getAlignmentList->getForwardPrime() !== "") &&
            ($getAlignmentList->getReversePrime() !== "") &&
            ($getAlignmentList->getOutputFormat() !== "") ) {
            $alignmentList = $this->alignmentMatcher->get(
                $getAlignmentList->getSpeciesShortName(),
                $getAlignmentList->getGenomeAssemblyReleaseVersion(),
                $getAlignmentList->getForwardPrime(),
                $getAlignmentList->getReversePrime(),
                $getAlignmentList->getOutputFormat()
            );
            $coordinates = array();            
            switch ($getAlignmentList->getOutputFormat()) {
                case "fa":
                    $coordinates = preg_replace(
                        "/[\r\n]+/",
                        "<br />",
                        preg_replace(
                            "/[\t]+/",
                            "&emsp;",
                            $alignmentList
                        )
                    );
                    break;
                case "pls":
                    foreach ( $alignmentList as $rawAlignment ) {
                        $alignment = array(
                            "chromosome"    => $rawAlignment->chromosomeName,
                            "end"           => $rawAlignment->endCoordinate,
                            "start"         => $rawAlignment->startCoordinate
                        );
                        $coordinates[] = $alignment;
                    }
                    break;
                default:
            }
        }

        return QueryResult::fromArray($coordinates);
    }
}
