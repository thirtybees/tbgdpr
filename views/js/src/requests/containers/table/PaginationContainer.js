import { connect } from 'react-redux';

import Pagination from '../../components/table/Pagination';

const mapStateToProps = (state) => {
  return {
    translations: state.translations,
  };
};

const mapDispatchToProps = (dispatch) => {
  return {
  };
};

const PaginationContainer = connect(
  mapStateToProps,
  mapDispatchToProps
)(Pagination);

export default PaginationContainer;
