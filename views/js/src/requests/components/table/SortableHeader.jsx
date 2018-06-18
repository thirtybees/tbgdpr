import React from 'react';
import PropTypes from 'prop-types';
import xss from 'xss';

export default class SortableHeader extends React.Component {
  static propTypes = {
    name: PropTypes.string,
    sort: PropTypes.string,
    setSort: PropTypes.func,
  };
  
  render() {
    const { name, id, sort, setSort } = this.props;

    return (
      <span className="title_box active">
        {xss(name)}&nbsp;
        <a
          className={sort === 'desc' ? 'active' : ''}
          style={{ cursor: 'pointer' }}
          onClick={(e) => setSort(id, true, e.nativeEvent.shiftKey)}
        >
          <i className="icon-caret-down"/>
        </a>
        &nbsp;
        <a
          className={sort === 'asc' ? 'active' : ''}
          style={{ cursor: 'pointer' }}
          onClick={(e) => setSort(id, false, e.nativeEvent.shiftKey)}
        >
          <i className="icon-caret-up"/>
        </a>
      </span>
    );
  }
}
