import { setTranslations } from '../actions';
import getStore from '../store';
const store = getStore();

class Translations {
  static get() {
    return store.getState().translations;
  }

  static set(translations) {
    store.dispatch(setTranslations(translations));
  }
}

export default Translations;
