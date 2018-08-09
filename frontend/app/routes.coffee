angular.module('supla-scripts')

.config (RestangularProvider) ->
  RestangularProvider.setBaseUrl('/api')

.config ($urlRouterProvider, $locationProvider, $urlMatcherFactoryProvider) ->
  $urlRouterProvider.otherwise ($injector, $location) ->
    $state = $injector.get('$state')
    $state.go('notFound')
    $location.path()
  $urlRouterProvider.when('', '/')
  $locationProvider.html5Mode(true)
  $urlMatcherFactoryProvider.defaultSquashPolicy(true)

.config ($stateProvider) ->
  $stateProvider
  .state 'home',
    url: '/'
    template: '<home-view></home-view>'

  .state 'login',
    url: '/login?register'
    template: '<login-view></login-view>'

  .state 'oauthAuthorize',
    url: '/authorize?code'
    template: '<oauth-authorize-page></oauth-authorize-page>'

  .state 'register',
    url: '/register'
    template: '<register-view></register-view>'

  .state 'dashboard',
    url: '/dashboard'
    template: '<dashboard></dashboard>'

  .state 'thermostats',
    url: '/thermostats'
    template: '<thermostats-list></thermostats-list>'

  .state 'thermostat',
    url: '/thermostat/{id}'
    abstract: true
    template: '<thermostat-details thermostat="thermostat"></thermostat-details>'
    controller: ($scope, thermostat) -> $scope.thermostat = thermostat
    resolve:
      thermostat: (Thermostats, $stateParams) -> Thermostats.one($stateParams.id).get(simple: yes)

  .state 'thermostat.rooms',
    url: '/rooms'
    template: '<thermostat-rooms-list thermostat="thermostat"></thermostat-rooms-list>'
    controller: ($scope, thermostat) -> $scope.thermostat = thermostat

  .state 'thermostat.profiles',
    url: '/profiles'
    template: '<thermostat-profiles-list thermostat="thermostat"></thermostat-profiles-list>'
    controller: ($scope, thermostat) -> $scope.thermostat = thermostat

  .state 'thermostat.preview',
    url: '/preview'
    template: '<thermostat-preview></thermostat-preview>'

  .state 'thermostat.logs',
    url: '/logs'
    template: '<div class="container"><logs-table entity-id="thermostat.id" limit="30"></logs-table></div>'
    controller: ($scope, thermostat) -> $scope.thermostat = thermostat

  .state 'thermostatPreview',
    url: '/thermostat-preview/:slug'
    template: '<thermostat-preview slug="slug"></thermostat-preview>'
    controller: ($scope, $stateParams) -> $scope.slug = $stateParams.slug

  .state 'temperatures',
    url: '/temperatures'
    template: '<temperature-history-page></temperature-history-page>'

  .state 'scenes',
    url: '/scenes'
    template: '<scenes-page></scenes-page>'

  .state 'scenes.details',
    url: '/:id'
    template: '<scene-details-page scene="scene"></scene-details-page>'
    controller: ($scope, scene) -> $scope.scene = scene
    resolve:
      scene: (Scenes, $stateParams) -> Scenes.one($stateParams.id).get()

  .state 'notifications',
    url: '/notifications'
    template: '<notifications-page></notifications-page>'

  .state 'notifications.details',
    url: '/:id'
    template: '<notification-details-page notification="notification"></notification-details-page>'
    controller: ($scope, notification) -> $scope.notification = notification
    resolve:
      notification: (Notifications, $stateParams) -> Notifications.one($stateParams.id).get()

  .state 'account',
    url: '/account'
    abstract: true
    template: '<account-page user="user"></account-page>'
    controller: ($scope, user) -> $scope.user = user
    resolve:
      user: (Users) -> Users.one('current').get()

  .state 'account.details',
    url: '/details'
    template: '<account-details user="user"></account-details>'
    controller: ($scope, user) -> $scope.user = user

  .state 'account.api',
    url: '/api'
    template: '<user-api-credentials user="user"></user-api-credentials>'
    controller: ($scope, user) -> $scope.user = user

  .state 'account.timezone',
    url: '/timezone'
    template: '<user-timezone user="user"></user-timezone>'
    controller: ($scope, user) -> $scope.user = user

  .state 'account.changePassword',
    url: '/password'
    template: '<user-password-change-form user="user"></user-password-change-form>'
    controller: ($scope, user) -> $scope.user = user

  .state 'account.delete',
    url: '/delete'
    template: '<account-delete user="user"></account-delete>'
    controller: ($scope, user) -> $scope.user = user

  .state 'logs',
    url: '/logs'
    template: '<logs-view></logs-view>'

  .state 'clients',
    url: '/clients'
    template: '<clients-list></clients-list>'

  .state 'notFound',
    templateUrl: 'app/common/errors/404.html'

  .state 'notAllowed',
    templateUrl: 'app/common/errors/403.html'

.run ($rootScope, $state, CacheFactory) ->
  $rootScope.$on 'AUTH_CHANGED', (event, user) ->
    CacheFactory.clearAll()
    $state.reload() if not user

###
  Automatically set location:replace for options when changing state with reloadOnSearch:false. This fixes the error when the
  "back" button changes the URL in browser but router does not handle it (as we told it not to do it by reloadOnSearch param).
###
angular.module('supla-scripts').decorator '$state', ($delegate) ->
  transitionTo = $delegate.transitionTo
  $delegate.transitionTo = (to, toParams, options = {}) ->
    toName = $delegate.get(to)?.name
    if $delegate.get(to)?.reloadOnSearch is false and $delegate.current.name is toName
      angular.extend(options, location: 'replace')
    transitionTo(to, toParams, options)
  $delegate

angular.module('supla-scripts').run ($rootScope, Token, $state) ->
  $rootScope.$on '$stateChangeStart', (event, toState) ->
    if not Token.getRememberedToken() and toState.name not in ['login', 'register', 'thermostatPreview', 'notFound', 'oauthAuthorize']
      event.preventDefault()
      $state.go('login')
