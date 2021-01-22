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
            ($getAlignmentList->getForwardPrimer() !== "") &&
            ($getAlignmentList->getReversePrimer() !== "") &&
            ($getAlignmentList->getMaximumPcrProductSize() !== "") &&
            ($getAlignmentList->getMinimumPerfectMatchSize() !== "") &&
            ($getAlignmentList->getMinimumGoodMatchesSize() !== "") &&
            ($getAlignmentList->getFlipReversePrimer() !== "") &&
            ($getAlignmentList->getOutputFormat() !== "") ) {
            $alignmentList = $this->alignmentMatcher->get(
                $getAlignmentList->getSpeciesShortName(),
                $getAlignmentList->getGenomeAssemblyReleaseVersion(),
                $getAlignmentList->getForwardPrimer(),
                $getAlignmentList->getReversePrimer(),
                $getAlignmentList->getMaximumPcrProductSize(),
                $getAlignmentList->getMinimumPerfectMatchSize(),
                $getAlignmentList->getMinimumGoodMatchesSize(),
                $getAlignmentList->getFlipReversePrimer(),
                $getAlignmentList->getOutputFormat()
            );
            $coordinates = preg_replace(
                "/[\r\n]+/",
                "<br />",
                preg_replace(
                    "/[\t]+/",
                    "&emsp;",
                    $alignmentList
                )
            );            
        }

        return QueryResult::fromArray($coordinates);
    }
}
