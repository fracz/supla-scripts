angular.module('supla-scripts').config ($httpProvider, jwtOptionsProvider) ->
  jwtOptionsProvider.config
    tokenGetter: (Token) ->
      'ngInject'
      Token.getRememberedToken()
  $httpProvider.interceptors.push('jwtInterceptor')
