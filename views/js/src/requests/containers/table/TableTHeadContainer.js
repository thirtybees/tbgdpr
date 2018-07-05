import { connect } from 'react-redux';

import TableTHead from '../../components/table/TableTHead';

const mapStateToProps = (state) => {
  return {
    translations: state.translations,
  };
};

const mapDispatchToProps = (dispatch) => {
  return {
  };
};

const TableTHeadContainer = connect(
  mapStateToProps,
  mapDispatchToProps
)(TableTHead);

export default TableTHeadContainer;
