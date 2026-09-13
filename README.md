# StoreHealth AI

**StoreHealth AI** is a Magento 2 health monitoring and business health dashboard designed to give store teams a clear overview of their e-commerce store health.

Instead of exposing technical Magento information to business users, StoreHealth AI converts important store signals into simple business-oriented health indicators, attention items, and opportunities.

---

## Overview

StoreHealth AI helps Magento store teams quickly understand:

* Overall store health
* Product availability
* Product data quality
* Category health
* Cache health
* Indexer health
* Cron health
* Areas that need attention
* Potential business opportunities

The goal is to provide a **simple business-facing health dashboard** rather than a technical Magento diagnostic screen.

---

## Features

### Store Business Health

StoreHealth AI provides an overall health score based on multiple areas of the Magento store.

Example:

```text
STORE BUSINESS HEALTH

Overall: 87/100

Sales             🟢
Products          🟡
Customer Journey  🔴
Performance       🟡
SEO               🟢
```

The dashboard is designed so that business teams can understand the current store condition without needing Magento development knowledge.

---

### Product Health

The module analyzes Magento product data and identifies potential issues such as:

* Disabled products
* Products without prices
* Products without URL keys
* Product availability issues
* Product data inconsistencies

Example:

```text
14 high-traffic products unavailable
27 products missing important data
```

---

### Category Health

The dashboard monitors Magento category information and provides category-related health signals.

---

### Cache Health

StoreHealth AI checks Magento cache status and reports whether required cache types are enabled.

---

### Indexer Health

Magento indexers are monitored to identify indexers that are not in a healthy state.

Example:

```text
Indexer Health

7 / 7 indexers valid

Status: Healthy
```

---

### Cron Health

Magento cron schedules are checked for potential issues such as pending or failed jobs.

---

### What Needs Attention?

The dashboard highlights the most important issues first.

Example:

```text
WHAT NEEDS ATTENTION

🔴 Mobile checkout conversion ↓21%
🟠 14 high-traffic products unavailable
🟡 27 new broken URLs
```

The intention is to help teams focus on the problems that can have the biggest business impact.

---

### Opportunities

StoreHealth AI can highlight potential improvement areas.

Example:

```text
OPPORTUNITIES

Mobile PDP traffic ↑18%
Mobile conversion ↓7%

Opportunity:
Investigate the mobile product-to-cart journey.
```

---

## Architecture

StoreHealth AI is designed with a modular architecture so additional health checks and integrations can be added over time.

Current architecture:

```text
StoreHealth AI
       │
       ├── Products
       ├── Product Availability
       ├── Product Data
       ├── Categories
       ├── Cache
       ├── Indexers
       └── Cron
```

Future integrations may include:

```text
Magento
   │
   ├── StoreHealth AI
   │
   ├── Google Analytics 4
   │
   ├── Google Search Console
   │
   ├── Shopify
   │
   └── WooCommerce
```

---

## Requirements

* Magento 2
* PHP version supported by the installed Magento version
* Magento Admin access
* Composer

The module is intended to work with Magento 2 installations and should follow the PHP/Magento compatibility requirements of the target Magento project.

---

## Installation

### Option 1 — Composer

Once the package is published on Packagist:

```bash
composer require kumar/module-store-health
```

Then run:

```bash
php bin/magento setup:upgrade
php bin/magento cache:flush
```

---

### Option 2 — Manual Installation

Copy the module into:

```text
app/code/Kumar/StoreHealth
```

Then run:

```bash
php bin/magento module:enable Kumar_StoreHealth
php bin/magento setup:upgrade
php bin/magento cache:flush
```

For production mode, run the required Magento deployment commands according to your deployment process.

---

## Admin Configuration

After installation, StoreHealth AI is available from the Magento Admin:

```text
Stores
└── StoreHealth AI
```

Configuration is available under:

```text
Stores
└── Configuration
    └── Kumar
        └── StoreHealth AI
```

The configuration can be used to enable or disable the module.

---

## Dashboard

The StoreHealth AI dashboard provides a business-oriented overview of the Magento store.

The dashboard is intended to answer questions such as:

* Is the store healthy?
* Are important products available?
* Are there product data problems?
* Are Magento indexers healthy?
* Are cron jobs running correctly?
* What requires immediate attention?
* Where are potential opportunities?

---

## Security

StoreHealth AI runs inside the Magento Admin area and uses Magento ACL permissions.

The module uses the following ACL resource:

```text
Kumar_StoreHealth::storehealth
```

Only Magento administrators with the appropriate permission should be able to access the StoreHealth AI dashboard and configuration.

---

## Current Scope

The current version focuses on **Magento-native store health information**.

The module checks information available directly from the Magento installation, including:

* Products
* Product status
* Product prices
* Product URL keys
* Categories
* Cache
* Indexers
* Cron schedules

External website crawling is intentionally not part of the current Magento-native health check.

---

## AI Layer

The long-term goal is to add an AI layer that can explain health issues in business-friendly language.

For example, instead of displaying:

```text
Indexer status: Reindex required
```

StoreHealth AI could eventually provide:

```text
Product information may not be fully reflected on the storefront.

Recommended action:
Refresh the affected product index.
```

The AI layer is intended to make technical store information easier for non-technical teams to understand.

---

## Design Principles

StoreHealth AI follows these principles:

### Business First

The dashboard should focus on business impact rather than technical implementation details.

### Actionable Information

Issues should explain what needs attention and why it matters.

### Simple Language

Business users should not need Magento development knowledge to understand the dashboard.

### Reliable Data

The first version prioritizes deterministic Magento data and reliable health rules before adding AI-generated insights.

### Extensible Architecture

Health checks and external integrations should be easy to add without rebuilding the entire dashboard.

---

## Development

Clone the repository:

```bash
git clone https://github.com/mekkumar/module-store-health.git
```

Enter the project:

```bash
cd module-store-health
```

For Magento development, copy the module to:

```text
app/code/Kumar/StoreHealth
```

Then run:

```bash
php bin/magento setup:upgrade
php bin/magento cache:flush
```

---

## Contributing

Contributions, suggestions, bug reports, and feature requests are welcome.

Before submitting a pull request:

1. Keep changes focused.
2. Follow Magento 2 coding standards.
3. Avoid unnecessary changes to unrelated files.
4. Test changes on a supported Magento installation.
5. Include clear information about the problem and solution.

---

## License

This project is licensed under the MIT License.

See the `LICENSE` file for details.

---

## Author

**Kunal Kumar**

Magento Frontend Developer

GitHub:

https://github.com/mekkumar

---

## Project Status

StoreHealth AI is currently under active development.

The initial version focuses on establishing a reliable Magento-native health monitoring foundation. Additional business metrics, integrations, historical reporting, and AI capabilities will be added progressively.
