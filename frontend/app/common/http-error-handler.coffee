angular.module('supla-scripts').config ($httpProvider) ->
  $httpProvider.interceptors.push ($q, $injector) ->
    responseError: (rejection) ->
      if not rejection.config?.skipErrorHandler
        Notifier = $injector.get('Notifier')
        if rejection.status <= 0
          Notifier.error(
            'Połączenie z serwerem nie powiodło się'
            'Sprawdź swoje połączenie z siecią lub poczekaj kilka chwil jeśli przypuszczasz, że problem może być po stronie serwera.'
          )
        else if rejection.status is 404 and rejection.config.method is 'GET'
          $injector.get('$timeout')(-> $injector.get('$state').go('notFound', {}, {reload: yes, location: no}))
        else if rejection.status in [401, 403] and rejection.config.method is 'GET'
          $injector.get('$timeout')(-> $injector.get('$state').go('notAllowed', {}, {reload: yes, location: no}))
        else if rejection.status isnt 404 and rejection.status > 0
          error = rejection.config.onError or ['Wystąpił nieoczekiwany błąd', rejection.data?.message]
          Notifier.error(error...)
      $q.reject(rejection)
