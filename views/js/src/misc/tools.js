import storage from 'local-storage-fallback';
import { DEFAULT_PAGE_SIZE } from "./consts";

export const saveTableState = (prefix, state) => {
  return storage.setItem(`${prefix}Table`, JSON.stringify(state));
};

export const loadTableState = (prefix) => {
  try {
    const item = storage.getItem(`${prefix}Table`);
    if (typeof item === 'undefined'
      || item.pageSize === 'undefined'
      || item.sorted === 'undefined'
      || item.filtered === 'undefined'
    ) {
      throw('invalid');
    }

    return JSON.parse(item);
  } catch (e) {
    return {
      pageSize: DEFAULT_PAGE_SIZE,
      sorted: [
        {
          id: 'id_tbgdpr_request',
          desc: true,
        }
      ],
      filtered: [],
    };
  }
};
