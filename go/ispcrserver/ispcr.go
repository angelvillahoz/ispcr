package ispcrserver

import (
	"os"
	"os/exec"
)

// Search checks the genome in the 2bit database chosen for any match
// from all the primes provided in the query file, and writes the match
// results into the file depending on the output format chosen by the
// user.
func Search(
	genomeDatabase *os.File,
	query *os.File,
	outputFormat string,
	output *os.File) {
	isPcr(
		genomeDatabase.Name(),
		query.Name(),
		outputFormat,
		output.Name())
}

// isPcr invokes the isPcr commandline tool. It takes the filenames of both
// 2bit database and query files, and returns the filename of the resulting
// file depending on the output format chosen by the user.
// The tool is assumed to be on the PATH and is invoked with default
// arguments.
func isPcr(
	genomeDatabase string,
	in string,
	outputFormat string,
	out string) {
	cmd := exec.Command(
		"isPcr",
		"-out="+outputFormat,
		genomeDatabase,
		in,
		out)
	if _error := cmd.Run(); _error != nil {
		panic(_error)
	}
}
