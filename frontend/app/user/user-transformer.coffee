angular.module('supla-scripts').factory 'UserTransformer', (TimestampsTransformer) ->
  (user) ->
    TimestampsTransformer(user, ['lastLoginDate'])

angular.module('supla-scripts').run (Restangular, Users, UserTransformer) ->
  Restangular.extendModel(Users.one().route, UserTransformer)
