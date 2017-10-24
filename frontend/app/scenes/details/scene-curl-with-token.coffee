angular.module('supla-scripts').filter 'sceneCurlWithToken', (sceneUrlFilter) ->
  (scene) ->
    sceneUrl = sceneUrlFilter(scene.id)
    header = "Authorization: Bearer " + scene.token
    "curl #{sceneUrl} -X GET -m 10000 -H \"#{header}\""
