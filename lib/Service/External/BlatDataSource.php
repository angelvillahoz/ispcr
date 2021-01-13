<?php
namespace CCR\BLAT\Service\External;

// Standard PHP Libraries (SPL)
use RuntimeException;
use Psr\Http\Message\StreamInterface;
// Third-party libraries
use GuzzleHttp\ClientInterface;
use League\Csv\Reader;
// BLAT libraries with namespaces
use CCR\BLAT\Service\External\Model\Alignment;
/**
 * Data source for sending individual and batch queries to a BLAT endpoint.
 * See https://genome.ucsc.edu/cgi-bin/hgBlat for more details.
 */
class BlatDataSource
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
     * Queries the BLAT endpoint using a FASTA file and returns the results in
     * BLAST9 format.
     * @param string $speciesShortName The species short name.
     * @param string $genomeAssemblyReleaseVersion The genome assembly release version.
     * @param string $minimumIdentityPercentage The minimum identity percentage.
     * @param string $fasta The FASTA file URI.
     * @param string $outputFormat The output format.
     * 
     * @return iterable The alignments returned from the query.
     */
    public function batchQuery(
        string $speciesShortName,
        string $genomeAssemblyReleaseVersion,
        string $minimumIdentityPercentage,
        string $fastaFile,
        string $outputFormat
    ): iterable {
        // Making a multipart/form-data request
        $streamInterface = $this->client->request(
            "POST",
            "",
            [
                "multipart" => [
                    [
                        "name"     => "speciesShortName",
                        "contents" => $speciesShortName,
                    ],
                    [
                        "name"     => "genomeAssemblyReleaseVersion",
                        "contents" => $genomeAssemblyReleaseVersion,
                    ],
                    [
                        "name"     => "minimumIdentityPercentage",
                        "contents" => $minimumIdentityPercentage,
                    ],
                    [
                        "name"     => "input",
                        "contents" => fopen($fastaFile, "r"),
                        "filename" => "input.fa"
                    ],
                    [
                        "name"     => "outputFormat",
                        "contents" => $outputFormat,
                    ]
                ]
            ]
        )->getBody();

        switch($outputFormat) {
            case "blast9":
                return $this->parseBlast9StreamInterface($streamInterface);
                break;
            case "pslx":
                return $this->parsePslxStreamInterface($streamInterface);
                break;
            default:
        }
    }
    /**
     * Queries the BLAT endpoint with a single sequence and returns the results
     * in BLAST9 format.
     * @param string $speciesShortName The species short name.
     * @param string $genomeAssemblyReleaseVersion The genome assembly release version.
     * @param string $minimumIdentityPercentage The minimum identity percentage.
     * @param string $sequence The nucleic acid sequence.
     * @param string $outputFormat The output format.
     * @return iterable The alignments returned from the query.
     */
    public function query(
        string $speciesShortName,
        string $genomeAssemblyReleaseVersion,        
        string $minimumIdentityPercentage,
        string $sequence,
        string $outputFormat
    ): iterable {
        $fastaFile = tmpfile();
        if ( $fastaFile === false ) {
            throw new RuntimeException("Failed to create temporary FASTA file.");
        }
        fwrite(
            $fastaFile,
            ">query" . PHP_EOL . $sequence . PHP_EOL
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
                        "name"     => "minimumIdentityPercentage",
                        "contents" => $minimumIdentityPercentage
                    ],                    
                    [
                        "name"     => "input",
                        "contents" => $fastaFile,
                        "filename" => "input.fa"
                    ],
                    [
                        "name"     => "outputFormat",
                        "contents" => $outputFormat
                    ]
                ]
            ]
        )->getBody();

        switch($outputFormat) {
            case "blast9":
                return $this->parseBlast9StreamInterface($streamInterface);
                break;
            case "pslx":
                return $this->parsePslxStreamInterface($streamInterface);
                break;
            default:
        }
    }
    private function parseBlast9StreamInterface(StreamInterface $streamInterface): iterable
    {
        $blast9Stream = $streamInterface->detach();
        if ( $blast9Stream === null ) {
            throw new RuntimeException("Failed to open the BLAST9 stream.");
        }
        yield stream_get_contents($blast9Stream);
    }    
    private function parsePslxStreamInterface(StreamInterface $streamInterface): iterable
    {
        $pslxStream = $streamInterface->detach();
        if ( $pslxStream === null ) {
            throw new RuntimeException("Failed to open the PSLX stream.");
        }
        $reader = Reader::createFromStream($pslxStream)
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
