angular.module('supla-scripts').filter 'scenePublicUrl', ($location) ->
  (slug) ->
    $location.absUrl().replace($location.url(), '') + '/api/scene/execute/' + slug
