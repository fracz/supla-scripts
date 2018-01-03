angular.module('supla-scripts').run (Restangular, Scenes) ->
  Restangular.extendModel Scenes.one('').route, (scene) ->
    if scene.id
      scene.executeWithFeedback = ->
        scene.patch().then (feedback) ->
          if feedback
            swal
              type: 'info'
              text: feedback
          scene.get().then ({pending_scenes}) ->
            scene.pending_scenes = pending_scenes
      scene.clearPending = ->
        scene.all('pending').remove().then ({pending_scenes}) ->
          scene.pending_scenes = pending_scenes
    if angular.isArray(scene.actions)
      scene.actions = {0: scene.actions[0]}
    scene
