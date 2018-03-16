angular.module('supla-scripts').component 'thermostatProfileButtonSelector',
  template: '<button type="button" class="btn btn-info btn-lg" ng-click="$ctrl.nextProfile()" ng-disabled="$ctrl.disabled">
                USTAW PROFIL: {{ $ctrl.currentProfile.name }}</button>'
  bindings:
    thermostat: '<'
    disabled: '<'
  require:
    ngModel: 'ngModel'
  controller: ->
    new class
      $onInit: ->
        @ngModel.$render = => @$onChanges()

      $onChanges: (changes) =>
        if changes?.thermostat
          @thermostat.all('thermostat-profiles').getList().then (@availableProfiles) =>
            @availableProfiles.unshift({id: false, name: 'Brak'})
            @index = 0
            if @ngModel.$viewValue
              id = @ngModel.$viewValue.replace('thermostatSetProfile,', '')
              @index = @availableProfiles.map((o) -> o.id).indexOf(id)
              @index = 0 if @index <= 0
            @nextProfile()
        @nextProfile(changes?.thermostat?.isFirstChange())

      nextProfile: (isFirstChange) ->
        if @availableProfiles
          @currentProfile = @availableProfiles[@index++]
          @index = 0 if @index >= @availableProfiles.length
          @ngModel.$setViewValue("thermostatSetProfile,#{@currentProfile.id}") if not isFirstChange
