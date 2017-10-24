angular.module('supla-scripts').component 'sceneDetailsPage',
  templateUrl: 'app/scenes/details/scene-details-page.html'
  bindings:
    scene: '<'
  controller: (swangular, $scope, $state, Notifier) ->
    new class
      saveScene: (newData) ->
        angular.extend(@scene, newData)
        @scene.put().then =>
          $state.go($state.current.name, {}, reload: yes)

      reloadScene: ->
        @scene.get().then (scene) =>
          angular.extend(@scene, scene)

      deleteScene: ->
        swangular.open
          scope: $scope
          type: 'question'
          title: 'Na pewno?'
          text: 'Czy chcesz usunąć tę scenę?'
          showCancelButton: yes
          showConfirmButton: yes
          cancelButtonText: 'Anuluj'
          confirmButtonText: 'Tak, usuń'
          confirmButtonColor: '#d62c1a'
          showLoaderOnConfirm: true
          preConfirm: => @scene.remove()
        .then =>
          Notifier.success('Scena została usunięta.')
          $state.go('scenes', {}, reload: yes)
