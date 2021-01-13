<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>BLAT Server</title>
    <script src="./node_modules/react/umd/react.production.min.js"></script>
    <script src="./node_modules/react-dom/umd/react-dom.production.min.js"></script>
    <script src="./node_modules/@babel/standalone/babel.min.js"></script>
    <script src="./node_modules/axios/dist/axios.min.js"></script>
    <script src="./src/components/custom-react-classes.js" type="text/babel"></script>
</head>
<body>
<div id="root"></div>
<script type="text/babel">

const API_PATH = 'api';

const customStyles = {
  option: (provided, state) => ({
    ...provided,
    borderBottom: "1px dotted pink",
    color: state.isSelected ? "blue" : "",
    fontSize: 16,
    backgroundColor: state.isSelected ? "#eee" : "",
    textAlign: "left",
    cursor: "pointer"
  }),
  container: base => ({
    ...base,
    width: "260px"
  }),
  control: base => ({
    ...base,
    height: 32,
    minHeight: 32,
    fontSize: 16,
    borderRadius: 0,
    width: "260px",
    textAlign: "left",
    cursor: "pointer"
  }),
  dropdownIndicator: base => ({
    ...base,
    display: "none"
  }),
  indicatorSeparator: base => ({
    ...base,
    display: "none"
  }),
  valueContainer: base => ({
    ...base,
    padding: 0,
    paddingLeft: 2
  })
};

ReactDOM.render(
    <BlatForm name="BlatForm" />,
    document.getElementById('root')
);
</script>
</body>
</html>