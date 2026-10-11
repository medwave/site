---
name: seo-hreflang
description: "Audit or generate hreflang annotations for a multi-language or multi-region site: tag presence, reciprocity, language/region codes, x-default, and canonical conflicts."
---

# OpenSEO Hreflang / International SEO

## Goal

Work out whether a site's language/region targeting is set up so Google serves the right locale version to the right searcher, and fix it if not. Hreflang is a hint, not a directive; most real-world hreflang problems are self-inflicted (broken reciprocity, wrong codes, conflicting canonicals) rather than Google ignoring a correct setup, so the audit should assume the implementation is broken until the evidence says otherwise.

Use this when a site has 2+ language or region variants of the same content (e.g. `/en/`, `/en-gb/`, `/fr/`, or separate ccTLDs/subdomains) and traffic, indexing, or locale-serving looks wrong. For a single-language site, this skill does not apply.

## Required inputs

- The site's locale variants: list of URLs or URL patterns for each language/region version of at least one representative page set
- `projectId`
- Whether locales live on paths (`/fr/`), subdomains (`fr.example.com`), or separate domains (`example.fr`) — ask if not obvious from the URLs given

## Project context

1. Call `get_project_context` first; confirm which markets/languages the business actually serves. A locale variant that exists but isn't a real target market (e.g. an abandoned translation) changes the fix from "repair" to "remove".
2. Append a research log entry on finish: `{ appendResearchLog: { summary: "Hreflang audit: <domain>. Verdict: <conclusion>" } }`.

## What to check

Fetch the raw HTML of each locale variant for at least one representative page (home page plus one deep page if available); hreflang in the rendered-only DOM without a raw-HTML or HTTP-header equivalent is invisible to Googlebot's indexing pass for this purpose, so check raw source, not just what renders.

1. **Where the tags live.** Valid locations are the `<head>` as `<link rel="alternate" hreflang="x" href="...">`, the HTTP header (`Link:` header, used for non-HTML like PDFs), or the XML sitemap (`<xhtml:link>` entries). Note which mechanism this site uses; mixing mechanisms inconsistently across pages is itself a finding.
2. **Code validity.** Each `hreflang` value must be a valid ISO 639-1 language code, optionally `-` plus an ISO 3166-1 Alpha-2 region code (e.g. `en`, `en-GB`, `pt-BR`). Flag invalid codes (`en-UK` is wrong, `en-GB` is right; a region code used alone with no language, like `hreflang="GB"`, is invalid).
3. **Self-reference.** Every page in a hreflang set must list itself as one of its own alternates. A set missing its own self-reference is a documented common failure mode.
4. **Reciprocity.** If page A points to page B via hreflang, B must point back to A. Fetch every URL in the set and build the actual link graph; one-directional links are the single most common real-world hreflang bug and usually the reason Google ignores the whole set for that page.
5. **x-default.** Check for an `x-default` entry pointing at the fallback/language-selector page, when one exists. Its absence is a minor finding, not critical, unless the project specifically relies on it for a selector page.
6. **Canonical conflicts.** Each locale page's `rel=canonical` should point to itself (or to the correct locale version), never to a single "main" version across all locales — a canonical pointing cross-locale tells Google to collapse the set, which defeats the hreflang entirely. This is a frequent and severe conflict: hreflang says "these are distinct, serve by locale" while canonical says "these are duplicates, pick one."
7. **Content parity.** The alternates should be genuine translations of the same content, not a different page that happens to share a template. Spot-check: do titles, headings, and primary CTA match in meaning across the set? A hreflang link to a thin or untranslated stub page is worse than no link.
8. **Sitemap consistency**, if hreflang is sitemap-based: confirm the sitemap's URLs return 200 and match the live site's locale URLs exactly (trailing slashes, http vs https, www vs non-www all count as mismatches).

## Workflow

1. Resolve the locale set and projectId. Fetch the raw HTML (or sitemap) for every URL in one representative set.
2. Extract every hreflang annotation found, per page, and build the link graph: who points to whom.
3. Validate codes, check self-reference and reciprocity against the graph, and flag x-default presence/absence.
4. Fetch each page's canonical tag and cross-check against hreflang targets for conflicts.
5. Spot-check content parity across 2-3 of the alternates.
6. If asked to generate rather than just audit, produce the correct `<link rel="alternate" hreflang="...">` block for each locale (full reciprocal set, including self and x-default), ready to paste into each page's `<head>`, derived from the actual URL list provided.
7. Report findings grouped by severity: broken reciprocity and canonical conflicts first (these actively suppress the set), then invalid codes, then missing self-reference/x-default, then content-parity concerns. Give the exact corrected tag(s) for each broken page.

## Guardrails

- Hreflang is a hint Google uses when choosing which URL to show; don't frame any single hreflang fix as guaranteed to change rankings or traffic — frame it as removing a known cause of wrong-locale serving.
- Don't assume ccTLD or subdomain structure implies correct geotargeting; check Search Console country targeting / project context instead of guessing from the domain shape.
- Verify every tag against fetched source, never from a template you assume the CMS uses. Different pages on the same site often have inconsistent hreflang output from different templates or plugins.
- Keep the report scoped to locale-targeting mechanics; translation quality and cultural adaptation beyond parity-checking belong to a content or localization review, not this skill.
