import storage from 'local-storage-fallback';
import { DEFAULT_PAGE_SIZE } from "./consts";

export const saveTableState = (prefix, state) => {
  return storage.setItem(`${prefix}Table`, JSON.stringify(state));
};

export const loadTableState = (prefix) => {
  try {
    const item = JSON.parse(storage.getItem(`${prefix}Table`));
    if (typeof item === 'undefined'
      || item.pageSize === 'undefined'
      || item.sorted === 'undefined'
      || item.filtered === 'undefined'
      || _.isEmpty(item)
    ) {
      throw('invalid');
    }

    return item;
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

export const getSort = (sorted, key) => {
  let sort = _.get(_.find(sorted, item => item.id === key), 'desc');
  if (typeof sort !== 'undefined') {
    sort = sort ? 'desc' : 'asc';
  }

  return sort;
};
