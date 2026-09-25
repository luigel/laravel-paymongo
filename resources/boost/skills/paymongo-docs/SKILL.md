---
name: paymongo-docs
description: "Look up PayMongo's official docs before answering or writing code. Use when working with the PayMongo API (endpoints, request/response fields, resources like Payment Intents, Payment Methods, Checkout Sessions, Webhooks, Refunds, Subscriptions, QR Ph), when touching the luigel/laravel-paymongo integration, or when answering any question about how PayMongo behaves (statuses, errors, test mode, payment methods)."
---

# PayMongo Docs

Every PayMongo fact you use comes from docs.paymongo.com, fetched in this session. Your memory of PayMongo only tells you what to look up. Base each claim on a page you fetched, and cite that page.

## Where the docs live

- **Index:** `https://docs.paymongo.com/llms.txt` lists every page with a one-line summary. It has two sections, `## Guides` (`/docs/...`) and `## API Reference` (`/reference/...`).
- **Markdown pages:** add `.md` to any page URL to get its markdown, e.g. `https://docs.paymongo.com/reference/create-a-paymentintent.md`.
- **API reference pages** include the full **OpenAPI definition** for their endpoint (request body schema, response schema, required fields, enums). Use it as the source of truth for field names and types.
- **Starting points:**
    - `https://docs.paymongo.com/docs/get-started-what-is-paymongo.md` for product concepts.
    - `https://docs.paymongo.com/reference/getting-started-with-your-api.md` for API basics like auth, keys and conventions.

## Steps

1. **Find the pages.** Fetch `llms.txt` and pick every page relevant to the task: the reference page for each endpoint or resource you touch, plus the guide that explains the flow (key concepts, errors, testing, webhooks). Use `curl -sL https://docs.paymongo.com/llms.txt | grep -i '<topic>'` to narrow it down.
2. **Read them.** Fetch each page's `.md` URL. For endpoints, read the OpenAPI definition. Use `curl -sL` to get the exact text when field names, enums or amounts matter. `WebFetch` summarises, which is fine for prose but can lose schema details.
3. **Check the wrapper.** When the task goes through `luigel/laravel-paymongo`, read its source in `vendor/luigel/laravel-paymongo` to see how it maps to the documented endpoint and payload.
4. **Answer or write the code from what you read.** Cite the page URL for each PayMongo fact.

You're done when every PayMongo-specific claim or payload field you rely on is backed by a page you fetched in this session.

## When the docs don't answer it

If the fetched pages don't cover the question, or two pages disagree, say so and name the pages you checked. Give the user the gap and a way to settle it, such as a test-mode request or asking PayMongo support. Leave the answer open rather than filling it in from memory.
