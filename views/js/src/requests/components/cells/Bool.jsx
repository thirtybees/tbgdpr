import React from 'react';
import PropTypes from 'prop-types';

export default class Bool extends React.Component {
  static propTypes = {
    enabled: PropTypes.bool,
    translations: PropTypes.object,
  };

  render() {
    const { enabled, translations } = this.props;

    return (
      <div>
        <Choose>
          <When condition={enabled}>
            <a className="list-action-enable action-enabled" title={translations.enabled}>
              <i className="icon-check"/>
            </a>
          </When>
          <Otherwise>
            <a className="list-action-enable action-disabled" title={translations.disabled}>
              <i className="icon-remove"/>
            </a>
          </Otherwise>
        </Choose>
      </div>
    );
  }
}

