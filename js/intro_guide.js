(function ($, Drupal, drupalSettings) {

  'use strict';
  Drupal.behaviors.introGuide = {
    attach: function () {
      $('div#block-bartik-page-title .content').append('<div><h1 id="first">Customer</h1><h4 id="first2">Customer2</h4></div>');
      $('div#block-bartik-page-title .content').append('<div><h2 id="second">Investor</h2><h4 id="second2">Investor2</h4></div>');
      $('div#block-bartik-page-title .content').append('<div><h3 id="third">Medias</h3><h4 id="third2">Media2</h4></div>');
      $('div#block-bartik-page-title .content').append('<div><h3 id="fourth">Event</h3><h4 id="fourth2">Event2</h4></div>');
      $('div#block-bartik-page-title .content').append('<div id="dialog"></div>');
      $('div#block-bartik-page-title .content').append('<input type="button" value="Button Start" class="button">');
      var intro_load = drupalSettings.intro_js.intro_load;
      var intro_trigger = drupalSettings.intro_js.intro_trigger;
      var intro_steps = drupalSettings.intro_js.intro_steps;
      var event = drupalSettings.intro_js.intro_events;
      var intro_title = drupalSettings.intro_js.intro_title;
      var intro_message  = drupalSettings.intro_js.intro_message;
      var step = [];
      var buttonsOpts = {};

      var introguide = introJs();

      for (var i = 0; i < event.length; i++) {
        var st = [];
          $.each(intro_steps[i], function(el, desc) {
            var steps = {
              element: desc,
              intro: 'hello'
            };
            st.push(steps);
          });
        step.push(st);
      }

      $.each(event, function (e, name) {
        buttonsOpts[name] = function() {
          $(this).dialog("close");
            introguide.setOptions({
              steps: step[e]
            });
          introguide.start();
        };
      });

      function ConfirmUserInterest(message) {
        $('<div></div>').appendTo('body').html('<div><h6>'+message+'</h6></div>').dialog({
          modal: true,  title: intro_title, zIndex: 10000, autoOpen: true, width: 'auto', resizable: true, buttons: buttonsOpts,
          close: function (event, ui) {
            $(this).remove();
          }
        });
      }

      if (typeof(Storage) !== "undefined") {
        var user_record = localStorage.getItem('firsttime');
        if (user_record === null && intro_load === 'click') {
          $(intro_trigger).click(function () {
            ConfirmUserInterest(intro_message);
          });
          localStorage.setItem('firsttime', true);
        }
        else {
          ConfirmUserInterest(intro_message);
        }
        localStorage.removeItem('firsttime');
      }
      else {
        alert('Sorry, your browser does not support Web Storage...');
      }

    }
  };

})(jQuery, Drupal, drupalSettings);
