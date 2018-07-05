import 'babel-polyfill';
import React from 'react';
import { render } from 'react-dom';
import { Provider } from 'react-redux';

import AllCustomerRequestsTable from './containers/AllCustomerRequestsTableContainer';

import store from '../store';

import './css/table.css';
import TranslationProvider from "../contexts/TranslationContext";

export default class AllCustomerRequests {
  constructor(target, translations) {
    if (typeof target === 'string') {
      target = document.getElementById(target);
    }
    store.dispatch(setTranslations(translations));

    render(
      <Provider store={store}>
        <TranslationProvider>
          {state => {
          console.log(state);
          <AllCustomerRequestsTable translations={state.translations} />
        }}
        </TranslationProvider>
      </Provider>,
      target
    );
  }
}
