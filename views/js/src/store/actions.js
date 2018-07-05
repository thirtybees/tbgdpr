/* eslint-disable camelcase */
// Action types
export const SET_ALL_CUSTOMER_REQUESTS = 'SET_ALL_CUSTOMER_REQUESTS';
export const SET_TRANSLATIONS = 'SET_TRANSLATIONS';

// Action creators
export const setAllCustomerRequests = (requests) => {
  return { type: SET_ALL_CUSTOMER_REQUESTS, requests };
};

export const setTranslations = (translations) => {
  return { type: SET_TRANSLATIONS, translations };
};
