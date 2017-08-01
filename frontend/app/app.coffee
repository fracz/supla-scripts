angular.module 'supla-scripts', [
  'angular-cache'
  'angular.filter'
  'angular-jwt'
  'angularMoment'
  'angularPromiseButtons'
  'elif'
  'ngAnimate'
  'ngFx'
  'ngStorage'
  'picardy.fontawesome'
  'restangular'
  'rzModule'
  'toastr'
  'ui.router'
  'ui.bootstrap.showErrors'
  'uiSwitch'
]
.config(($compileProvider, ANGULAR_DEBUG_DATA_ENABLED) -> $compileProvider.debugInfoEnabled(ANGULAR_DEBUG_DATA_ENABLED))
.run (Restangular) -> # synchronize browser time with server's
  requestStartTime = Date.now()
  Restangular.one('time').withHttpConfig(skipErrorHandler: yes).get().then (serverTime) ->
    requestTime = Date.now() - requestStartTime
    offset = new Date(serverTime).getTime() - Date.now() + requestTime
    moment.now = -> Date.now() + offset
