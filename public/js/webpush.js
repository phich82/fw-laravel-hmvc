/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!*********************************!*\
  !*** ./resources/js/webpush.js ***!
  \*********************************/
var Browser = function () {
  var targetBrowser;
  var dataBrowser = [{
    target: "Edge",
    name: "MS Edge",
    shortname: 'edge'
  }, {
    target: "MSIE",
    name: "Explorer",
    shortname: 'ie'
  }, {
    target: "Trident",
    name: "Explorer",
    shortname: 'ie'
  }, {
    target: "Firefox",
    name: "Firefox",
    shortname: 'firefox'
  }, {
    target: "Opera",
    name: "Opera",
    shortname: 'opera'
  }, {
    target: "OPR",
    name: "Opera",
    shortname: 'opera'
  }, {
    target: "Chrome",
    name: "Chrome",
    shortname: 'chrome'
  }, {
    target: "Safari",
    name: "Safari",
    shortname: 'safari'
  }];

  function searchBrowser(data) {
    for (var i = 0; i < data.length; i++) {
      targetBrowser = data[i].target;

      if (navigator.userAgent.indexOf(data[i].target) !== -1) {
        return {
          name: data[i].name,
          shortname: data[i].shortname
        };
      }
    }

    return {
      name: 'Other',
      shortname: 'other'
    };
  }

  function searchVersion(userAgent) {
    var index = userAgent.indexOf(targetBrowser);

    if (index === -1) {
      return;
    }

    var rv = userAgent.indexOf("rv:");

    if (targetBrowser === "Trident" && rv !== -1) {
      return parseFloat(userAgent.substring(rv + 3));
    }

    return parseFloat(userAgent.substring(index + targetBrowser.length + 1));
  }

  function searchIP(callback) {
    callback = callback || function () {};

    var apiUrl = 'https://api.ipify.org?format=json'; // http://ipinfo.io

    return fetch(apiUrl, {
      headers: {
        'Accept': 'application/json'
      }
    }).then(function (response) {
      return response.json();
    });
  }

  return Object.assign(searchBrowser(dataBrowser), {
    version: searchVersion(navigator.userAgent) || searchVersion(navigator.appVersion) || "Unknown",
    ip: searchIP
  });
}();

var IP;
Browser.ip().then(function (response) {
  IP = response.ip;
});

function initPush() {
  if (!navigator.serviceWorker.ready) {
    return;
  }

  new Promise(function (resolve, reject) {
    var permissionResult = Notification.requestPermission(function (result) {
      resolve(result);
    });

    if (permissionResult) {
      permissionResult.then(resolve, reject);
    }
  }).then(function (permissionResult) {
    if (permissionResult !== 'granted') {
      throw new Error('We weren\'t granted permission.');
    }

    subscribeUser();
  });
}

function subscribeUser() {
  navigator.serviceWorker.ready.then(function (registration) {
    var subscribeOptions = {
      userVisibleOnly: true,
      applicationServerKey: urlBase64ToUint8Array('BERpG0wUiCWEsUuzjq-XRsprcOPBe3tdcj6VYrOj98StkKLJTlNDzPk9ZWxNm5ebmzK2MgQsYfeoYZUjDiT0U04')
    };
    return registration.pushManager.subscribe(subscribeOptions);
  }).then(function (pushSubscription) {
    console.log('Received PushSubscription: ', JSON.stringify(pushSubscription));
    storePushSubscription(pushSubscription);
  });
}

function urlBase64ToUint8Array(base64String) {
  var padding = '='.repeat((4 - base64String.length % 4) % 4);
  var base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
  var rawData = window.atob(base64);
  var outputArray = new Uint8Array(rawData.length);

  for (var i = 0; i < rawData.length; ++i) {
    outputArray[i] = rawData.charCodeAt(i);
  }

  return outputArray;
}

function storePushSubscription(pushSubscription) {
  var token = document.querySelector('meta[name=csrf-token]').getAttribute('content');
  var contentEncoding = (PushManager.supportedContentEncodings || ['aesgcm'])[0];
  var subscription = Object.assign(pushSubscription.toJSON(), {
    contentEncoding: contentEncoding
  });

  var registerPushNotification = function registerPushNotification() {
    fetch('/register-push-notification', {
      method: 'POST',
      body: JSON.stringify({
        subscription: subscription,
        ip: IP,
        browser: Browser.shortname
      }),
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': token
      },
      credentials: 'same-origin'
    }).then(function (res) {
      return res.json();
    }).then(function (res) {
      console.log(res);
    })["catch"](function (err) {
      console.log(err);
    });
  };

  if (IP) {
    registerPushNotification();
  } else {
    Browser.ip().then(function (response) {
      IP = response.ip;
      registerPushNotification();
    });
  }
}

document.addEventListener('DOMContentLoaded', function () {
  if ("serviceWorker" in navigator && "PushManager" in window) {
    // register the service worker
    navigator.serviceWorker.register('../service-worker.js').then(function () {
      console.log('serviceWorker installed!');
      initPush();
    })["catch"](function (err) {
      console.log(err);
    });
  }
});
/******/ })()
;