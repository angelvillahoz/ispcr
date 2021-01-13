class Spinner extends React.Component {
  render() {
    return (
      <img
        src='./images/spinner.gif'
        style={{
          margin: 'auto',
          display: 'block'
        }}
        alt='Checking any sequence match...'
      />
    );
  }
}

function validate(sequence) {
  const errorsList = [];
  if (sequence.length < 20) {
    errorsList.push('The sequence is too short');
  } else {
    if (sequence.match(/[^ACGTacgt]/gm)) {
      errorsList.push('The sequence has invalid character(s): ' + sequence.match(/[^ACGTacgt]/gm));
    }
  }

  return errorsList;
}

class BlatForm extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      speciesScientificNames: [],
      selectedSpeciesScientificName: 'Drosophila melanogaster (dmel)',
      genomeAssemblyReleaseVersions: [],
      selectedGenomeAssemblyReleaseVersion: 'dm6',
      minimumIdentityPercentage: '95',
      sequence: '',
      loading: false,
      errors: []
    };
    this.changeSpeciesScientificName = this.changeSpeciesScientificName.bind(this);
    this.changeGenomeAssemblyReleaseVersion = this.changeGenomeAssemblyReleaseVersion.bind(this);
  };

  componentDidMount() {
    this.setState({
      speciesScientificNames: [
        { name: 'Aedes aegypti (aaeg)', genomeAssemblyReleaseVersions: 
          [ { name: 'aaeg5' } ]
        },
        { name: 'Anopheles gambiae (agam)', genomeAssemblyReleaseVersions:
          [ { name: 'agam4' } ]
        },
        { name: 'Drosophila melanogaster (dmel)', genomeAssemblyReleaseVersions:
          [ { name: 'dm6'}, 
            { name: 'dm3'},
            { name: 'dm2'},
            { name: 'dm1'} ] },
        { name: 'Tribolium castaneum (tcas)', genomeAssemblyReleaseVersions:
          [ { name: 'tcas5.2' } ] } 
      ],
      genomeAssemblyReleaseVersions: [
        { name: 'dm6'}, 
        { name: 'dm3'},
        { name: 'dm2'},
        { name: 'dm1'}]
    });
  }

  changeSpeciesScientificName(event) {
		this.setState({ selectedSpeciesScientificName: event.target.value });
    this.setState({ genomeAssemblyReleaseVersions: this.state.speciesScientificNames.find(speciesScientificName => speciesScientificName.name === event.target.value).genomeAssemblyReleaseVersions });
    this.setState({ selectedGenomeAssemblyReleaseVersion: this.state.speciesScientificNames.find(speciesScientificName => speciesScientificName.name === event.target.value).genomeAssemblyReleaseVersions[0]['name'] });
	}

  changeGenomeAssemblyReleaseVersion(event) {
		this.setState({ selectedGenomeAssemblyReleaseVersion: event.target.value });
	}

  handleSubmit = e => {
    e.preventDefault();
    const errors = validate(this.state.sequence);
    if (errors.length > 0) {
      this.setState({ errors: errors });
      this.setState({ list: ''});
      return;
    } else {
      this.setState({ errors: [] });
    }
    this.setState({
      loading: true
    });    
    axios({
      data: this.state,
      headers: { 'content-type': 'application/json' },
      method: 'post',
      url: API_PATH + '/search'
    })
    .then(result => {
      this.setState({
        list: result.data.results[0],
        loading: false
      });      
    })
    .catch(error => this.setState({ 
      errors : [error.message],
      loading: false      
    }));
  };

  render() {
    let output;
    if (this.state.list === null || this.state.loading) {
      output = <Spinner />;
    } else {
      if (this.state.list !== null) {
        if (this.state.list !== '') {
          output = <div dangerouslySetInnerHTML={{__html: this.state.list}}></div>
        } else {
          if (this.state.errors == '') {
            output = <p>Not any match</p>;
          } else {
            output = <p></p>;
          }
        } 
      } else {
        output = <p>Case not covered</p>;
      }
    }
    const {errors} = this.state;
    return ( 
      <div className="BlatForm">
        <p>BLAT server</p>
        <div>
          <form onSubmit={this.handleSubmit}>
            <label>Species Scientific Name:&nbsp;</label>
            <select placeholder="speciesScientificNamesSelector" value={this.state.selectedSpeciesScientificName} onChange={this.changeSpeciesScientificName}>
              {this.state.speciesScientificNames.map((e, key) => {
							  return <option key="{key}">{e.name}</option>;
						  })}
					  </select><br />
            <br />
            <label>Genome Assembly Release Version:&nbsp;</label>
            <select placeholder="genomeAssemblyReleaseVersionsSelector" value={this.state.selectedGenomeAssemblyReleaseVersion} onChange={this.changeGenomeAssemblyReleaseVersion}>
						  {this.state.genomeAssemblyReleaseVersions.map((e, key) => {
							  return <option key="{key}">{e.name}</option>;
						  })}
					  </select><br />
            <br />
            <label>Minimum Identity Percentage:&nbsp;</label>
            <br />
            <input type="text"
                   id="minimumIdentityPercentageId"
                   name="minimumIdentityPercentage"
                   size="1"
                   required
                   title="Only between 0.01% as minimum and 100.00% as maximum with two digits after the decimal point"
                   pattern="^([0-9]\.[0-9][0-9]{0,1}|[1-9][0-9]{0,1}|[1-9][0-9]\.[0-9][0-9]{0,1}|100|100\.0{1,2})$"
                   value={this.state.minimumIdentityPercentage}
                   onChange={e => this.setState({ minimumIdentityPercentage: e.target.value })}/>
            <label>%</label><br />
            <br />
            <label>Sequence:&nbsp;</label><br />
            <textarea id="sequenceId"
                      name="sequence"
                      required
                      rows="10"
                      cols="100"
                      value={this.state.sequence}
                      onChange={e => this.setState({ sequence: e.target.value })}></textarea><br />
            <br />
            <input type="submit"
                   value="Submit" /><br />
            <br />
            {errors.map(error => (
              <p key={error}>Error: {error}</p>
            ))}
            <label>Results:&nbsp;</label><br />
            <div id="outputId">{output}</div>
          </form>
        </div>
      </div>
    );
  }
}