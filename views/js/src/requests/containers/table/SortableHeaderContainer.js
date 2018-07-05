import { connect } from 'react-redux';

import SortableHeader from '../../components/table/SortableHeader';

const mapStateToProps = (state) => {
  return {
    translations: state.translations,
  };
};

const mapDispatchToProps = (dispatch) => {
  return {
  };
};

const SortableHeaderContainer = connect(
  mapStateToProps,
  mapDispatchToProps
)(SortableHeader);

export default SortableHeaderContainer;
