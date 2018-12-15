angular.module('supla-scripts').component 'scenesDropdown',
  templateUrl: 'app/scenes/dropdown/scenes-dropdown.html'
  bindings:
    hideIds: '<'
  require:
    ngModel: 'ngModel'
  controller: (Scenes) ->
    new class
      $onInit: =>
        Scenes.getList().then (@scenes) =>
          @ngModel.$render = => @chosenSceneId = @ngModel.$viewValue

      isNotHidden: (scene) =>
        scene.id not in (@hideIds or [])

      updateModel: ->
        @ngModel.$setViewValue(@chosenSceneId)
