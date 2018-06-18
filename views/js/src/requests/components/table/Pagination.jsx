import React from "react";
import PropTypes from "prop-types";

const defaultButton = props => <button {...props}>{props.children}</button>;

export default class Pagination extends React.Component {
  constructor(props) {
    super();

    this.changePage = this.changePage.bind(this);

    this.state = {
      visiblePages: this.getVisiblePages(null, props.pages)
    };
  }

  static propTypes = {
    pages: PropTypes.number,
    page: PropTypes.number,
    PageButtonComponent: PropTypes.any,
    onPageChange: PropTypes.func,
    previousText: PropTypes.string,
    nextText: PropTypes.string
  };

  componentWillReceiveProps(nextProps) {
    if (this.props.pages !== nextProps.pages) {
      this.setState({
        visiblePages: this.getVisiblePages(null, nextProps.pages)
      });
    }

    this.changePage(nextProps.page + 1);
  }

  filterPages = (visiblePages, totalPages) => {
    return visiblePages.filter(page => page <= totalPages);
  };

  getVisiblePages = (page, total) => {
    if (total < 6) {
      return this.filterPages([1, 2, 3, 4, 5], total);
    } else {
      if (page % 5 >= 0 && page > 3 && page + 2 < total) {
        return [page - 2, page - 1, page, page + 1, page + 2];
      } else if (page % 5 >= 0 && page > 3 && page + 2 >= total) {
        return [total - 4, total - 3, total - 2, total - 1, total];
      } else {
        return [1, 2, 3, 4, 5];
      }
    }
  };

  changePage(page) {
    const activePage = this.props.page + 1;

    if (page === activePage) {
      return;
    }

    const visiblePages = this.getVisiblePages(page, this.props.pages);

    this.setState({
      visiblePages: this.filterPages(visiblePages, this.props.pages)
    });

    this.props.onPageChange(page - 1);
  }

  render() {
    const { PageButtonComponent = defaultButton, onPageSizeChange, pageSize, rowCount } = this.props;
    const { visiblePages } = this.state;
    const activePage = this.props.page + 1;
    const total = this.props.pages;
    
    return (
      <div className="row">
        <div className="col-lg-6">
        </div>
        <div className="col-lg-6">
          <div className="pagination">
            Display
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
            &nbsp;/ {parseInt(rowCount, 10)} result(s)
          </div>
          <ul className="pagination pull-right" style={{ cursor: 'pointer' }}>
            <li className={activePage < 2 ? 'disabled' : ''}>
              <a
                className="pagination-link"
                onClick={this.changePage.bind(null, 1)}
              >
                <i className="icon-double-angle-left"/>
              </a>
            </li>
            <li className={activePage < 2 ? 'disabled' : ''}>
              <a
                className="pagination-link"
                onClick={this.changePage.bind(null, activePage - 1)}
              >
                <i className="icon-angle-left"/>
              </a>
            </li>
            <If condition={activePage > 3 && total > 5}>
              <li className="disabled">
                <a >…</a>
              </li>
            </If>
            <For each="page" of={visiblePages}>
              <li key={page} className={activePage == page ? 'active' : ''}>
                <a
                  className="pagination-link"
                  onClick={this.changePage.bind(null, page)}
                >
                  {parseInt(page, 10)}
                </a>
              </li>
            </For>
            <If condition={activePage < total - 2 && total > 5}>
              <li className="disabled">
                <a >…</a>
              </li>
            </If>
            <li className={(activePage >= total) ? 'disabled' : ''}>
              <a
                className="pagination-link"
                onClick={this.changePage.bind(null, activePage + 1)}
              >
                <i className="icon-angle-right"/>
              </a>
            </li>
            <li className={(activePage >= total) ? 'disabled' : ''}>
              <a
                className="pagination-link"
                onClick={this.changePage.bind(null, total)}
              >
                <i className="icon-double-angle-right"/>
              </a>
            </li>
          </ul>
        </div>
      </div>
    );

    /**
     * <div className="Table__pagination">
     <div className="Table__prevPageWrapper">
     <PageButtonComponent
     className="Table__pageButton"
     onClick={() => {
              if (activePage === 1) return;
              this.changePage(activePage - 1);
            }}
     disabled={activePage === 1}
     >
     {this.props.previousText}
     </PageButtonComponent>
     </div>
     <div className="Table__visiblePagesWrapper">
     {visiblePages.map((page, index, array) => {
       return (
         <PageButtonComponent
           key={page}
           className={
             activePage === page
               ? "Table__pageButton Table__pageButton--active"
               : "Table__pageButton"
           }
           onClick={this.changePage.bind(null, page)}
         >
           {array[index - 1] + 2 < page ? `...${page}` : page}
         </PageButtonComponent>
       );
     })}
     </div>
     <div className="Table__nextPageWrapper">
     <PageButtonComponent
     className="Table__pageButton"
     onClick={() => {
              if (activePage === this.props.pages) return;
              this.changePage(activePage + 1);
            }}
     disabled={activePage === this.props.pages}
     >
     {this.props.nextText}
     </PageButtonComponent>
     </div>
     </div>
     */
  }
}
