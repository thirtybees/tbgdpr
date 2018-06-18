import React from 'react';
import { render } from 'react-dom';
import { Provider } from 'react-redux';

import AllCustomerRequestsTableBadge from './containers/AllCustomerRequestsTableBadgeContainer';

import store from '../store';

import './css/table.css';
import './css/loading.css';

export default class AllCustomerRequestsBadge {
  constructor(target) {
    if (typeof target === 'string') {
      target = document.getElementById(target);
    }

    render(
      <Provider store={store}>
        <AllCustomerRequestsTableBadge />
      </Provider>,
      target
    );
  }
}
