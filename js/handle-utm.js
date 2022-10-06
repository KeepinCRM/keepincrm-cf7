jQuery(function ($) {
  var qvars = getUrlVars();

  $.each(['utm_source', 'utm_medium', 'utm_term', 'utm_content', 'utm_campaign'], function (_, v) {
    var cookie_field = GetQVars(v, qvars);

    if (cookie_field != '') {
      Cookies.set(v, cookie_field, { expires: 30 });
    }
  });
});

function GetQVars(v, qvars) {
  if (qvars[v] != undefined) {
    return decodeURI(qvars[v]);
  }

  return '';
}

function getUrlVars() {
  var vars = {};

  window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function (_, key, value) {
    vars[key] = decodeURI(value);
  });

  return vars;
}
