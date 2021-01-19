<?php
namespace CCR\ISPCR\Service\External;

// Standard PHP Libraries (SPL)
use RuntimeException;
use Psr\Http\Message\StreamInterface;
// Third-party libraries
use GuzzleHttp\ClientInterface;
use League\Csv\Reader;
// ISPCR libraries with namespaces
use CCR\ISPCR\Service\External\Model\Alignment;
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
     * @param string $outputFormat The output format.
     * @return iterable The alignments returned from the query.
     */
    public function query(
        string $speciesShortName,
        string $genomeAssemblyReleaseVersion,        
        string $forwardPrime,
        string $reversePrime,
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
                        "name"     => "outputFormat",
                        "contents" => $outputFormat
                    ]
                ]
            ]
        )->getBody();
        switch($outputFormat) {
            case "fa":
                return $this->parseFastaStreamInterface($streamInterface);
                break;
            case "psl":
                return $this->parsePslStreamInterface($streamInterface);
                break;
            default:
        }
    }
    private function parseFastaStreamInterface(StreamInterface $streamInterface): iterable
    {
        $fastaStream = $streamInterface->detach();
        if ( $fastaStream === null ) {
            throw new RuntimeException("Failed to open the FASTA stream.");
        }
        yield stream_get_contents($fastaStream);
    }    
    private function parsePslStreamInterface(StreamInterface $streamInterface): iterable
    {
        $pslStream = $streamInterface->detach();
        if ( $pslStream === null ) {
            throw new RuntimeException("Failed to open the PSL stream.");
        }
        $reader = Reader::createFromStream($pslStream)
            ->setDelimiter("\t");
        foreach ( $reader->fetch() as $row ) {
            yield $row[9] => $this->buildAlignment($row);
        }
    }
    private function buildAlignment(array $record): Alignment
    {
        $strand = $record[8];
        if ( strtolower(substr($record[13], 0, 3)) === "chr" ) {
            $chromosomeName = substr($record[13], 3);
        } else {
            $chromosomeName = $record[13];
        }
        $startCoordinate = $record[15];
        $endCoordinate = $record[16];
        $sequence = trim(strtoupper($record[22]), ",");
        $matchesNumber = $record[0];
        $repeatMatchesNumber = $record[2];
        $mismatchesNumber = $record[1];
        $queryInsertsNumber = $record[4];
        $targetInsertsNumber = $record[6];

        return new Alignment(
            $strand,
            $chromosomeName,
            $startCoordinate,
            $endCoordinate,
            $sequence,
            $matchesNumber,
            $repeatMatchesNumber,
            $mismatchesNumber,
            $queryInsertsNumber,
            $targetInsertsNumber
        );
    }
}
