import React from 'react';
import PropTypes from 'prop-types';
import _ from 'lodash';

const defaultButton = props => <button {...props}>{props.children}</button>;

class Pagination extends React.Component {
  state = {
    visiblePages: [],
  };

  static propTypes = {
    pages: PropTypes.number,
    page: PropTypes.number,
    PageButtonComponent: PropTypes.any,
    onPageChange: PropTypes.func,
    previousText: PropTypes.string,
    nextText: PropTypes.string,
    translations: PropTypes.object,
  };

  static defaultProps = {
    page: 0,
    pages: 1,
  };

  static getDerivedStateFromProps(nextProps) {
    return {
      visiblePages: Pagination.getVisiblePages(nextProps.page + 1, nextProps.pages),
    };
  }

  static filterPages = (visiblePages, totalPages) => {
    return visiblePages.filter(page => page <= totalPages);
  };

  static getVisiblePages = (page, total) => {
    if (total < 6) {
      return Pagination.filterPages(_.range(4), total);
    } else {
      if (page % 5 >= 0 && page > 2 && page + 1 < total) {
        return [page - 3, page - 2, page - 1, page, page + 1];
      } else if (page % 5 >= 0 && page > 2 && page + 1 >= total) {
        return [total - 5, total - 4, total - 3, total - 2, total - 1];
      } else {
        return _.range(4);
      }
    }
  };

  render() {
    const {
      PageButtonComponent = defaultButton,
      pageSize,
      rowCount,
      translations,
      page: activePage,
      pages,
      onPageChange,
      onPageSizeChange,
    } = this.props;
    const { visiblePages } = this.state;
    const total = pages;
    console.log(visiblePages);
    
    return (
      <div className="row">
        <div className="col-lg-6">
        </div>
        <div className="col-lg-6">
          <div className="pagination">
            {translations.display}
            <button type="button" className="btn btn-default dropdown-toggle" data-toggle="dropdown">
              {parseInt(pageSize, 10)}&nbsp;
              <i className="icon-caret-down"/>
            </button>
            <ul className="dropdown-menu" style={{ cursor: 'pointer' }}>
              <li>
                <a className="pagination-items-page" onClick={() => onPageSizeChange(20)}>20</a>
              </li>
              <li>
                <a className="pagination-items-page" onClick={() => onPageSizeChange(50)}>50</a>
              </li>
              <li>
                <a className="pagination-items-page" onClick={() => onPageSizeChange(100)}>100</a>
              </li>
              <li>
                <a className="pagination-items-page" onClick={() => onPageSizeChange(300)}>300</a>
              </li>
              <li>
                <a className="pagination-items-page" onClick={() => onPageSizeChange(1000)}>1000</a>
              </li>
            </ul>
            &nbsp;/ {parseInt(rowCount, 10)} {translations.paginationResults}
          </div>
          <ul className="pagination pull-right" style={{ cursor: 'pointer' }}>
            <li className={activePage < 1 ? 'disabled' : ''}>
              <a
                className="pagination-link"
                onClick={() => onPageChange(0)}
              >
                <i className="icon-double-angle-left"/>
              </a>
            </li>
            <li className={activePage < 1 ? 'disabled' : ''}>
              <a
                className="pagination-link"
                onClick={() => onPageChange(this.props.page - 1)}
              >
                <i className="icon-angle-left"/>
              </a>
            </li>
            <If condition={activePage > 2 && total > 4}>
              <li className="disabled">
                <a >…</a>
              </li>
            </If>
            <For each="page" of={visiblePages}>
              <li key={page} className={this.props.page == page ? 'active' : ''}>
                <a
                  className="pagination-link"
                  onClick={() => onPageChange(page)}
                >
                  {parseInt(page, 10) + 1}
                </a>
              </li>
            </For>
            <If condition={activePage < total - 3 && total > 4}>
              <li className="disabled">
                <a>…</a>
              </li>
            </If>
            <li className={(activePage >= total - 1) ? 'disabled' : ''}>
              <a
                className="pagination-link"
                onClick={() => onPageChange(this.props.page + 1)}
              >
                <i className="icon-angle-right"/>
              </a>
            </li>
            <li className={(activePage >= total - 1) ? 'disabled' : ''}>
              <a
                className="pagination-link"
                onClick={() => onPageChange(total)}
              >
                <i className="icon-double-angle-right"/>
              </a>
            </li>
          </ul>
        </div>
      </div>
    );
  }
}

export default Pagination;
