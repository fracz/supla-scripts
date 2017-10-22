angular.module('supla-scripts').component 'scenesPage',
  templateUrl: 'app/scene/scenes-page.html'
  controller: (Scenes, $state) ->
    $onInit: ->
      Scenes.getList().then((@scenes) =>)

    addNewScene: (scene) ->
      Scenes.post(scene).then (savedScene) =>
        @scenes.push(savedScene)
        $state.go('scenes.details', {id: savedScene.id})
