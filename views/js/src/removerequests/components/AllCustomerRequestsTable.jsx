import React, { Component } from 'react';
import ReactTableBla from 'react-table';
import axios from 'axios';
import PropTypes from 'prop-types';
import _ from 'lodash';

import { setAllCustomerRequests } from '../../actions';

import store from '../../store';
import { loadTableState, saveTableState } from "../../misc";

const prefix = 'allCustomers';
const ReactTable = ReactTableBla.default;
let pendingRequests = [];

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

        saveTableState(
          prefix,
          {
            pageSize,
            sorted,
            filtered,
          }
        );

        const res = {
          rows: sortedData.slice(pageSize * page, pageSize * page + pageSize),
          pages: Math.ceil(filteredData.length / pageSize)
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
    page: 0,
    pageSize: 50,
    sorted: [],
    filtered: [],
    loading: false,
    rows: 0,
    pages: 0,
  };

  static propTypes = {
    displayedCustomerRequests: PropTypes.array,
  };

  componentDidMount() {
    const prevState = loadTableState(prefix);
    if (!_.isEmpty(prevState)) {
      this.setState({
        pageSize: prevState.pageSize,
        sorted: prevState.sorted,
        filtered: prevState.filtered,
      }, () => {
        this.fetchData(this.state);
      });
    } else {
      this.fetchData(this.state);
    }
  }

  fetchData = (state) => {
    this.setState({ loading: true });
    requestData(
      state.pageSize,
      state.page,
      state.sorted,
      state.filtered
    )
      .then(res => {
        this.setState({
          row: res.rows,
          pages: res.pages,
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

  setExecutedFilter = (value) => {
    const { filtered } = _.cloneDeep(this.state);
    if (!value) {
      this.setState({
        filtered: _.filter(filtered, item => item.id !== 'executed'),
      }, this.fetchData(this.state));
    } else {
      let filter = _.find(filtered);
      if (filter) {
        filter.value = value;
      } else {
        filter = {
          id: 'executed',
          value,
        };
        filtered.push(filter);
      }
      this.setState({
        filtered,
      }, this.fetchData(this.state));
    }
  };

  render() {
    const { displayedCustomerRequests, translations } = this.props;
    const { loading, filtered } = this.state;

    if (_.isEmpty(displayedCustomerRequests)) {
      return null;
    }

    const executedFilter = _.find(filtered, item => item.id === 'executed');

    const columns = [
      {
        Header: 'ID',
        accessor: 'id_tbgdpr_request',
      }, {
        Header: 'Email',
        accessor: 'email',
      },
      {
        Header: 'Executed',
        accessor: 'executed',
        filterable: true,
        Filter: (
          <select onChange={event => this.setExecutedFilter(event.target.value)} value={executedFilter ? executedFilter.value : ''}>
            <option value="">--</option>
            <option value="0">Yes</option>
            <option value="1">No</option>
          </select>
        ),
        Cell: props => (
          <Choose>
            <When condition={parseInt(props.value, 10)}><i className='icon icon-check' style={{color: '#72C279'}} /></When>
            <Otherwise><i className='icon icon-times' style={{color: '#E08F95'}} /></Otherwise>
          </Choose>
        )
      },
      {
        Header: 'Date Added',
        accessor: 'date_add',
        filterable: false,
      }
    ];

    const prevState = loadTableState(prefix);

    return (
      <ReactTable
        data={displayedCustomerRequests}
        columns={columns}
        loading={loading}
        onFetchData={this.fetchData}
        defaultSorted={prevState.sorted}
        defaultFiltered={prevState.filtered}
        defaultPageSize={prevState.pagesize}
        pageSizeOptions={[20, 50, 100, 300, 1000]}
        filterable={true}

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
