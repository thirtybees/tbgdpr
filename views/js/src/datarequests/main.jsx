import 'babel-polyfill';
import React from 'react';
import { render } from 'react-dom';

import AllCustomerRequestsTable from './containers/AllCustomerRequestsTableContainer';

import store from '../store';

import './css/table.css';
import { Provider } from 'react-redux';

class AllCustomerRequests {
  constructor(target, translations) {
    if (typeof target === 'string') {
      target = document.getElementById(target);
    }
    render(
      <Provider store={store}>
        <AllCustomerRequestsTable />
      </Provider>,
      target
    );
  }
}
