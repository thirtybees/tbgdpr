import React, { Component } from 'react';
const ReactTable  = require('react-table').default;
import axios from 'axios';
import PropTypes from 'prop-types';
import classnames from 'classnames';
import _ from 'lodash';
import { inspect } from 'util';
import serialize from 'serialize-javascript';

import Bool from '../containers/table/cells/BoolContainer';
import ResetButton from '../containers/table/ResetButtonContainer';
import TableTHead from '../containers/table/TableTHeadContainer';
import SortableHeader from '../containers/table/SortableHeaderContainer';
import Loading from './misc/Loading';
import Pagination from '../containers/table/PaginationContainer';
import { setAllCustomerRequests } from '../../store/actions';

import { getSort, loadTableState, saveTableState } from '../../misc/tools';
import { DEFAULT_ROW_SELECT, TABLE_BOOL, TABLE_EMAIL_HEX } from '../../misc/consts';
import getStore from '../../store';

const store = getStore();
const prefix = 'allCustomers';
let pendingRequests = [];
const prevState = loadTableState(prefix);

/**
 * Request data
 *
 * @param {number} pageSize
 * @param {number} page
 * @param {Array}  sorted
 * @param {Array}  filtered
 *
 * @returns {Promise<{rows: *[], pages, page: *, rowCount}>}
 */
const requestData = async (pageSize, page, sorted, filtered) => {
    _.forEach(pendingRequests, async (source) => {
      source.cancel();
    });
    pendingRequests = [];
    const source = axios.CancelToken.source();
    pendingRequests.push(source);
    const { data: { data, pages, rowCount, success } } = await axios.post(`${window.TbGdprModule.urls.ajax}&ajax=1&action=RetrieveCustomerRequests`, {
      page,
      pageSize,
      sorted,
      filtered,
    }, {
      cancelToken: source.token,
    });
    store.dispatch(setAllCustomerRequests(data));
    let filteredData = data;
    if (filtered.length) {
      filteredData = filtered.reduce((filteredSoFar, nextFilter) => {
        return filteredSoFar.filter(row => {
          return (row[nextFilter.id] + '').includes(nextFilter.value);
        });
      }, filteredData);
    }

    const sortedData = _.orderBy(
      filteredData,
      sorted.map(sort => {
        return row => {
          if (row[sort.id] === null || row[sort.id] === undefined) {
            return -Infinity;
          }
          return typeof row[sort.id] === 'string'
            ? row[sort.id].toLowerCase()
            : row[sort.id];
        };
      }),
      sorted.map(d => (d.desc ? 'desc' : 'asc'))
    );

    return {
      rows: sortedData.slice(pageSize * page, pageSize * page + pageSize),
      pages: pages,
      page,
      rowCount,
    };
};

class AllCustomerRequestsTable extends Component {
  state = {
    pages: 0,
    rowCount: 0,
    page: 0,
    loading: false,
    sorted: prevState.sorted,
    filtered: prevState.filtered,
  };

  static propTypes = {
    displayedCustomerRequests: PropTypes.array,
    translations: PropTypes.object,
  };

  shouldComponentUpdate(nextProps) {
    return !_.isEmpty(nextProps.translations);
  }

  /**
   * Fetch data
   *
   * @param {Object} state
   *
   * @returns {Promise<void>}
   */
  fetchData = async (state) => {
    const { pageSize, page, sorted, filtered } = state;

    saveTableState(prefix, {
      pageSize,
      filtered,
      sorted,
    });

    this.setState({
      loading: true,
    });

    try {
      const res = await requestData(
        pageSize,
        page,
        sorted,
        filtered
      );
      this.setState({
        pages: res.pages,
        page,
        rowCount: res.rowCount,
        loading: false
      });
    } catch (e) {
      this.setState({
        loading: false,
      });
      console.error(e);
    }
  };

  injectThead = ({ toggleSort, className, children, ...rest }) => {
    if (typeof toggleSort !== 'function') {
      return (
        <div
          className={classnames('rt-th', 'rt-th-filter', className)}
          onClick={e => toggleSort && toggleSort(e)}
          role="columnheader"
          tabIndex="-1"
          {...rest}
        >
          {children}
        </div>
      );
    }

    return <TableTHead children={children}/>;
  };

  setSort = (id, desc, add = false) => {
    const sorted = add ? _.cloneDeep(this.state.sorted) : [];
    let sort = _.find(sorted, item => item.id === id);
    if (typeof sort === 'undefined') {
      sort = {
        id,
      };
      sorted.push(sort);
    }
    sort.desc = desc;

    this.setState({
      sorted,
    });
  };

  render() {
    const { displayedCustomerRequests, translations } = this.props;
    const { pages, loading, sorted, filtered, rowCount } = this.state;

    if (!_.isArray(displayedCustomerRequests)) {
      return null;
    }

    const columns = [
      {
        Header: <SortableHeader
          name={translations.ID}
          id="id_tbgdpr_request"
          sort={getSort(sorted, 'id_tbgdpr_request')}
          setSort={this.setSort}
        />,
        accessor: 'id_tbgdpr_request',
        style: {
          cursor: 'pointer',
        }
      }, {
        Header: <SortableHeader
          name={translations.visitor}
          id="email"
          sort={getSort(sorted, 'email')}
          setSort={this.setSort}
        />,
        accessor: 'email',
        Filter: ({ filter, onChange }) =>
          <input
            type="text"
            onChange={event => onChange({
              value: event.target.value,
              type: TABLE_EMAIL_HEX,
            })}
            style={{ width: '100%' }}
            value={_.get(filter, 'value.value', '') + ''}
          />,
        Cell: props => <kbd>{props.value}</kbd>,
        style: {
          cursor: 'pointer',
        }
      },
      {
        Header: <SortableHeader
          name={translations.executed}
          id="executed"
          sort={getSort(sorted, 'executed')}
          setSort={this.setSort}
        />,
        accessor: 'executed',
        filterable: true,
        Filter: ({ filter, onChange }) =>
          <select
            onChange={event => onChange({
              value: event.target.value,
              type: TABLE_BOOL,
            })}
            style={{ width: '100%' }}
            value={filter ? filter.value.value : 'all'}
          >
            <option value="all">--</option>
            <option value="true">{translations.yes}</option>
            <option value="false">{translations.no}</option>
          </select>,
        Cell: props => <Bool enabled={!!parseInt(props.value, 10)} style={{ cursor: 'pointer' }}/>,
        style: {
          cursor: 'pointer',
        }
      },
      {
        Header: <SortableHeader
          name={translations.dateAdded}
          id="date_add"
          sort={getSort(sorted, 'date_add')}
          setSort={this.setSort}
        />,
        accessor: 'date_add',
        filterable: false,
        style: {
          cursor: 'pointer',
        }
      },
      {
        Header: null,
        Filter: <ResetButton resetSort={() => this.setState({filtered: [], sorted: []})}/>,
        filterable: !_.isEmpty(sorted) || !_.isEmpty(filtered),
        sortable: false,
      }
    ];

    return (
      <ReactTable
        className="-striped -highlight"

        data={displayedCustomerRequests}
        columns={columns}
        loading={loading}
        sorted={sorted}
        filtered={filtered}

        defaultPageSize={prevState.pageSize}
        pageSizeOptions={DEFAULT_ROW_SELECT}
        filterable
        resizable={false}
        pages={pages}
        manual
        showPaginationBottom
        rowCount={rowCount}

        onFetchData={this.fetchData}
        onSortedChange={sorted => this.setState({ sorted })}
        onFilteredChange={filtered => this.setState({ filtered })}

        ThComponent={this.injectThead}
        PaginationComponent={Pagination}

        previousText={translations.previous}
        nextText={translations.next}
        loadingText={<Loading/>}
        noDataText={translations.noRowsFound}
        pageText={translations.page}
        ofText={translations.of}
        rowsText=""
      />
    );
  }
}

export default AllCustomerRequestsTable;
