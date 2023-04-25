# Resilient Messaging Workshop

W tej części warsztatu zapoznamy się z mechanizmami zapewniającymi dostępność i niezawodność w [Ecotone Framework](https://docs.ecotone.tech/).

# Wymagania

W celu uruchomienia warsztatu będzie nam potrzebny jedynie [Docker](https://docs.docker.com/engine/install/) i [Docker-Compose](https://docs.docker.com/compose/install/).

W edycji kodu projektu pomoże Visual Studio Code: https://code.visualstudio.com/ oraz roszerzenie do Visual Studio Code, podpowiadające składnię programowania: https://marketplace.visualstudio.com/items?itemName=DEVSENSE.phptools-vscode 

# Instalacja

0. Otwórz konsolę komend i przejdź do folderu w którym jest ten plik README.md. Pamiętaj, że wszystkie komendy `docker-compose` zadziałają tylko wtedy, gdy wykonasz je będąc w folderze w którym istnieje plik docker-compose.yml
1. Uruchom komendę `docker-compose pull && docker-compose up`
2. W momencie startu kontener z aplikacją zainstaluje dla nas wszystkie zależności. Można to sprawdzić przez `docker logs -f ecotone_demo`
3. Otwórz Visual Studio Code i w nim, otwórz folder w którym jest ten plik README.
4. Jesteśmy gotowi do warsztatu. Przejdź do rozdziału 'Zadanie do wykonania' 
5. Po zakończeniu ćwiczenia, aby usunąć wszystkie kontenery, wpisz komendę `docker-compose down`
 

# Zadanie do wykonania

W tym ćwieczeniu mamy zadanie zbudować stabilną funkcjonalność składania zamówienia.
Składanie zamówienia składa się z dwóch kroków:

- Zapisanie zamówienia `Order`
- Wywołanie zewnętrznego serwisu `ShippingService` w celu wysłania zamówienia do klienta

## Wywołanie zadania

Wykonaj polecenie z konsoli: `docker exec -it ecotone_demo php run_example.php`  
Gdy zadanie będzie poprawnie zaimplementowane skrypt poinformuje Cie o tym w innym przypadku wystąpi błąd.

## Zadanie 1

Związku z tym, że `ShippingService` to zewnętrzny serwis, nie możemy polegać na jego dostępności.  
Dlatego chcąc rozdzielić zapis zamówienia od wywołania `ShippingService`, chcemy przetworzyć wysyłkę zamówenia korzystając z asynchronicznej wiadomości.  

1. Przerób `OrderService` aby zamiast wywoływać `ShippingService` opublikował `Event` `OrderWasPlaced`.  
2. Dodaj EventHandler który będzie nasłuchiwał na `OrderWasPlaced` i wywoływał `ShippingService` (Możesz go stworzyć w ramach klasy `src/Application/OrderService.php`).
3. Dodaj asynchroniczny kanał o nazwie `orders`, który będzie wysyłał wiadomości do RabbitMQ: `\Ecotone\Amqp\AmqpBackedMessageChannelBuilder::create("orders")` (Możesz go stworzyć w ramach klasy `src/Infrastructure/MessageChannelConfiguration.php`)
4. Wykorzystaj ten kanał, aby przetworzyć EventHandler (`OrderWasPlaced`) asynchronicznie.

### Podpowiedzi

- [Publikowanie eventów](https://docs.ecotone.tech/modelling/event-handling/dispatching-events#publishing)
- [Przetwarzanie eventów](https://docs.ecotone.tech/modelling/event-handling/handling-events#registering-class-based-event-handler)
- [Asynchroniczne przetwarzanie wiadomości](https://docs.ecotone.tech/modelling/asynchronous-handling#running-asynchronously)

## Zadanie 2 [Opcjonalne]

Message Broker (RabbitMQ), może nie być dostępny w momencie wysyłki wiadomości. 
W takim przypadku nie uda nam się zapisać zamówienia, lub go dostarczyć.  
Chcemy aby nasz system był odporny na takie przypadki.

1. Zaimplementuj mechanizm, który zamiast wysłania wiadomości do RabbitMQ, zapisze (wraz z Order'em) i przetworzy bezpośrednio z bazy danych.    
Wykorzystaj do tego kanał, który zapisuje wiadomości w bazie danych, zamiast `RabbitMQ`: `\Ecotone\Dbal\DbalBackedMessageChannelBuilder::create("orders")`. 

### Podpowiedzi

- [Outbox Pattern](https://docs.ecotone.tech/modelling/error-handling/outbox-pattern#dbal-message-channel)