package main

import (
	"fmt"
	"log"
	"net/http"
	"os"

	"redfly.edu/ispcrserver"
)

func main() {
	aedesAegyptiGenomeReleaseVersionFile, _error := os.Open("../assets/aaeg5.2bit")
	if _error != nil {
		panic(_error)
	}
	defer aedesAegyptiGenomeReleaseVersionFile.Close()
	anophelesGambiaeGenomeReleaseVersionFile, _error := os.Open("../assets/agam4.2bit")
	if _error != nil {
		panic(_error)
	}
	defer anophelesGambiaeGenomeReleaseVersionFile.Close()
	drosophilaMelanogasterGenomeReleaseVersion1File, _error := os.Open("../assets/dm1.2bit")
	if _error != nil {
		panic(_error)
	}
	defer drosophilaMelanogasterGenomeReleaseVersion1File.Close()
	drosophilaMelanogasterGenomeReleaseVersion2File, _error := os.Open("../assets/dm2.2bit")
	if _error != nil {
		panic(_error)
	}
	defer drosophilaMelanogasterGenomeReleaseVersion2File.Close()
	drosophilaMelanogasterGenomeReleaseVersion3File, _error := os.Open("../assets/dm3.2bit")
	if _error != nil {
		panic(_error)
	}
	defer drosophilaMelanogasterGenomeReleaseVersion3File.Close()
	drosophilaMelanogasterGenomeReleaseVersion6File, _error := os.Open("../assets/dm6.2bit")
	if _error != nil {
		panic(_error)
	}
	defer drosophilaMelanogasterGenomeReleaseVersion6File.Close()
	triboliumCastaneumGenomeReleaseVersionFile, _error := os.Open("../assets/tcas5.2.2bit")
	if _error != nil {
		panic(_error)
	}
	defer triboliumCastaneumGenomeReleaseVersionFile.Close()
	fmt.Println("Starting server; hit CTRL+C to exit.")
	r := ispcrserver.BuildRoutes(
		aedesAegyptiGenomeReleaseVersionFile,
		anophelesGambiaeGenomeReleaseVersionFile,
		drosophilaMelanogasterGenomeReleaseVersion1File,
		drosophilaMelanogasterGenomeReleaseVersion2File,
		drosophilaMelanogasterGenomeReleaseVersion3File,
		drosophilaMelanogasterGenomeReleaseVersion6File,
		triboliumCastaneumGenomeReleaseVersionFile)
	log.Fatal(http.ListenAndServe(":8080", r))
}
