import { connect } from 'react-redux';

import AllCustomerRequestsTable from '../components/AllCustomerRequestsTable';

const mapStateToProps = (state) => {
  return state;
};

const mapDispatchToProps = (dispatch) => {
  return {
  };
};

const AllCustomerRequestsTableContainer = connect(
  mapStateToProps,
  mapDispatchToProps
)(AllCustomerRequestsTable);

export default AllCustomerRequestsTableContainer;
