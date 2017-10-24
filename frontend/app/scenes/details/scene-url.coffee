angular.module('supla-scripts').filter 'sceneUrl', (appUrlFilter) ->
  (slug, publicUrl = no) ->
    appUrlFilter('/api/scenes/' + (if publicUrl then 'public' else 'execute') + '/' + slug)
