window.OneSignal = window.OneSignal || [];
OneSignal.push(function() {
  OneSignal.init({
    appId: "YOUR_ONESIGNAL_APP_ID",
    safari_web_id: "YOUR_SAFARI_WEB_ID", // opzionale
    notifyButton: {
      enable: true,
    },
    allowLocalhostAsSecureOrigin: true
  });
});