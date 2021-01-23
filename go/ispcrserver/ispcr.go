package ispcrserver

import (
	"os"
	"os/exec"
	"strings"
)

// Search checks the genome in the 2bit database chosen for any match
// from all the primes provided in the query file, and writes the match
// results into the file depending on the output format chosen by the
// user.
func Search(
	genomeDatabase *os.File,
	query *os.File,
	maximumPcrProductSize string,
	minimumPerfectMatchSize string,
	minimumGoodMatchesSize string,
	flipReversePrimer string,
	outputFormat string,
	output *os.File) {
	isPcr(
		genomeDatabase.Name(),
		query.Name(),
		maximumPcrProductSize,
		minimumPerfectMatchSize,
		minimumGoodMatchesSize,
		flipReversePrimer,
		outputFormat,
		output.Name())
	tacOutput, _error := exec.Command(
		"tac",
		output.Name()).Output()
	if _error != nil {
		panic(_error)
	}
	lines := strings.Split(string(tacOutput), "\n")
	newOutput, _error := os.OpenFile(output.Name(), os.O_RDWR|os.O_CREATE|os.O_TRUNC, 0755)
	if _error != nil {
		panic(_error)
	}
	defer newOutput.Close()
	fastaPart := ""
	for _, line := range lines {
		if line != "" {
			if fastaPart == "" {
				fastaPart = line + "\n"
			} else {
				fastaPart = line + "\n" + fastaPart
			}
			if string(line[0]) == ">" {
				newOutput.WriteString(fastaPart)
				fastaPart = ""
			}
		}
	}
	newOutput.Sync()
}

// isPcr invokes the isPcr commandline tool. It takes the filenames of both
// 2bit database and query files, and returns the filename of the resulting
// file depending on the output format chosen by the user.
// The tool is assumed to be on the PATH and is invoked with default
// arguments.
func isPcr(
	genomeDatabase string,
	in string,
	maximumPcrProductSize string,
	minimumPerfectMatchSize string,
	minimumGoodMatchesSize string,
	flipReversePrimer string,
	outputFormat string,
	out string) {
	var cmd *exec.Cmd
	if flipReversePrimer == "false" {
		cmd = exec.Command(
			"isPcr",
			"-maxSize="+maximumPcrProductSize,
			"-minPerfect="+minimumPerfectMatchSize,
			"-minGood="+minimumGoodMatchesSize,
			"-out="+outputFormat,
			genomeDatabase,
			in,
			out)
	} else {
		cmd = exec.Command(
			"isPcr",
			"-flipReverse",
			"-maxSize="+maximumPcrProductSize,
			"-minPerfect="+minimumPerfectMatchSize,
			"-minGood="+minimumGoodMatchesSize,
			"-out="+outputFormat,
			genomeDatabase,
			in,
			out)
	}
	if _error := cmd.Run(); _error != nil {
		panic(_error)
	}
}
