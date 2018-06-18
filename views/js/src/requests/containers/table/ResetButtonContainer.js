import { connect } from 'react-redux';
import ResetButton from '../../components/table/ResetButton';

const mapStateToProps = (state) => {
  return {
    translations: state.translations,
  };
};

const mapDispatchToProps = () => {
  return {
  };
};

const ResetButtonContainer = connect(
  mapStateToProps,
  mapDispatchToProps
)(ResetButton);

export default ResetButtonContainer;
