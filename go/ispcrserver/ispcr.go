package ispcrserver

import (
	"os"
	"os/exec"
)

// Search checks the genome in the 2bit database chosen for any match
// from all sequences provided in the FASTA file, and writes the match
// results into the file depending on the output format.
func Search(
	genomeDatabase *os.File,
	minimumIdentityPercentage string,
	fasta *os.File,
	outputFormat string,
	output *os.File) {
	blat(
		genomeDatabase.Name(),
		minimumIdentityPercentage,
		fasta.Name(),
		outputFormat,
		output.Name())
}

// blat invokes the BLAT commandline tool. It takes the filenames of the
// 2bit database and the FASTA files, and returns the filename of the resulting
// file depending on the output format.
// The tool is assumed to be on the PATH and is invoked with default
// arguments with the following exception(s):
// -q is set to "dna" only accepting DNA sequences as the input
// See https://genome.ucsc.edu/goldenpath/help/blatSpec.html#blatUsage for
// details on the BLAT commandline tool.
func blat(
	genomeDatabase string,
	minimumIdentityPercentage string,
	in string,
	outputFormat string,
	out string) {
	if outputFormat == "blast9" {
		cmd := exec.Command(
			"blat",
			"-minIdentity="+minimumIdentityPercentage,
			"-out="+outputFormat,
			"-q=dna",
			genomeDatabase,
			in,
			out)
		if _error := cmd.Run(); _error != nil {
			panic(_error)
		}
	} else {
		cmd := exec.Command(
			"blat",
			"-minIdentity="+minimumIdentityPercentage,
			"noHead",
			"-out="+outputFormat,
			"-q=dna",
			genomeDatabase,
			in,
			out)
		if _error := cmd.Run(); _error != nil {
			panic(_error)
		}
	}
}
