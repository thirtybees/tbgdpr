import React from 'react';

export default class Loading extends React.Component {
  render() {
    return (
      <div className="rt-spinner">
        <div className="bounce1"/>
        <div className="bounce2"/>
        <div className="bounce3"/>
      </div>
    );
  }
}
