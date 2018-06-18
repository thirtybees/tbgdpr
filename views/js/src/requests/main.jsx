import React from 'react';
import { render } from 'react-dom';
import { Provider } from 'react-redux';

import AllCustomerRequestsTable from './containers/AllCustomerRequestsTableContainer';

import getStore from '../store';
const store = getStore();

import './css/table.css';
import './css/loading.css';

export default class AllCustomerRequests {
  constructor(target) {
    if (typeof target === 'string') {
      target = document.getElementById(target);
    }
    this.target = target;
    const self = this;
    if (typeof window.IntersectionObserver !== 'undefined') {
      const observer = new IntersectionObserver((changes) => {
        changes.forEach((change) => {
          if (change.intersectionRatio > 0) {
            self.initRender();
            observer.disconnect();
          }
        });
      }, {
        root: null,
        rootMargin: '0px',
        threshold: 0.5
      });
      observer.observe(target);
    } else {
      const interval = setInterval(() => {
        if (target.offsetParent) {
          self.initRender();
          clearInterval(interval);
        }
      }, 500);
    }
  }

  initRender = () => {
    render(
      <React.StrictMode>
        <Provider store={store}>
          <AllCustomerRequestsTable />
        </Provider>
      </React.StrictMode>,
      this.target
    );
  };
}
