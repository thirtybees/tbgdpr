import React, { Component } from 'react';
import classnames from 'classnames';
import { TheadComponent } from 'react-table';

export default class TableTHead extends Component {
  static propTypes = {};

  render() {
    const { style, children } = this.props;
    return (
      <div
        className={classnames('rt-th', 'rt-th-sort')}
        style={style}
      >
        {children}
      </div>
    );
  }
}
