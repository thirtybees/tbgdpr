import { createStore, applyMiddleware } from 'redux';
import thunk from 'redux-thunk';
import requests from '../reducers/requests';

const devTools = window.__REDUX_DEVTOOLS_EXTENSION__;
const getStore = () => {
  window.TbGdprModule = window.TbGdprModule || {};
  if (!window.TbGdprModule.store) {
    window.TbGdprModule.store = createStore(
      requests,
      devTools && devTools(),
      applyMiddleware(thunk)
    );
  }

  return window.TbGdprModule.store;
};

export default getStore;
