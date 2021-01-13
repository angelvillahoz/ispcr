package ispcrserver

import (
	"bytes"
	"fmt"
	"io"
	"io/ioutil"
	"net/http"
	"os"

	"github.com/gorilla/mux"
)

// BuildRoutes builds the routes for the RESTful API.
// The function takes the 2bit genome database files regarding to the following speces:
// 1) Aedes aegypti
// 2) Anopheles gambiae
// 3) Drosophila melanogaster with its four genome assembly release versions
// 4) Tribolium castaneum
// as arguments for use in building the responses.
func BuildRoutes(
	aedesAegyptiGenomeReleaseVersionFile *os.File,
	anophelesGambiaeGenomeReleaseVersionFile *os.File,
	drosophilaMelanogasterGenomeReleaseVersion1File *os.File,
	drosophilaMelanogasterGenomeReleaseVersion2File *os.File,
	drosophilaMelanogasterGenomeReleaseVersion3File *os.File,
	drosophilaMelanogasterGenomeReleaseVersion6File *os.File,
	triboliumCastaneumGenomeReleaseVersionFile *os.File,
) *mux.Router {
	r := mux.NewRouter()
	r.NotFoundHandler = http.HandlerFunc(func(
		w http.ResponseWriter,
		r *http.Request) {
		fmt.Fprint(w, "Error 404: Not found.")
	})
	r.HandleFunc("/",
		func(
			w http.ResponseWriter,
			r *http.Request) {
			var speciesShortName,
				genomeAssemblyReleaseVersion,
				minimumIdentityPercentage,
				outputFormat string = "",
				"",
				"",
				""
			var genomeDatabaseFile *os.File
			var filePath string
			var file *os.File
			var bytesNumber int64
			multipartReader, _error := r.MultipartReader()
			if _error != nil {
				panic(_error)
			}
			for {
				part, _error := multipartReader.NextPart()
				if _error == io.EOF {
					break
				}
				switch part.FormName() {
				case "genomeAssemblyReleaseVersion":
					buffer := new(bytes.Buffer)
					buffer.ReadFrom(part)
					genomeAssemblyReleaseVersion = buffer.String()
				case "minimumIdentityPercentage":
					buffer := new(bytes.Buffer)
					buffer.ReadFrom(part)
					minimumIdentityPercentage = buffer.String()
				case "input":
					filePath = "./" + part.FileName()
					file, _error = os.Create(filePath)
					if _error != nil {
						panic(_error)
					}
					bytesNumber, _error = io.Copy(
						file,
						part)
					if _error != nil {
						panic(_error)
					}
					fmt.Println(bytesNumber, " bytes copied from part into file")
					_error = file.Sync()
					if _error != nil {
						panic(_error)
					}
					defer file.Close()
				case "outputFormat":
					buffer := new(bytes.Buffer)
					buffer.ReadFrom(part)
					outputFormat = buffer.String()
				case "speciesShortName":
					buffer := new(bytes.Buffer)
					buffer.ReadFrom(part)
					speciesShortName = buffer.String()
				}
			}
			if speciesShortName == "" {
				panic("No species short name")
			}
			if minimumIdentityPercentage == "" {
				panic("No minimum identity percentage")
			}
			if genomeAssemblyReleaseVersion == "" {
				panic("No genome assembly release version")
			}
			if outputFormat == "" {
				panic("No output format")
			}
			switch speciesShortName {
			case "aaeg":
				switch genomeAssemblyReleaseVersion {
				case "aaeg5":
					genomeDatabaseFile = aedesAegyptiGenomeReleaseVersionFile
				default:
					panic("Unknown genome assembly release version: " + genomeAssemblyReleaseVersion +
						" for the species short name, " + speciesShortName)
				}
			case "agam":
				switch genomeAssemblyReleaseVersion {
				case "agam4":
					genomeDatabaseFile = anophelesGambiaeGenomeReleaseVersionFile
				default:
					panic("Unknown genome assembly release version: " + genomeAssemblyReleaseVersion +
						" for the species short name, " + speciesShortName)
				}
			case "dmel":
				switch genomeAssemblyReleaseVersion {
				case "dm1":
					genomeDatabaseFile = drosophilaMelanogasterGenomeReleaseVersion1File
				case "dm2":
					genomeDatabaseFile = drosophilaMelanogasterGenomeReleaseVersion2File
				case "dm3":
					genomeDatabaseFile = drosophilaMelanogasterGenomeReleaseVersion3File
				case "dm6":
					genomeDatabaseFile = drosophilaMelanogasterGenomeReleaseVersion6File
				default:
					panic("Unknown genome assembly release version: " + genomeAssemblyReleaseVersion +
						" for the species short name, " + speciesShortName)
				}
			case "tcas":
				switch genomeAssemblyReleaseVersion {
				case "tcas5.2":
					genomeDatabaseFile = triboliumCastaneumGenomeReleaseVersionFile
				default:
					panic("Unknown genome assembly release version: " + genomeAssemblyReleaseVersion +
						" for the species short name, " + speciesShortName)
				}
			default:
				panic("Unknown species short name: " + speciesShortName)
			}
			fmt.Println("Species short name: " + speciesShortName)
			fmt.Println("Genome assembly release version: " + genomeAssemblyReleaseVersion)
			fmt.Println("Minimum identity percentage: " + minimumIdentityPercentage + "%")
			fmt.Println("Output format: " + outputFormat)
			file, _error = os.OpenFile(
				filePath,
				os.O_RDONLY,
				0644)
			if _error != nil {
				panic(_error)
			}
			defer file.Close()
			in, _error := ioutil.TempFile(
				"",
				"fasta")
			if _error != nil {
				panic(_error)
			}
			defer in.Close()
			defer os.Remove(in.Name())
			out, _error := ioutil.TempFile(
				"",
				"pslx")
			if _error != nil {
				panic(_error)
			}
			defer out.Close()
			defer os.Remove(out.Name())
			bytesNumber, _error = io.Copy(
				in,
				file)
			if _error != nil {
				panic(_error)
			}
			fmt.Println(bytesNumber, " bytes copied from file into in")
			Search(genomeDatabaseFile,
				minimumIdentityPercentage,
				in,
				outputFormat,
				out)
			fmt.Println("Search done!")
			bytesNumber, _error = io.Copy(
				w,
				out)
			if _error != nil {
				panic(_error)
			}
			fmt.Println(bytesNumber, " bytes copied from out into w")
			fmt.Println()
		}).Methods("POST")

	return r
}
