angular.module('supla-scripts').service 'Devices', (Restangular, CacheFactory) ->
  Devices = Restangular.service('devices')

  devicesCache = CacheFactory.get('devicesCache') or CacheFactory 'devicesCache',
    maxAge: 5 * 60 * 1000
    deleteOnExpire: 'aggressive'

  Devices.getList = (params) ->
    Restangular.all('devices').withHttpConfig(cache: devicesCache).getList(params)

  Devices
