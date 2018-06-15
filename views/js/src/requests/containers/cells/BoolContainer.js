import { connect } from 'react-redux';
import Bool from '../../components/cells/Bool';

const mapStateToProps = (state) => {
  return {
    translations: state.translations,
  };
};

const mapDispatchToProps = () => {
  return {
  };
};

const BoolContainer = connect(
  mapStateToProps,
  mapDispatchToProps
)(Bool);

export default BoolContainer;
