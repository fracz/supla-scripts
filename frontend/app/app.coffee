angular.module 'supla-scripts', [
  '720kb.tooltips'
  'angular-cache'
  'angular-clipboard'
  'angular.filter'
  'angular-jwt'
  'angularMoment'
  'angularPromiseButtons'
  'angular-svg-round-progressbar'
  'chart.js'
  'elif'
  'monospaced.elastic'
  'ngAnimate'
  'ngFx'
  'ngStorage'
  'picardy.fontawesome'
  'restangular'
  'rzModule'
  'smartArea'
  'swangular'
  'toastr'
  'ui.router'
  'ui.bootstrap.showErrors'
  'uiSwitch'
]
.config(($compileProvider, ANGULAR_DEBUG_DATA_ENABLED) -> $compileProvider.debugInfoEnabled(ANGULAR_DEBUG_DATA_ENABLED))
.config((msdElasticConfig) -> msdElasticConfig.append = '\n')
.run (Restangular, $rootScope) -> # synchronize browser time with server's
  requestStartTime = Date.now()
  Restangular.one('info').withHttpConfig(skipErrorHandler: yes).get().then (response) ->
    requestTime = Date.now() - requestStartTime
    offset = new Date(response.time).getTime() - Date.now() + requestTime
    moment.now = -> Date.now() + offset
    $rootScope.APP_CONFIG = response

