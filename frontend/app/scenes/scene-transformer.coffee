angular.module('supla-scripts').run (Restangular, Scenes) ->
  Restangular.extendModel Scenes.one('').route, (scene) ->
    if scene.id
      scene.executeWithFeedback = ->
        scene.patch().then (feedback) ->
          if feedback
            swal
              type: 'info'
              text: feedback
    if angular.isArray(scene.actions)
      scene.actions = {0: scene.actions[0]}
    scene
