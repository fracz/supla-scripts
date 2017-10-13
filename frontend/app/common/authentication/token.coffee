angular.module('supla-scripts').service 'Token', (Restangular, $localStorage, $timeout, jwtHelper, $rootScope) ->
  Token = Restangular.service('tokens')

  LOCAL_STORAGE_KEY = 'jwtToken'

  refreshing = null

  refreshTokenWhenItIsAboutToExpire = ->
    if Token.getRememberedToken()
      expirationDate = jwtHelper.getTokenExpirationDate(Token.getRememberedToken())
      expiresIn = moment(expirationDate).diff(moment())
      $timeout.cancel(refreshing) if refreshing
      refreshing = $timeout(Token.renewToken, Math.max(expiresIn - 5 * 60 * 1000, 0)) # renew token 5 minutes before its expiration

  dispatchAuthEvent = ->
    $rootScope.$broadcast('AUTH_CHANGED', Token.getCurrentUser())

  Token.renewToken = ->
    Token.one().withHttpConfig(skipErrorHandler: yes).put()
      .then(({token}) -> Token.rememberToken(token))
      .catch(Token.forgetRememberedToken)

  Token.rememberToken = (token) ->
    try
      jwtHelper.decodeToken(token)
      $localStorage[LOCAL_STORAGE_KEY] = token
      refreshTokenWhenItIsAboutToExpire()
      dispatchAuthEvent()

  Token.getRememberedToken = -> $localStorage[LOCAL_STORAGE_KEY]
  Token.getRememberedTokenPayload = ->
    token = Token.getRememberedToken()
    if token
      if jwtHelper.isTokenExpired(token)
        Token.forgetRememberedToken()
      else
        jwtHelper.decodeToken(Token.getRememberedToken()) if Token.getRememberedToken()

  Token.hasUser = -> Token.getRememberedTokenPayload()?.user

  Token.getCurrentUser = ->
    Token.getRememberedTokenPayload()?.user

  Token.isPasswordExpired = ->
    !!Token.getRememberedTokenPayload()?.expiredPassword

  Token.authenticate = (userData) ->
    Token.one('').all('new').withHttpConfig(skipErrorHandler: yes)
      .post(userData)
      .then(({token}) -> Token.rememberToken(token))

  Token.forgetRememberedToken = ->
    if $localStorage[LOCAL_STORAGE_KEY]
      delete $localStorage[LOCAL_STORAGE_KEY]
      dispatchAuthEvent()

  if Token.getRememberedToken()
    if jwtHelper.isTokenExpired(Token.getRememberedToken())
      Token.forgetRememberedToken()
    else
      dispatchAuthEvent()
      Token.renewToken()

  # token expiration watchdog
  $interval(Token.getRememberedTokenPayload, 30000)

  Token
