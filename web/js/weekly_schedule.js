(function ($) {
  'use strict';

  var DayScheduleSelector = function (el, options) {
    this.$el = $(el);
    this.options = $.extend({}, DayScheduleSelector.DEFAULTS, options);
    this.render();
    this.attachEvents();
    this.$selectedStart = null;
    this.$someSelected = false;

  };

  DayScheduleSelector.DEFAULTS = {
    days        : [0, 1, 2, 3, 4, 5, 6],  // Sun - Sat
    startTime   : '08:00',                // HH:mm format
    endTime     : '20:00',                // HH:mm format
    interval    : 30,                     // minutes
    stringDays  : ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
    template    : '<div class="day-schedule-selector">'         +
                    '<table class="schedule-table">'            +
                      '<thead class="schedule-header"></thead>' +
                      '<tbody class="schedule-rows"></tbody>'   +
                    '</table>'                                  +
                  '<div>'
  };

  /**
   * Render the calendar UI
   * @public
   */
  DayScheduleSelector.prototype.render = function () {
    this.$el.html(this.options.template);
    this.renderHeader();
    this.renderRows();
  };

  /**
   * Render the calendar header
   * @public
   */
  DayScheduleSelector.prototype.renderHeader = function () {
    var stringDays = this.options.stringDays
      , days = this.options.days
      , html = '';

    $.each(days, function (i, _) { html += '<th>' + (stringDays[i] || '') + '</th>'; });
    this.$el.find('.schedule-header').html('<tr><th></th>' + html + '</tr>');
  };

  /**
   * Render the calendar rows, including the time slots and labels
   * @public
   */
  DayScheduleSelector.prototype.renderRows = function () {
    var start = this.options.startTime
      , end = this.options.endTime
      , interval = this.options.interval
      , days = this.options.days
      , $el = this.$el.find('.schedule-rows');

    $.each(generateDates(start, end, interval), function (i, d) {
      var daysInARow = $.map(new Array(days.length), function (_, i) {
        return '<td class="time-slot" data-date="' + today_date(i) + '" data-time="' + hhmm(d) + '" data-day="' + days[i] + '"></td>'
      }).join();

      $el.append('<tr><td class="time-label">' + hmmAmPm(d) + '</td>' + daysInARow + '</tr>');
    });
  };

  /**
   * Is the day schedule selector in selected mode?
   * @public
   */
    DayScheduleSelector.prototype.isSelected = function () {
      return !!this.$selectedStart;
    };

  DayScheduleSelector.prototype.isSomeSelected = function () {
      return this.$someSelected;
    };
  DayScheduleSelector.prototype.deselect = function ($slot) {
    $slot.attr('data-status', 'available');
    this.$someSelected = false;
    $('#id_fecha').val('');
    $('#id_hora').val('');
  };
  DayScheduleSelector.prototype.select = function ($slot) {
    $slot.attr('data-status', 'selected');
    this.$someSelected = true;
    $('#id_fecha').val($slot.attr('data-date'));
    $('#id_hora').val($slot.attr('data-time'));
    $('.active_modal').click();
  };
  DayScheduleSelector.prototype.occupied = function ($slot) {  $slot.attr('data-status', 'occupied'); };

  function isSlotAvailable($slot) { return $slot.attr('data-status') == 'available'; }
  function isSlotSelected($slot) { return $slot.attr('data-status') == 'selected'; }
  function isSlotOccupied($slot) { return $slot.attr('data-status') == 'occupied'; }


  DayScheduleSelector.prototype.attachEvents = function () {
    var plugin = this
      , options = this.options
      , $slots;

    this.$el.on('click', '.time-slot', function () {
      var day = $(this).data('day');
      if (!isSlotSelected($(this))) {  // if we are not in selected mode
        if (isSlotAvailable($(this)) && !plugin.isSomeSelected()) {
          plugin.select($(this));
          }
      } else {  // if we are in selected mode
        plugin.deselect($(this));

      }
    });

  };

  /**
   * Serialize the selections
   * @public
   * @returns {Object} An object containing the selections of each day, e.g.
   *    {
   *      0: [],
   *      1: [["15:00", "16:30"]],
   *      2: [],
   *      3: [],
   *      5: [["09:00", "12:30"], ["15:00", "16:30"]],
   *      6: []
   *    }
   */
  DayScheduleSelector.prototype.serialize = function () {
    var plugin = this
      , selections = {};

    $.each(this.options.days, function (_, v) {
      var start, end;
      start = end = false; selections[v] = [];
      plugin.$el.find(".time-slot[data-day='" + v + "']").each(function () {
        // Start of selection
        if (isSlotAvailable($(this)) && !start) {
          start = $(this).data('time');
        }

        // End of selection (I am not available, so select until my previous one.)
        if (!isSlotAvailable($(this)) && !!start) {
          end = $(this).data('time');
        }

        // End of selection (I am the last one :) .)
        if (isSlotAvailable($(this)) && !!start && $(this).is(".time-slot[data-day='" + v + "']:last")) {
          end = secondsSinceMidnightToHhmm(
            hhmmToSecondsSinceMidnight($(this).data('time')) + plugin.options.interval * 60);
        }

        if (!!end) { selections[v].push([start, end]); start = end = false; }
      });
    })
    return selections;
  };

  /**
   * Deserialize the schedule and render on the UI
   * @public
   * @param {Object} schedule An object containing the schedule of each day, e.g.
   *    {
   *      0: [],
   *      1: [["15:00", "16:30"]],
   *      2: [],
   *      3: [],
   *      5: [["09:00", "12:30"], ["15:00", "16:30"]],
   *      6: []
   *    }
   */
  DayScheduleSelector.prototype.deserialize = function (schedule) {
    var plugin = this, i;
    $.each(schedule, function(d, ds) {
      var $slots = plugin.$el.find('.time-slot[data-day="' + d + '"]');
      $.each(ds, function(p, f) {
        for (i = 0; i < $slots.length; i++) {
          if ($slots.eq(i).data('time') >= f) { break; }
          if ($slots.eq(i).data('time') >= p) { plugin.deselect($slots.eq(i)); }
        }
      })
    });
  };

  DayScheduleSelector.prototype.deserializeOccupied = function (schedule) {
    var plugin = this, i;
    $.each(schedule, function(d, ds) {
      var $slots = plugin.$el.find('.time-slot[data-day="' + d + '"]');
      $.each(ds, function(p, f) {
        for (i = 0; i < $slots.length; i++) {
          if ($slots.eq(i).data('time') >= f) { break; }
          if ($slots.eq(i).data('time') >= p) { plugin.occupied($slots.eq(i)); }
        }
      })
    });
  };

  // DayScheduleSelector Plugin Definition
  // =====================================

  function Plugin(option) {
    return this.each(function (){
      var $this   = $(this)
        , data    = $this.data('artsy.dayScheduleSelector')
        , options = typeof option == 'object' && option;

      if (!data) {
        $this.data('artsy.dayScheduleSelector', (data = new DayScheduleSelector(this, options)));
      }
    })
  }

  $.fn.dayScheduleSelector = Plugin;

  /**
   * Generate Date objects for each time slot in a day
   * @private
   * @param {String} start Start time in HH:mm format, e.g. "08:00"
   * @param {String} end End time in HH:mm format, e.g. "21:00"
   * @param {Number} interval Interval of each time slot in minutes, e.g. 30 (minutes)
   * @returns {Array} An array of Date objects representing the start time of the time slots
   */
  function generateDates(start, end, interval) {
    var numOfRows = Math.ceil(timeDiff(start, end) / interval);
    return $.map(new Array(numOfRows), function (_, i) {
      // need a dummy date to utilize the Date object
      return new Date(new Date(2000, 0, 1, start.split(':')[0], start.split(':')[1]).getTime() + i * interval * 60000);
    });
  }

  /**
   * Return time difference in minutes
   * @private
   */
  function timeDiff(start, end) {   // time in HH:mm format
    // need a dummy date to utilize the Date object
    return (new Date(2000, 0, 1, end.split(':')[0], end.split(':')[1]).getTime() -
            new Date(2000, 0, 1, start.split(':')[0], start.split(':')[1]).getTime()) / 60000;
  }

  /**
   * Return the actual day
   * */
      function getQueryParams(qs) {
        qs = qs.split("+").join(" ");
        var params = {},
            tokens,
            re = /[?&]?([^=]+)=([^&]*)/g;

        while (tokens = re.exec(qs)) {
            params[decodeURIComponent(tokens[1])]
                = decodeURIComponent(tokens[2]);
        }

        return params;
    }

    function today_date(i){
        var $_GET = getQueryParams(document.location.search);
        var week = $_GET['week'];

        if (week != undefined){
          if(week > 50){week=50}else if (week < -50){week=-50}
          i = (week*7) + i;
        }

        var d = new Date();
        d.setDate(d.getDate() - d.getDay() + 1);
        var day_f = d.getDate();
        d.setDate(day_f + i);
        var month_l = d.getMonth()+1;
        var day_l = d.getDate();
        var year_l = d.getFullYear();

      return (day_l<10 ? '0' : '') +  day_l + '/' +
                    (month_l<10 ? '0' : '') + month_l + '/' +
                    year_l
    }

  /**
   * Convert a Date object to time in H:mm format with am/pm
   * @private
   * @returns {String} Time in H:mm format with am/pm, e.g. '9:30am'
   */
  function hmmAmPm(date) {
    var hours = date.getHours()
      , minutes = date.getMinutes()
      , ampm = hours >= 12 ? 'pm' : 'am';
    return hours + ':' + ('0' + minutes).slice(-2) + ampm;
  }

  /**
   * Convert a Date object to time in HH:mm format
   * @private
   * @returns {String} Time in HH:mm format, e.g. '09:30'
   */
  function hhmm(date) {
    var hours = date.getHours()
      , minutes = date.getMinutes();
    return ('0' + hours).slice(-2) + ':' + ('0' + minutes).slice(-2);
  }

  function hhmmToSecondsSinceMidnight(hhmm) {
    var h = hhmm.split(':')[0]
      , m = hhmm.split(':')[1];
    return parseInt(h, 10) * 60 * 60 + parseInt(m, 10) * 60;
  }

  /**
   * Convert seconds since midnight to HH:mm string, and simply
   * ignore the seconds.
   */
  function secondsSinceMidnightToHhmm(seconds) {
    var minutes = Math.floor(seconds / 60);
    return ('0' + Math.floor(minutes / 60)).slice(-2) + ':' +
           ('0' + (minutes % 60)).slice(-2);
  }

  // Expose some utility functions
  window.DayScheduleSelector = {
    ssmToHhmm: secondsSinceMidnightToHhmm,
    hhmmToSsm: hhmmToSecondsSinceMidnight
  };

})(jQuery);
