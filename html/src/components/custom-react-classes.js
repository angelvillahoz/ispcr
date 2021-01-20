class Spinner extends React.Component {
  render() {
    return (
      <img
        src='./images/spinner.gif'
        style={{
          margin: 'auto',
          display: 'block'
        }}
        alt='Checking any forward and reverse primes match...'
      />
    );
  }
}

function validate(forwardPrime, reversePrime) {
  const errorsList = [];
  if (forwardPrime.length < 15) {
    errorsList.push('The forward prime is too short');
  } else {
    if (forwardPrime.match(/[^ACGTacgt]/gm)) {
      errorsList.push('The forward prime has invalid character(s): ' + forwardPrime.match(/[^ACGTacgt]/gm));
    }
  }
  if (reversePrime.length < 15) {
    errorsList.push('The reverse prime is too short');
  } else {
    if (reversePrime.match(/[^ACGTacgt]/gm)) {
      errorsList.push('The reverse prime has invalid character(s): ' + reversePrime.match(/[^ACGTacgt]/gm));
    }
  }

  return errorsList;
}

class IsPcrForm extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      speciesScientificNames: [],
      selectedSpeciesScientificName: 'Drosophila melanogaster (dmel)',
      genomeAssemblyReleaseVersions: [],
      selectedGenomeAssemblyReleaseVersion: 'dm6',
      forwardPrime: '',
      reversePrime: '',
      loading: false,
      errors: []
    };
    this.changeSpeciesScientificName = this.changeSpeciesScientificName.bind(this);
    this.changeGenomeAssemblyReleaseVersion = this.changeGenomeAssemblyReleaseVersion.bind(this);
  };

  componentDidMount() {
    this.setState({
      speciesScientificNames: [
        { 
          name: 'Aedes aegypti (aaeg)',
          genomeAssemblyReleaseVersions: [ 
            { name: 'aaeg5' }
          ]
        },
        { 
          name: 'Anopheles gambiae (agam)',
          genomeAssemblyReleaseVersions: [
            { name: 'agam4' }
          ]
        },
        { 
          name: 'Drosophila melanogaster (dmel)',
          genomeAssemblyReleaseVersions: [
            { name: 'dm6'}, 
            { name: 'dm3'},
            { name: 'dm2'},
            { name: 'dm1'}
          ]
        },
        {
          name: 'Tribolium castaneum (tcas)',
          genomeAssemblyReleaseVersions: [
            { name: 'tcas5.2' }
          ]
        } 
      ],
      genomeAssemblyReleaseVersions: [
        { name: 'dm6'}, 
        { name: 'dm3'},
        { name: 'dm2'},
        { name: 'dm1'}
      ]
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
    const errors = validate(
        this.state.forwardPrime,
        this.state.reversePrime
    );
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
          output = <tt><pre><div dangerouslySetInnerHTML={{__html: this.state.list}}></div></pre></tt>
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
      <div className="IsPcrForm">
        <p>In-Silico PCR server</p>
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
            <label>Forward Prime:&nbsp;</label><br />
            <textarea id="forwardPrimeId"
                      name="forwardPrime"
                      required
                      rows="3"
                      cols="100"
                      value={this.state.forwardPrime}
                      onChange={e => this.setState({ forwardPrime: e.target.value })}></textarea><br />
            <br />
            <label>Reverse Prime:&nbsp;</label><br />
            <textarea id="reversePrimeId"
                      name="reversePrime"
                      required
                      rows="3"
                      cols="100"
                      value={this.state.reversePrime}
                      onChange={e => this.setState({ reversePrime: e.target.value })}></textarea><br />
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