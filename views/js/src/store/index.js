import { createStore, applyMiddleware } from 'redux';
import thunk from 'redux-thunk';
import requests from '../reducers/requests';

let store;
const devTools = window.__REDUX_DEVTOOLS_EXTENSION__;

// if (window.TbGdprModule.debug) {
  store = createStore(
    requests,
    devTools && devTools(),
    applyMiddleware(thunk)
  );
// } else {
//   store = createStore(
//     requests,
//     applyMiddleware(thunk)
//   );
// }

export default store;
