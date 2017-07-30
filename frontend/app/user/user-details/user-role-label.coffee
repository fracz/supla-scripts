angular.module('supla-scripts').filter 'userRoleLabel', ->
  (user) ->
    role = user?.role or user
    switch role
      when 'admin' then 'Administrator'
      when 'coordinator' then 'Koordynator'
      else
        'Użytkownik'
