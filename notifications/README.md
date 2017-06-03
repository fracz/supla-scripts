# Notifications

## English instructions
TODO


## Polska instrukcja

### Instalacja

 1. Skonfiguruj skrypty SUPLA wg [instrukcji](https://forum.supla.org/viewtopic.php?f=24&t=2102).
 1. Skopiuj przykładowy plik `config.php.sample` konfigurujący powiadomienia i dostosuj go do swoich potrzeb (szczegóły konfiguracji niżej).
 1. Zainstaluj na swoim urządzeniu mobilnym (Android) aplikację [Automate](https://play.google.com/store/apps/details?id=com.llamalab.automate&hl=pl).
 1. Po otwarciu aplikacji kliknij ikonkę ludzików na gorze (Community) i znajdź Flow [`SUPLA Notifications`](https://llamalab.com/automate/community/flows/12861). Zainstaluj.
 1. Na stronie szczegółów flow włącz wszystkie uprawnienia (komunikacja z siecią oraz dostęp do plików).
 1. Uruchom flow. Przy pierwszym uruchomieniu zapyta się od miejsce instalacji skryptów - należy podać adres URL
    do miejsca gdzie zainstalowane są skrypty.
 1. Powinno działać.

### Opcje konfiguracji

W pliku `config.php` konfigurujemy powiadomienia, które mają być wyświetlane na urządzeniach
mobilnych.

Każde powiadomienie posiada kilka opcji konfiguracyjnych które są przedstawione
poniżej. Gwiazdką `*` oznaczono opcje wymagane.

#### `interval`*

Określa jak często powiadomienie powinno się pojawiać. Można tu wpisać trzy 
rodzaje wartości:

 * Liczba całkowita, określająca co ile sekund powiadomienie powinno się odświeżać;
   przykłady: `15`, `60`, `3600`.
 * Wyrażenie w notacji [crontab](http://www.nncron.ru/help/EN/working/cron-format.htm) określające
   kiedy powiadomienie ma być pokazywane; przykłady:
   `*/15 * * * *` (co 15 minut), `*/10 12,13 * * *` (co 10 minut, jeśli godzina to 12 lub 13)
 * Tabliica wyrażeń crontab, co pozwala na lepsze doprecyzowanie momentów;
   przykład: `['20 6 * * *', '*/10 20 * * *']` (codziennie o godzinie 6:20 oraz
   co 10 minut o godzienie 20)
   
#### `notification`*

Definiuje powiadomienie które ma się pokazać. Jest to tablica, która ma
następujące pola:

##### `title`*
Powiadomienie do wyświetlenia, np. *Brama jest otwarta*.

##### `message`
Dodatkowa informacja w powiadomieniu, pisana mniejszą czcionką. Szczegóły.

##### `icon`
Kod ikony do wyświetlenia w powiadomieniu. Domyślnie wyświetlany jest wykrzynik. Kody można brać z klasy
[`AutomateIcons`](https://github.com/fracz/supla-scripts/blob/master/utils/AutomateIcons.php).

Przykład: `'icon' => AutomateIcons::BULB`

##### `cancellable`
Przyjmuje wartość `true` lub `false`. `true` oznacza, że będziemy mogli zignorować powiadomienie (do następnego sprawdzenia),
odwołując je przez przeciągnięcie lub wyczysczenie powiadomień. Domyślnie `false`.

##### `ongoing`
`true` lub `false`.
Określa, czy powiadomienie powinno pokazać się jako "trwające" na samej górze.

##### `sound`
Czy odtwarzać dźwięk powiadomienia? `true` lub `false`, domyślnie `false`.

##### `vibrate`
Czy wibrować? `true` lub `false`, domyślnie `false`.

##### `flash`
Czy mrugać diodą urządzenia? `true` lub `false`, domyślnie `false`.

#### `awake`
Czy gdy nadejdzie czas powiadomenia, urządzenie powinno się obudzić? Ustawienie `false`
może spowodować opóźnienie powiadomienia (np. do czasu włączenia ekranu), ale dzięki temu powiadomienia będą mniej
wpływać na zużycie baterii. `true` oznacza wybudzanie urządzenia z każdym powiadomieniem.
Domyślnie `false.`

#### `condition`

Warunek wyświetlania powiadomienia. Dzięki temu możemy wyświetlać powiadomienie gdy jakiś warunek jest spełniony
(np. tylko gdy brama jest otwarta albo tylko gdy światło jest zaświecone).

Warunki zaimplementowane są w klasie [`Conditions`](https://github.com/fracz/supla-scripts/blob/master/utils/conditions/Conditions.php).

Przykład: `'condition' => Conditions::isOpened(1234)`, gdzie `1234` to id kanału czujnika bramy.

Można także nadać warunek zbudowany z innych (powiadomienie pokaże się jeśli
jeden z warunków będzie spełniony).

Przykład: `'condition' => Conditions::anyOf(Conditions::isOpened(1234), Conditions::isOpened(1235))`.

#### `valueProviders`

Jeśli chcemy użyć jakiejś wartości ze stanu urządzenia w powiadomieniu (np. temperatury, stanu otwarcia),
musimy dostarczyć do niego takie informacje. Wykorzystujemy do tego `valueProvidery`
zdefiniowane w klasie [`ValueProviders`](https://github.com/fracz/supla-scripts/blob/master/utils/valueproviders/ValueProviders.php).

Przykład:

```
'valueProviders' => ValueProviders::temperatureAndHumidity(444),
'notification' => [
  'title' => 'Temperatura to {temperature}'
],
```

Możemy odczytać wiele stanów jednocześnie, pamiętając o tym by potem w wiadomości
wskazać z którego kanału informacja ma być podana.

```
'valueProviders' => [
   ValueProviders::temperatureAndHumidity(444),
   ValueProviders::onOff(555),
],
'notification' => [
  'title' => 'Temperatura to {temperature|444}',
  'message' => 'Światło jest {on|555}.',
],
```

#### `actions`

Tablica zawierająca akcje możliwe do wykonania bezpośrednio z powiadomienia (w formie przycisków).

Każda akcja to kolejna sekcja konfiguracji zawierająca poniższe wartości.

##### `label`*

Napisz na przycisku. Np. `'label' => 'Włącz'`.

##### `icon`

Ikona przycisku.

##### `command`

Komenda do wykonania gdy akcja zostanie wybrana. Możliwe są dwie wartości.

 * komenda do wykonania w postaci [sceny](), np. `'command' => 'turnOff,123|turnOn,125'`
 * komenda odkładająca następne sprawdzenie powiadomienia na wybraną ilość sekund, niezależnie od skonfigurowanego `interval`, np.
   `'command' => 'postpone,30'`, `'command' => 'postponse,3600'`.
   
#### `retryInterval`

Jeśli warunek powiadomienia został spełniony (np. brama jest otwarta) to za ile sekund,
niezależnie od konfiguracji `interval` powinno nastąpić kolejna zmiana stanu.

Poniższa konfiguracja bedzie sprawdzać co 15 minut czy brama jest otwarta, ale jeśli
powiadomienie się pokaże to co minutę będzie sprawdzane, czy przypadkiem go nie schować.

Wartość opcjonalna. Domyślnie, kolejne sprawdzenie następuje zgodnie z konfiguracją w
`interval`.

```
'condition' => Conditions::isOpened(1234),
'interval' => '*/15 * * * *',
'retryInterval => 60,
```

### Numery kanałów w zmiennych

Zauważ, że by plik konfiguracyjny pisało się łatwiej, na jego początku
zostały zdefiniowane zmienne których potem można używać w konfiguracji.

```
$outsideDS18B20 = 120;
$insideDHT22 = 135;
$sampleBulb = 675;
$garageGateSensor = 768;
$garageGate = 769;
```

Nie jest to jednak wymagane.

### Przykłady

#### Powiadom mnie, jeśli światło jest włączone

Co 30 sekund sprawdzane jest czy żarówka jest włączona i jeśli tak - pokazuje się powiadomienie.
Następnie co 5 sekund sprawdzane jest czy się nie wyłączyła i jeśli tak - powiadomienie znika.
Powiadomienie można odwołać i dźwięk jest odtwarzany.
Mamy do wyboru dwie akcje: wyłączenie żarówki oraz odłożenie sprawdzania na
za 45 sekund.

```
[
    'condition' => Conditions::isTurnedOn(123),
    'valueProviders' => ValueProviders::onOff(123, 'włączona', 'wyłączona'),
    'interval' => 30,
    'retryInterval' => 5,
    'notification' => [
        'title' => 'Żarówa jest {on}.',
        'message' => 'Wyłącz ją bo marnujesz prąd.',
        'icon' => AutomateIcons::BULB,
        'cancellable' => true,
        'sound' => true,
    ],
    'actions' => [
        ['label' => 'Wyłącz', 'icon' => AutomateIcons::TURN_ON, 'command' => "turnOff,123"],
        ['label' => 'Sprawdź potem', 'icon' => AutomateIcons::CLOCK, 'command' => 'postpone,45'],
    ],
],
```

#### Powiadom mnie, że brama jest otwarta

Sprawdzaj co 15 minut czy brama jest otwarta, wybudzając urządzenie.

```
[
    'condition' => Conditions::isOpened($bramaDomowaCzujnik),
    'interval' => ['*/15 * * * *'],
    'awake' => true,
    'retryInterval' => 60,
    'notification' => [
        'title' => 'Brama domowa jest otwarta!',
        'icon' => AutomateIcons::LOCK_OPENED,
        'cancellable' => true,
        'sound' => true,
        'vibrate' => true,
    ],
    'actions' => [
        ['label' => 'Zamknij', 'icon' => AutomateIcons::LOCK_CLOSED, 'command' => "openClose,$bramaDomowa"],
        ['label' => 'Sprawdź teraz', 'icon' => AutomateIcons::REFRESH, 'command' => 'postpone,1'],
        ['label' => 'Sprawdź za godzinę', 'icon' => AutomateIcons::CLOCK, 'command' => 'postpone,3600'],
    ]
],
```

#### Powiadom mnie, że na zewnąrz jest chłodniej niż w domu

```
[
    'condition' => Conditions::firstTemperatureIsLowerThanSecond($outsideDS18B20, $insideDHT22),
    'interval' => ['*/15 17,18,19 * * *'],
    'valueProviders' => [
        ValueProviders::temperatureAndHumidity($insideDHT22),
        ValueProviders::temperatureAndHumidity($outsideDS18B20),
    ],
    'notification' => [
        'title' => 'Na zewnątrz jest chłodniej niż w domu',
        'message' => "Otwórz okno. W domu jest aż {temperature|$insideDHT22} a na zewnątrz tylko {temperature|$outsideDS18B20}!",
        'icon' => AutomateIcons::HEART,
        'cancellable' => true,
    ],
    'actions' => [
        ['label' => 'Sprawdź teraz', 'icon' => AutomateIcons::REFRESH, 'command' => 'postpone,1'],
        ['label' => 'Otwarłem', 'icon' => AutomateIcons::CHECK, 'command' => 'postpone,60000'],
    ]
],
```

#### Powiadom mnie o obecnych temperaturach w wybranych godzinach

```
[
    'valueProviders' => [
        ValueProviders::temperatureAndHumidity($outsideDS18B20),
        ValueProviders::temperatureAndHumidity($insideDHT22),
    ],
    'interval' => ['30 6 * * * ', '0 12 * * *', '0 15 * * *', '0 20 * * *'],
    'notification' => [
        'title' => "Na zewnątrz jest {temperature|$outsideDS18B20}, w środku {temperature|$insideDHT22}",
        'message' => "Wilgotność: {humidity|$insideDHT22}",
        'icon' => AutomateIcons::THERMISTOR,
        'cancellable' => true,
    ]
]
```
