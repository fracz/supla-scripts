angular.module('supla-scripts').component 'timezonePicker',
  template: '
    <select class="form-control"
            ng-model="$ctrl.timezone"
            ng-options="timezone.name as $ctrl.label(timezone) for timezone in $ctrl.timezones"></select>
  '
  require:
    ngModel: 'ngModel'
  controller: class
    $onInit: ->
      @ngModel.$render = => @timezone = @ngModel.$viewValue
      @timezones = @getAvailableTimezones()

    label: ({name, offset, currentTime}) ->
      "#{name} (UTC#{if offset >= 0 then '+' else ''}#{offset}) #{currentTime}"

    getAvailableTimezones: ->
      moment.tz.names()
        .filter((timezone) -> timezone.match(/^(America|Antartica|Arctic|Asia|Atlantic|Europe|Indian|Pacific|UTC)\//))
        .map (timezone) ->
          name: timezone,
          offset: moment.tz(timezone).utcOffset() / 60,
          currentTime: moment.tz(timezone).format('H:mm')
        .sort (timezone1, timezone2) ->
          if timezone1.offset == timezone2.offset
            if timezone1.name < timezone2.name then -1 else 1
          else
            timezone1.offset - timezone2.offset
