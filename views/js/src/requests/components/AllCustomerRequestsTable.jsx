import React, { Component } from 'react';
import ReactTableBla from 'react-table';
import axios from 'axios';
import PropTypes from 'prop-types';
import _ from 'lodash';
import xss from 'xss';

import { setAllCustomerRequests } from '../../actions';

import store from '../../store';
import { loadTableState, saveTableState } from "../../misc/tools";
import { DEFAULT_PAGE_SIZE, DEFAULT_ROW_SELECT, TABLE_BOOL, TABLE_EMAIL_HEX } from "../../misc/consts";

const prefix = 'allCustomers';
const ReactTable = ReactTableBla.default;
let pendingRequests = [];
const prevState = loadTableState(prefix);

const requestData = (pageSize, page, sorted, filtered) => {
  return new Promise((resolve, reject) => {
    _.forEach(pendingRequests, (source) => {
      source.cancel();
    });
    pendingRequests = [];
    const source = axios.CancelToken.source();
    pendingRequests.push(source);
    axios.post(`${window.TbGdprModule.urls.ajax}&ajax=1&action=RetrieveCustomerRequests`, {
      page,
      pageSize,
      sorted,
      filtered,
    }, {
      cancelToken: source.token,
    })
      .then(({ data }) => {
        store.dispatch(setAllCustomerRequests(data.data));
        let filteredData = data.data;
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

        const res = {
          rows: sortedData.slice(pageSize * page, pageSize * page + pageSize),
          pages: data.pages,
          page,
        };
        resolve(res);
      })
      .catch((err) => {
        reject(err);
      })
    ;
  });
};

export default class AllCustomerRequestsTable extends Component {
  state = {
    pages: 0,
    page: 0,
    loading: false,
  };

  static propTypes = {
    displayedCustomerRequests: PropTypes.array,
  };

  componentDidMount() {
    const prevState = loadTableState(prefix);
    this.fetchData(prevState);
  }

  fetchData = (state) => {
    if (typeof state === 'undefined') {
      state = _.cloneDeep(this.state);
    }

    const { pageSize, page, sorted, filtered } = state;

    saveTableState(prefix, {
      pageSize,
      filtered,
      sorted,
    });

    this.setState({ loading: true });
    requestData(
      pageSize,
      page,
      sorted,
      filtered
    )
      .then(res => {
        this.setState({
          pages: res.pages,
          page: res.page,
          loading: false
        });
      })
      .catch(() => {
        this.setState({
          loading: false,
        });
      })
    ;
  };

  render() {
    const { displayedCustomerRequests, translations } = this.props;
    const state = _.cloneDeep(this.state);
    const { loading, pages } = state;

    if (!Array.isArray(displayedCustomerRequests)) {
      return null;
    }

    const columns = [
      {
        Header: 'ID',
        accessor: 'id_tbgdpr_request',
      }, {
        Header: 'Visitor',
        accessor: 'email',
        filterMethod: () => true,
        Filter: ({ filter, onChange }) =>
          <input
            type="text"
            onChange={event => onChange({
              value: event.target.value,
              type: TABLE_EMAIL_HEX,
            })}
            style={{ width: "100%" }}
            value={_.get(filter, 'value.value', '') + ''}
          />,
        Cell: props => <kbd>{xss(props.value)}</kbd>
      },
      {
        Header: 'Executed',
        accessor: 'executed',
        filterable: true,
        filterMethod: () => true,
        Filter: ({ filter, onChange }) =>
          <select
            onChange={event => onChange({
              value: event.target.value,
              type: TABLE_BOOL,
            })}
            style={{ width: "100%" }}
            value={filter ? filter.value.value : "all"}
          >
            <option value="all">--</option>
            <option value="true">{xss(translations.yes)}</option>
            <option value="false">{xss(translations.no)}</option>
          </select>,
        Cell: props => (
          <Choose>
            <When condition={parseInt(props.value, 10)}><i className='icon icon-check' style={{color: '#72C279'}} /></When>
            <Otherwise><i className='icon icon-times' style={{color: '#E08F95'}} /></Otherwise>
          </Choose>
        )
      },
      {
        Header: xss(translations.dateAdded),
        accessor: 'date_add',
        filterable: false,
      }
    ];

    return (
      <ReactTable
        data={displayedCustomerRequests}
        columns={columns}
        loading={loading}
        onFetchData={this.fetchData}
        defaultSorted={prevState.sorted}
        defaultFiltered={prevState.filtered}
        defaultPageSize={prevState.pageSize}
        pageSizeOptions={DEFAULT_ROW_SELECT}
        filterable={true}
        pages={pages}
        manual

        previousText={translations.previous}
        nextText={translations.next}
        loadingText={translations.loading}
        noDataText={translations.noRowsFound}
        pageText={translations.page}
        ofText={translations.of}
        rowsText=""
      />
    );
  }
}
