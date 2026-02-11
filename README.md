# Star Wars API Search App


## Running the app

Simply do

```bash
make run
```

Open **http://localhost:8080** in your browser.

## Testing/Linters

**Back-end** (PHPUnit): since I'm more focused on BE I've added more tests, but primarily focused on entity behavior and use cases.

```bash
make test-be
make lint-be
```

**Front-end** (React Testing Library): did some sample tests for a few components, in the interest of time.

```bash
make test-fe
make lint-fe
```


## Extra Handy Commands

| Command        | Description                           |
| -------------- | ------------------------------------- |
| `make build`   | Build Docker images                   |
| `make up`      | Start all services (app + Redis)      |
| `make down`    | Stop all services                     |
| `make restart` | Restart all services                  |
| `make logs`    | Follow live logs                      |
| `make clean`   | Wipe volumes and rebuild from scratch |

#### Key Design Decisions

I'm fond of **Clean Architecture** (Uncle Bob) and **Domain-Driven Design** (Eric Evans) principles so, I've tried to follow those in this exercise. 

## Tech Stack

| Layer         | Technology                                                 |
| ------------- | ---------------------------------------------------------- |
| Back-end      | PHP 8.3, Laravel 12, Predis                                |
| Front-end     | React 18, TypeScript, React Router v6, Axios, SCSS Modules |
| Cache / Queue | Redis 7                                                    |
| Database      | SQLite (metrics query logs)                                |
| Server        | Nginx, PHP-FPM, Supervisord                                |
| Container     | Docker/Compose                                             |


For the **BACKEND** I've implemented these features:

* **Rich Domain Entities**

* **Centralized Error Handling**

* **Redis Caching**

* **Graceful Shutdown**

For the **FRONTEND** I've implemented these features:

* **Context API (no Redux, no reducers)**

* **SCSS Modules**

* **Error Handling via Toasts**

For the **INFRA** I've used:

* **Single Container (compose)**

* **Multi-stage Dockerfile**

* **Nginx**             |

## Metrics

- Query statistics are stored in SQLite (`query_logs` table) on every search request.
- A scheduled job (`RecomputeMetricsJob`) runs every 5 minutes via Laravel's scheduler, recomputes aggregates, and stores the snapshot in Redis.
- The `/api/metrics` endpoint reads the pre-computed snapshot for instant response.
- Metrics include: 
    * top 5 queries
    * average request duration
    * cached vs non-cached ratio,
    * error vs success ratio