class Spinner extends React.Component {
  render() {
    return (
      <img
        src='./images/spinner.gif'
        style={{
          display: 'block',
          margin: 'auto'
        }}
        alt='Checking any forward and reverse primers match...'
      />
    );
  }
}

class IsPcrForm extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      speciesScientificNames: [],
      selectedSpeciesScientificName: 'Drosophila melanogaster (dmel)',
      genomeAssemblyReleaseVersions: [],
      selectedGenomeAssemblyReleaseVersion: 'dm6',
      forwardPrimer: '',
      reversePrimer: '',
      maximumPcrProductSize: '4000',
      minimumPerfectMatchSize: '15',
      minimumGoodMatchesSize: '15',
      selectedFlipReversePrimer: false,
      outputFormats: [],
      selectedOutputFormat: 'fa',
      loading: false,
      isDisabled: false,      
      errors: []
    };
    this.changeSpeciesScientificName = this.changeSpeciesScientificName.bind(this);
    this.changeGenomeAssemblyReleaseVersion = this.changeGenomeAssemblyReleaseVersion.bind(this);
    this.changeOutputFormat = this.changeOutputFormat.bind(this);
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
      ],
      outputFormats: [
        { name: 'bed'},
        { name: 'fa'},
        { name: 'psl'}
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

  changeOutputFormat(event) {
		this.setState({ selectedOutputFormat: event.target.value });
	}

  handleClear = e => {
    this.setState({
      forwardPrimer: '',
      reversePrimer: ''
    });    
  }

  validate() {
    const {
      forwardPrimer,
      reversePrimer
    } = this.state;
    const errorsList = [];
    if (forwardPrimer.length < 15) {
      errorsList.push('The forward primer is too short');
    } else {
      if (forwardPrimer.match(/[^ACGTacgt]/gm)) {
        errorsList.push('The forward primer has invalid character(s): ' + forwardPrimer.match(/[^ACGTacgt]/gm));
      }
    }
    if (reversePrimer.length < 15) {
      errorsList.push('The reverse primer is too short');
    } else {
      if (reversePrimer.match(/[^ACGTacgt]/gm)) {
        errorsList.push('The reverse primer has invalid character(s): ' + reversePrimer.match(/[^ACGTacgt]/gm));
      }
    }
    
    return errorsList;
  }
  
  handleSubmit = e => {
    e.preventDefault();
    this.state.forwardPrimer = this.state.forwardPrimer.replace(/(\r\n|\r|\n)/g, '');
    this.state.reversePrimer = this.state.reversePrimer.replace(/(\r\n|\r|\n)/g, '');
    const errors = this.validate();
    if (errors.length > 0) {
      this.setState({ errors: errors });
      this.setState({ list: ''});
      return;
    } else {
      this.setState({ errors: [] });
    }
    this.setState({
      isDisabled: true,
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
        isDisabled: false,        
        list: result.data.results[0],
        loading: false
      });      
    })
    .catch(error => this.setState({ 
      errors : [error.message],
      isDisabled: false,      
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
            <label>Forward Primer:&nbsp;</label><br />
            <textarea id="forwardPrimerId"
                      name="forwardPrimer"
                      required
                      rows="3"
                      cols="100"
                      value={this.state.forwardPrimer}
                      onChange={e => this.setState({ forwardPrimer: e.target.value })}></textarea><br />
            <br />
            <label>Reverse Primer:&nbsp;</label><br />
            <textarea id="reversePrimerId"
                      name="reversePrimer"
                      required
                      rows="3"
                      cols="100"
                      value={this.state.reversePrimer}
                      onChange={e => this.setState({ reversePrimer: e.target.value })}></textarea><br />
            <br />
            <label>Maximum PCR Product Size:&nbsp;</label>
            <input type="text"
                   id="maximumPcrProductSizeId"
                   name="maximumPcrProductSize"
                   required
                   size="5"
                   title="Only between 0 and 2147483647"
                   pattern="^\d+$"
                   value={this.state.maximumPcrProductSize}
                   onChange={e => this.setState({ maximumPcrProductSize: e.target.value })}/><br />
            <br />                   
            <label>Minimum Perfect Match Size:&nbsp;</label>
            <input type="text"
                   id="minimumPerfectMatchSizeId"
                   name="minimumPerfectMatchSize"
                   required
                   size="5"
                   title="Only between 0 and 2147483647"
                   pattern="^\d+$"
                   value={this.state.minimumPerfectMatchSize}
                   onChange={e => this.setState({ minimumPerfectMatchSize: e.target.value })}/><br />
            <br />                   
            <label>Minimum Good Match Size:&nbsp;</label>
            <input type="text"
                   id="minimumGoodMatchesSizeId"
                   name="minimumGoodMatchesSize"
                   required
                   size="5"
                   title="Only between 0 and 2147483647"
                   pattern="^\d+$"
                   value={this.state.minimumGoodMatchesSize}
                   onChange={e => this.setState({ minimumGoodMatchesSize: e.target.value })}/><br />
            <br />
            <label>Flip Reverse Primer:&nbsp;</label>
            <input type="checkbox"
                   checked={this.state.selectedFlipReversePrimer}
                   id="flipReversePrimerId"
                   name="flipReversePrimer"
                   onChange={e => this.setState({ selectedFlipReversePrimer: e.target.checked })}/><br />
            <br />
            <label>Output Format:&nbsp;</label>            
            <select placeholder="outputFormatsSelector" value={this.state.selectedOutputFormat} onChange={this.changeOutputFormat}>
						  {this.state.outputFormats.map((e, key) => {
							  return <option key="{key}">{e.name}</option>;
						  })}
					  </select><br />
            <br />                               
            <button type="submit" disabled={this.state.isDisabled} >Submit</button>&nbsp;&nbsp;&nbsp;
            <button type="button" disabled={this.state.isDisabled} onClick={this.handleClear}>Clear</button><br />
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