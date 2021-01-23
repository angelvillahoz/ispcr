<?php
namespace CCR\ISPCR\Service\External;

// Standard PHP Libraries (SPL)
use RuntimeException;
use Psr\Http\Message\StreamInterface;
// Third-party libraries
use GuzzleHttp\ClientInterface;
/**
 * Data source for sending individual queries to a ISPCR endpoint.
 * See https://genome.ucsc.edu/cgi-bin/hgPcr for more details.
 */
class IsPcrDataSource
{
    /**
     * @var ClientInterface $client Guzzle client.
     */
    private $client;
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }
    /**
     * Queries the ISPCR endpoint with both forward and reverse primes and
     * returns the results in the output format chosen by the user.
     * @param string $speciesShortName The species short name.
     * @param string $genomeAssemblyReleaseVersion The genome assembly release version.
     * @param string $forwardPrime The forward prime.
     * @param string $reversePrime The reverse prime.
     * @param int $maximumPcrProductSize The maximum size of the PCR product.
     * @param int $minimumPerfectMatchSize The minimum size of the perfect match at 3\' end of primer.
     * @param int $minimumGoodMatchesSize The minimum size where there must be 2 matches for each mismatch.
     * @param bool $flipReversePrimer The reverse of the complement reverse (second) primer.
     * @param string $outputFormat The output format.
     * @return iterable The alignments returned from the query.
     */
    public function query(
        string $speciesShortName,
        string $genomeAssemblyReleaseVersion,        
        string $forwardPrime,
        string $reversePrime,
        int $maximumPcrProductSize,
        int $minimumPerfectMatchSize,
        int $minimumGoodMatchesSize,
        bool $flipReversePrimer,
        string $outputFormat
    ): iterable {
        $queryFile = tmpfile();
        if ( $queryFile === false ) {
            throw new RuntimeException("Failed to create the query file.");
        }
        fwrite(
            $queryFile,
            "query\t" . $forwardPrime . "\t" . $reversePrime . PHP_EOL
        );
        $flipReversePrimer = $flipReversePrimer ? "true" : "false";
        // Making a multipart/form-data request
        $streamInterface = $this->client->request(
            "POST",
            "",
            [
                "multipart" => [
                    [
                        "name"     => "speciesShortName",
                        "contents" => $speciesShortName
                    ],
                    [
                        "name"     => "genomeAssemblyReleaseVersion",
                        "contents" => $genomeAssemblyReleaseVersion
                    ],
                    [
                        "name"     => "input",
                        "contents" => $queryFile,
                        "filename" => "query"
                    ],
                    [
                        "name"     => "maximumPcrProductSize",
                        "contents" => $maximumPcrProductSize
                    ],
                    [
                        "name"     => "minimumPerfectMatchSize",
                        "contents" => $minimumPerfectMatchSize
                    ],
                    [
                        "name"     => "minimumGoodMatchesSize",
                        "contents" => $minimumGoodMatchesSize
                    ],
                    [
                        "name"     => "flipReversePrimer",
                        "contents" => $flipReversePrimer
                    ],                                                            
                    [
                        "name"     => "outputFormat",
                        "contents" => $outputFormat
                    ]
                ]
            ]
        )->getBody();

        return $this->parseStreamInterface($streamInterface);
    }
    private function parseStreamInterface(StreamInterface $streamInterface): iterable
    {
        $stream = $streamInterface->detach();
        if ( $stream === null ) {
            throw new RuntimeException("Failed to open the stream.");
        }
        yield stream_get_contents($stream);
    }    
}
