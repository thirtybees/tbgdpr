(function () {
  window.approveRemovalRequest = function (idRequest) {
    var approveButton = document.querySelector('[data-approve-request="' + idRequest +'"]');
    if (approveButton) {
      approveButton.setAttribute('disabled', 'disabled');
    }
    var denyButton = document.querySelector('[data-deny-request="' + idRequest +'"]');
    if (denyButton) {
      denyButton.setAttribute('disabled', 'disabled');
    }

    var request = new XMLHttpRequest();
    request.open('POST', window.TbGdprModule.urls.base + '&action=ApproveRemovalRequest&ajax=1', true);

    request.onreadystatechange = function () {
      if (this.readyState === 4) {
        if (this.status >= 200 && this.status < 400) {
          // Success!
          try {
            var data = JSON.parse(this.responseText);
            if (data.success) {
              window.showSuccessMessage(data.message);
            } else {
              window.showErrorMessage(data.message);
            }
          } catch (e) {
            console.log(e);
          }
        } else {
        }
        request = null;
      }
    };

    request.send(JSON.stringify({
      idRequest: idRequest,
    }));
  };

  window.denyRemovalRequest = function (idRequest) {
    var request = new XMLHttpRequest();
    request.open('POST', window.TbGdprModule.urls.base + '&action=DenyRemovalRequest&ajax=1', true);

    request.onreadystatechange = function () {
      if (this.readyState === 4) {
        if (this.status >= 200 && this.status < 400) {
          // Success!
          try {
            var data = JSON.parse(this.responseText);
            if (data.success) {
              window.showSuccessMessage(data.message);
            } else {
              window.showErrorMessage(data.message);
            }
          } catch (e) {
            console.log(e);
          }
        } else {
        }
        request = null;
      }
    };

    request.send(JSON.stringify({
      idRequest: idRequest,
    }));
  };
}());
