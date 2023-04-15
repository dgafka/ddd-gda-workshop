# Resilient Messaging Workshop

W tej części warsztatu zapoznamy się z mechanizmami zapewniającymi dostępność i niezawodność w [Ecotone Framework](https://docs.ecotone.tech/).

# Wymagania

W celu uruchomienia warsztatu będzie nam potrzebny jedynie [Docker](https://docs.docker.com/engine/install/) i [Docker-Compose](https://docs.docker.com/compose/install/).

# Instalacja

1. Uruchom komendę `docker-compose pull && docker-compose up -d`
2. W momencie startu kontener z aplikacją zainstaluje dla nas wszystkie zależności. Można to sprawdzić przez `docker logs -f demo_development`
3. Jesteśmy gotowi do warsztatu.
4. Po zakończeniu ćwiczenia, aby usunąć wszystkie kontenery, wpisz komendę `docker-compose down`

# Zadanie do wykonania

W tym ćwieczeniu mamy zadanie zbudować stabilną funkcjonalność składania zamówienia.
Składanie zamówienia składa się z dwóch kroków:

- Zapisanie zamówienia `Order`
- Wywołanie zewnętrznego serwisu `ShippingService` w celu wysłania zamówienia do klienta

## Wywołanie zadania

1. `docker exec -it ecotone_demo php run_example.php`
2. Gdy zadanie będzie poprawnie zaimplementowane, skrypt poinformuje Cie o tym w innym przypadku wystąpi błąd.

## Zadanie 1

Związku z tym, że ShippingService to zewnętrzny serwis, nie możemy polegać na jego dostępności.  
Dlatego chcąc zapewnić stabilność naszego systemu, chcemy przetworzyć wysyłkę zamówenia korzystając z asynchronicznych wiadomości.  

1. Przerób `OrderService` aby zamiast wywoływać `ShippingService` opublikował `Event` `OrderWasPlaced`.  
2. Dodaj EventHandler który będzie nasłuchiwał na `OrderWasPlaced` i wywoływał `ShippingService`.
3. Dodaj asynchroniczny kanał, który będzie wysyłał wiadomości do RabbitMQ: `AmqpBackedMessageChannelBuilder::create("orders")`
4. Wykorzystaj ten kanał, aby przeworzyć EventHandler `OrderWasPlaced` asynchronicznie.

## Zadanie 2 [Opcjonalne]

Message Broker (RabbitMQ), może nie być dostępny w momencie wysyłki wiadomości. 
W takim przypadku nie uda nam się złożyć zamówienia, lub go dostarczyć.  
Chcemy aby nasz system był odporny na takie przypadki.

1. Zaimplementuj mechanizm, który zamiast wysłania wiadomości do RabbitMQ, zapisze (wraz z Order'em) i przetworzy bezpośrednio z bazy danych.    
Wykorzystaj do tego kanał, który zapisuje wiadomości w bazie danych, zamiast `RabbitMQ`: `DbalBackedMessageChannelBuilder::create("orders")`. 

### Podpowiedzi

- [Outbox Pattern](https://docs.ecotone.tech/modelling/error-handling/outbox-pattern)