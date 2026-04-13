# Luxury Sofà

Sito vetrina e-commerce in PHP con pannello amministrativo, carrello (localStorage), API per login/registrazione, ordini e newsletter. Layout responsive per desktop e mobile.

## Requisiti

- PHP 8.x con estensione PDO MySQL
- MySQL / MariaDB (es. XAMPP)
- Server web (Apache) oppure `php -S` per sviluppo

## Installazione locale

1. Clona la repository nella cartella del server web (es. `htdocs/luxury_sofa`).
2. Crea il database MySQL (nome predefinito in `db.php`: `luxury_sofa_db`) e le tabelle necessarie per utenti, prodotti, ordini, coupon e iscritti newsletter.
3. Adatta le credenziali in `db.php` se servono host, utente o password diversi.
4. Apri il sito nel browser (es. `http://localhost/luxury_sofa/`).

## Struttura principale

| Percorso | Descrizione |
|----------|-------------|
| `index.php` | Home e catalogo |
| `product.php` | Scheda prodotto |
| `profilo.php` | Area utente |
| `api.php` | Endpoint AJAX |
| `admin/` | Gestionale amministratori |

## Pubblicazione su GitHub

1. Installa [Git for Windows](https://git-scm.com/) e [GitHub CLI](https://cli.github.com/) se non sono già presenti.
2. Autenticati una volta: `gh auth login`
3. Dalla cartella del progetto esegui `.\scripts\publish-github.ps1` oppure:
   - `gh repo create luxury-sofa --public --source=. --remote=origin --push`

In alternativa crea manualmente il repository vuoto su GitHub e collega il remote:

```bash
git remote add origin https://github.com/TUO_UTENTE/luxury-sofa.git
git push -u origin main
```

## Licenza

Uso privato / progetto dimostrativo — adatta la licenza alle tue esigenze.
